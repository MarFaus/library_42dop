<?php

use App\Http\Controllers\Web\AuthorController;
use App\Http\Controllers\Web\BookController;
use App\Http\Controllers\Web\BorrowingController;
use App\Http\Controllers\Web\ReaderController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/books');

Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{book}', [BookController::class, 'show'])->whereNumber('book')->name('books.show');

Route::get('/authors', [AuthorController::class, 'index'])->name('authors.index');
Route::get('/readers', [ReaderController::class, 'index'])->name('readers.index');

Route::get('/borrowings', [BorrowingController::class, 'index'])->name('borrowings.index');
Route::post('/borrowings', [BorrowingController::class, 'store'])->name('borrowings.store');
Route::post('/borrowings/{borrowing}/return', [BorrowingController::class, 'returnBook'])
    ->whereNumber('borrowing')
    ->name('borrowings.return');

Route::view('/api-docs', 'pages.api')->name('api.docs');
