<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Reader;
use App\Services\BorrowingService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BorrowingController extends Controller
{
    public function __construct(private BorrowingService $service) {}

    public function index(Request $request)
    {
        return view('borrowings.index', [
            'borrowings' => Borrowing::with(['book.author', 'reader'])->latest('id')->paginate(10),
            'books' => Book::where('quantity', '>', 0)->orderBy('title')->get(['id', 'title', 'quantity']),
            'readers' => Reader::orderBy('name')->get(['id', 'name']),
            'selectedBook' => $request->integer('book_id') ?: null,
            'max' => BorrowingService::MAX_ACTIVE_PER_READER,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'reader_id' => ['required', 'integer', 'exists:readers,id'],
            'return_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        try {
            $b = $this->service->issue($data['book_id'], $data['reader_id'], $data['return_date']);
        } catch (ValidationException $e) {
            return redirect(route('borrowings.index').'#issue')->withErrors($e->errors())->withInput();
        }

        return redirect()->route('borrowings.index')->with('status', 'Книга выдана. Вернуть до '.$b->return_date->format('d.m.Y').'.');
    }

    public function returnBook(Borrowing $borrowing)
    {
        try {
            $this->service->returnBook($borrowing);
        } catch (ValidationException $e) {
            return redirect()->route('borrowings.index')->withErrors($e->errors());
        }

        return redirect()->route('borrowings.index')->with('status', 'Книга принята обратно.');
    }
}
