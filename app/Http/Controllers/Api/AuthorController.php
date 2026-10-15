<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    /**
     * GET /api/authors
     */
    public function index(): JsonResponse
    {
        // Формат тот же, что у /api/books: current_page, data, per_page, total.
        return response()->json(
            Author::withCount('books')
                ->orderBy('name')
                ->paginate(10)
        );
    }

    /**
     * GET /api/authors/{author}
     */
    public function show(Author $author): JsonResponse
    {
        $author->load('books');

        return response()->json([
            'data' => $author,
        ]);
    }

    /**
     * POST /api/authors
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'country' => 'nullable|string|max:100',
            'biography' => 'nullable|string',
        ]);

        $author = Author::create($data);

        return response()->json([
            'message' => 'Автор добавлен',
            'data' => $author,
        ], 201);
    }

    /**
     * PUT|PATCH /api/authors/{author}
     */
    public function update(Request $request, Author $author): JsonResponse
    {
        $required = $request->isMethod('patch') ? 'sometimes|required' : 'required';

        $data = $request->validate([
            'name' => $required.'|string|max:255',
            'birth_date' => 'nullable|date',
            'country' => 'nullable|string|max:100',
            'biography' => 'nullable|string',
        ]);

        $author->update($data);

        return response()->json([
            'message' => 'Автор обновлён',
            'data' => $author,
        ]);
    }

    /**
     * DELETE /api/authors/{author}
     */
    public function destroy(Author $author): JsonResponse
    {
        if ($author->books()->exists()) {
            // 409 Conflict: действие противоречит текущему состоянию данных.
            return response()->json([
                'message' => 'Нельзя удалить автора, у которого есть книги.',
            ], 409);
        }

        $author->delete();

        return response()->json([
            'message' => 'Автор удалён',
        ]);
    }
}
