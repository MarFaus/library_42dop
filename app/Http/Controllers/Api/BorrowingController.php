<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Services\BorrowingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BorrowingController extends Controller
{
    public function __construct(
        private BorrowingService $borrowingService
    ) {
    }

    /**
     * GET /api/borrowings
     */
    public function index(): JsonResponse
    {
        $borrowings = Borrowing::with([
            'book.author',
            'reader',
        ])
            ->latest('id')
            ->paginate(10);

        return response()->json($borrowings);
    }

    /**
     * GET /api/borrowings/{borrowing}
     */
    public function show(Borrowing $borrowing): JsonResponse
    {
        $borrowing->load(['book.author', 'reader']);

        return response()->json([
            'data' => $borrowing,
        ]);
    }

    /**
     * POST /api/borrowings
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'book_id' => 'required|exists:books,id',
            'reader_id' => 'required|exists:readers,id',
            'return_date' => 'required|date|after_or_equal:today',
        ]);

        // Все правила выдачи проверяет сервис: тот же, что использует веб-интерфейс.
        $borrowing = $this->borrowingService->issue(
            (int) $data['book_id'],
            (int) $data['reader_id'],
            $data['return_date']
        );

        $borrowing->load(['book.author', 'reader']);

        return response()->json([
            'message' => 'Книга выдана читателю',
            'data' => $borrowing,
        ], 201);
    }

    /**
     * PATCH /api/borrowings/{borrowing}/return
     */
    public function returnBook(Borrowing $borrowing): JsonResponse
    {
        $borrowing = $this->borrowingService->returnBook($borrowing);

        return response()->json([
            'message' => 'Книга возвращена',
            'data' => $borrowing,
        ]);
    }
}
