<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Services\BookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function __construct(
        private BookService $bookService
    ) {
    }

    /**
     * GET /api/books?search=...
     */
    public function index(Request $request): JsonResponse
    {
        $books = $this->bookService->getAll(
            $request->input('search')
        );

        return response()->json($books);
    }

    /**
     * GET /api/books/{book}
     */
    public function show(Book $book): JsonResponse
    {
        $book->load('author');

        return response()->json([
            'data' => $book,
        ]);
    }

    /**
     * POST /api/books
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());

        $data['available'] = true;

        $book = $this->bookService->create($data);
        $book->load('author');

        return response()->json([
            'message' => 'Книга успешно добавлена',
            'data' => $book,
        ], 201);
    }

    /**
     * PUT /api/books/{book}   полное обновление, все обязательные поля
     * PATCH /api/books/{book} частичное обновление, достаточно изменяемых полей
     */
    public function update(Request $request, Book $book): JsonResponse
    {
        $data = $request->validate($this->rules($request->isMethod('patch')));

        $book = $this->bookService->update($book, $data);
        $book->load('author');

        return response()->json([
            'message' => 'Книга обновлена',
            'data' => $book,
        ]);
    }

    /**
     * DELETE /api/books/{book}
     */
    public function destroy(Book $book): JsonResponse
    {
        // Сервис запрещает удаление книги с активной выдачей (ответ 422).
        $this->bookService->delete($book);

        return response()->json([
            'message' => 'Книга удалена',
        ]);
    }

    /** Правила проверки. Для PATCH обязательные поля становятся необязательными (sometimes). */
    private function rules(bool $partial = false): array
    {
        $required = $partial ? 'sometimes|required' : 'required';

        return [
            'title' => $required.'|string|max:255',
            'author_id' => $required.'|exists:authors,id',
            'genre' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1000|max:2100',
            'isbn' => 'nullable|string|max:50',
            'quantity' => $required.'|integer|min:1',
            'description' => 'nullable|string',
        ];
    }
}
