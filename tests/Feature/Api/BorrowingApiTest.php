<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Reader;
use App\Services\BorrowingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BorrowingApiTest extends TestCase
{
    use RefreshDatabase;

    private function issue(Book $book, Reader $reader, ?string $date = null)
    {
        return $this->postJson('/api/borrowings', [
            'book_id' => $book->id,
            'reader_id' => $reader->id,
            'return_date' => $date ?? today()->addDays(7)->toDateString(),
        ]);
    }

    public function test_issue_decrements_stock_and_return_restores_it(): void
    {
        $book = Book::factory()->create(['quantity' => 1]);
        $reader = Reader::factory()->create();

        $id = $this->issue($book, $reader)->assertCreated()->json('data.id');
        $this->assertDatabaseHas('books', ['id' => $book->id, 'quantity' => 0, 'available' => false]);

        $this->patchJson("/api/borrowings/{$id}/return")->assertOk()->assertJsonPath('data.is_active', false);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'quantity' => 1, 'available' => true]);
    }

    public function test_cannot_issue_when_out_of_stock(): void
    {
        $book = Book::factory()->create(['quantity' => 1]);
        $this->issue($book, Reader::factory()->create())->assertCreated();

        $this->issue($book, Reader::factory()->create())->assertStatus(422)->assertJsonValidationErrors(['book_id']);
    }

    public function test_same_reader_cannot_take_same_book_twice(): void
    {
        $book = Book::factory()->create(['quantity' => 3]);
        $reader = Reader::factory()->create();
        $this->issue($book, $reader)->assertCreated();

        $this->issue($book, $reader)->assertStatus(422)->assertJsonValidationErrors(['book_id']);
    }

    public function test_reader_limit(): void
    {
        $reader = Reader::factory()->create();
        Book::factory()->count(BorrowingService::MAX_ACTIVE_PER_READER)->create()->each(fn ($b) => $this->issue($b, $reader)->assertCreated());

        $this->issue(Book::factory()->create(), $reader)->assertStatus(422)->assertJsonValidationErrors(['reader_id']);
    }

    public function test_past_return_date_is_rejected(): void
    {
        $this->issue(Book::factory()->create(), Reader::factory()->create(), today()->subDay()->toDateString())
            ->assertStatus(422)->assertJsonValidationErrors(['return_date']);
    }

    public function test_return_twice_fails(): void
    {
        $id = $this->issue(Book::factory()->create(), Reader::factory()->create())->json('data.id');
        $this->patchJson("/api/borrowings/{$id}/return")->assertOk();

        $this->patchJson("/api/borrowings/{$id}/return")->assertStatus(422);
        $this->patchJson('/api/borrowings/9999/return')->assertNotFound();
    }
}
