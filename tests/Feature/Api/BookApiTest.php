<?php

namespace Tests\Feature\Api;

use App\Models\Author;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Reader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $over = []): array
    {
        return array_merge([
            'title' => 'Новая книга',
            'author_id' => Author::factory()->create()->id,
            'genre' => 'Роман',
            'year' => 2020,
            'isbn' => '978-0-000-00000-0',
            'quantity' => 2,
        ], $over);
    }

    public function test_index_is_paginated_by_ten(): void
    {
        Book::factory()->count(12)->create();

        $this->getJson('/api/books')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('total', 12);
    }

    public function test_search_is_case_insensitive_for_cyrillic(): void
    {
        $author = Author::factory()->create(['name' => 'Джоан Роулинг']);
        Book::factory()->create(['title' => 'Гарри Поттер', 'author_id' => $author->id]);
        Book::factory()->create(['title' => 'Другая книга']);

        $this->getJson('/api/books?search='.urlencode('гарри'))->assertJsonPath('total', 1);
        $this->getJson('/api/books?search='.urlencode('РОУЛИНГ'))->assertJsonPath('total', 1);
    }

    public function test_show_returns_book_with_author_and_404(): void
    {
        $book = Book::factory()->create();

        $this->getJson("/api/books/{$book->id}")->assertOk()->assertJsonPath('data.id', $book->id)->assertJsonStructure(['data' => ['author']]);
        $this->getJson('/api/books/9999')->assertNotFound();
    }

    public function test_store_creates_and_validates(): void
    {
        $this->postJson('/api/books', $this->payload())->assertCreated()->assertJsonPath('data.title', 'Новая книга');
        $this->assertDatabaseHas('books', ['title' => 'Новая книга', 'available' => true]);

        $this->postJson('/api/books', [])->assertStatus(422)->assertJsonValidationErrors(['title', 'author_id', 'quantity']);
    }

    public function test_validation_error_is_json_even_without_accept_header(): void
    {
        $this->post('/api/books', [])->assertStatus(422)->assertJsonValidationErrors(['title']);
    }

    public function test_patch_updates_partially_and_put_requires_all_fields(): void
    {
        $book = Book::factory()->create(['title' => 'Старое']);

        $this->patchJson("/api/books/{$book->id}", ['title' => 'Новое'])->assertOk()->assertJsonPath('data.title', 'Новое');
        $this->putJson("/api/books/{$book->id}", ['title' => 'Только название'])->assertStatus(422);
    }

    public function test_delete_removes_book_but_not_when_borrowed(): void
    {
        $free = Book::factory()->create();
        $this->deleteJson("/api/books/{$free->id}")->assertOk();
        $this->assertDatabaseMissing('books', ['id' => $free->id]);

        $busy = Book::factory()->create();
        Borrowing::factory()->create(['book_id' => $busy->id, 'reader_id' => Reader::factory()]);
        $this->deleteJson("/api/books/{$busy->id}")->assertStatus(422);
        $this->assertDatabaseHas('books', ['id' => $busy->id]);
    }
}
