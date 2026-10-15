<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

/**
 * Работа с книгами. Один и тот же сервис использует
 * и веб-интерфейс, и API, поэтому правила не дублируются.
 */
class BookService
{
    public const PER_PAGE = 10;

    /** Книги с авторами, с поиском по названию, автору, жанру и ISBN. */
    public function getAll(?string $search = null): LengthAwarePaginator
    {
        return Book::query()
            ->with('author')
            ->search($search)
            ->orderBy('title')
            ->orderBy('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();
    }

    public function create(array $data): Book
    {
        $data['available'] = ((int) ($data['quantity'] ?? 1)) > 0;

        return Book::create($data);
    }

    public function update(Book $book, array $data): Book
    {
        // Признак «есть в наличии» всегда следует за количеством экземпляров.
        $data['available'] = ((int) ($data['quantity'] ?? $book->quantity)) > 0;

        $book->update($data);

        return $book;
    }

    /**
     * Книгу с активной выдачей удалять нельзя: читатель ещё не вернул экземпляр.
     * Исключение превращается в ответ 422 для API и в сообщение об ошибке для веб-страницы.
     */
    public function delete(Book $book): void
    {
        if ($book->activeBorrowings()->exists()) {
            throw ValidationException::withMessages([
                'book' => 'Нельзя удалить книгу: она сейчас выдана читателю.',
            ]);
        }

        $book->delete();
    }
}
