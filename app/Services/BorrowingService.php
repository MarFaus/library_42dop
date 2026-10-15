<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Reader;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Выдача и возврат книг. Все бизнес-правила живут здесь,
 * а контроллеры (веб и API) только вызывают сервис.
 */
class BorrowingService
{
    /** Сколько книг одновременно может быть на руках у одного читателя. */
    public const MAX_ACTIVE_PER_READER = 5;

    /**
     * Выдать книгу читателю.
     *
     * Правила: есть свободный экземпляр; читатель не держит эту же книгу;
     * у читателя не больше MAX_ACTIVE_PER_READER книг. Строка книги блокируется
     * на время транзакции, чтобы два одновременных запроса не выдали последний экземпляр дважды.
     */
    public function issue(int $bookId, int $readerId, string $returnDate): Borrowing
    {
        return DB::transaction(function () use ($bookId, $readerId, $returnDate) {
            $book = Book::query()->whereKey($bookId)->lockForUpdate()->firstOrFail();
            $reader = Reader::query()->findOrFail($readerId);

            if ($book->quantity < 1) {
                throw ValidationException::withMessages([
                    'book_id' => 'Свободных экземпляров этой книги нет.',
                ]);
            }

            $active = Borrowing::query()
                ->where('reader_id', $reader->id)
                ->whereNull('returned_at');

            if ((clone $active)->where('book_id', $book->id)->exists()) {
                throw ValidationException::withMessages([
                    'book_id' => 'Эта книга уже выдана этому читателю.',
                ]);
            }

            if ((clone $active)->count() >= self::MAX_ACTIVE_PER_READER) {
                throw ValidationException::withMessages([
                    'reader_id' => 'У читателя уже '.self::MAX_ACTIVE_PER_READER.' книг на руках.',
                ]);
            }

            $book->quantity = $book->quantity - 1;
            $book->available = $book->quantity > 0;
            $book->save();

            return Borrowing::create([
                'book_id' => $book->id,
                'reader_id' => $reader->id,
                'borrowed_at' => today(),
                'return_date' => $returnDate,
            ]);
        });
    }

    /** Принять книгу обратно: экземпляр возвращается на полку. */
    public function returnBook(Borrowing $borrowing): Borrowing
    {
        return DB::transaction(function () use ($borrowing) {
            $current = Borrowing::query()->whereKey($borrowing->id)->lockForUpdate()->firstOrFail();

            if ($current->returned_at !== null) {
                throw ValidationException::withMessages([
                    'borrowing' => 'Эта книга уже возвращена.',
                ]);
            }

            $book = Book::query()->whereKey($current->book_id)->lockForUpdate()->firstOrFail();
            $book->quantity = $book->quantity + 1;
            $book->available = true;
            $book->save();

            $current->returned_at = now();
            $current->save();

            return $current->load(['book.author', 'reader']);
        });
    }
}
