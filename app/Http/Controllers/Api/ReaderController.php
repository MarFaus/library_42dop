<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReaderController extends Controller
{
    /**
     * GET /api/readers
     */
    public function index(): JsonResponse
    {
        return response()->json(
            Reader::withCount('activeBorrowings')
                ->orderBy('name')
                ->paginate(10)
        );
    }

    /**
     * GET /api/readers/{reader}
     */
    public function show(Reader $reader): JsonResponse
    {
        $reader->load('activeBorrowings.book');

        return response()->json([
            'data' => $reader,
        ]);
    }

    /**
     * POST /api/readers
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());

        $reader = Reader::create($data);

        return response()->json([
            'message' => 'Читатель добавлен',
            'data' => $reader,
        ], 201);
    }

    /**
     * PUT|PATCH /api/readers/{reader}
     */
    public function update(Request $request, Reader $reader): JsonResponse
    {
        $data = $request->validate($this->rules($reader, $request->isMethod('patch')));

        $reader->update($data);

        return response()->json([
            'message' => 'Читатель обновлён',
            'data' => $reader,
        ]);
    }

    /**
     * DELETE /api/readers/{reader}
     */
    public function destroy(Reader $reader): JsonResponse
    {
        if ($reader->activeBorrowings()->exists()) {
            return response()->json([
                'message' => 'Нельзя удалить читателя, у которого есть невозвращённые книги.',
            ], 409);
        }

        $reader->delete();

        return response()->json([
            'message' => 'Читатель удалён',
        ]);
    }

    private function rules(?Reader $reader = null, bool $partial = false): array
    {
        $required = $partial ? 'sometimes|required' : 'required';

        return [
            'name' => $required.'|string|max:255',
            'email' => [
                ...explode('|', $required),
                'email',
                'max:255',
                Rule::unique('readers', 'email')->ignore($reader?->id),
            ],
            'phone' => 'nullable|string|max:50',
        ];
    }
}
