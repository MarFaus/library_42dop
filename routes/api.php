<?php

use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\BorrowingController;
use App\Http\Controllers\Api\ReaderController;
use Illuminate\Support\Facades\Route;

/*
 * REST API библиотеки. Файл подключён в bootstrap/app.php,
 * поэтому ко всем адресам автоматически добавляется префикс /api:
 * внутри файла пишем books, а обращаемся к /api/books.
 *
 * Префикс имён api. нужен, чтобы имена маршрутов API (api.books.index)
 * не совпадали с именами веб-страниц (books.index).
 *
 * Проверка: php artisan route:list --path=api
 */
Route::name('api.')->group(function () {
    Route::apiResource('books', BookController::class);
    Route::apiResource('authors', AuthorController::class);
    Route::apiResource('readers', ReaderController::class);

    // У выдач нет редактирования и удаления: выдачу можно только оформить
    // и принять возврат. Без only() маршруты PUT и DELETE вели бы
    // на несуществующие методы контроллера.
    Route::apiResource('borrowings', BorrowingController::class)
        ->only(['index', 'show', 'store']);

    Route::patch('borrowings/{borrowing}/return', [BorrowingController::class, 'returnBook'])
        ->name('borrowings.return');
});
