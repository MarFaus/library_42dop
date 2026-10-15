<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Services\BookService;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function __construct(private BookService $books) {}

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        return view('books.index', [
            'books' => $this->books->getAll($search ?: null),
            'search' => $search,
        ]);
    }

    public function show(Book $book)
    {
        $book->load(['author', 'activeBorrowings.reader']);

        return view('books.show', ['book' => $book]);
    }
}
