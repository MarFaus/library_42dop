<?php

namespace Tests\Feature\Api;

use App\Models\Author;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Reader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorReaderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_crud(): void
    {
        $id = $this->postJson('/api/authors', ['name' => 'Автор'])->assertCreated()->json('data.id');
        $this->getJson("/api/authors/{$id}")->assertOk();
        $this->patchJson("/api/authors/{$id}", ['name' => 'Автор 2'])->assertOk();
        $this->assertDatabaseHas('authors', ['id' => $id, 'name' => 'Автор 2']);
        $this->deleteJson("/api/authors/{$id}")->assertOk();
        $this->getJson("/api/authors/{$id}")->assertNotFound();
    }

    public function test_author_with_books_cannot_be_deleted(): void
    {
        $book = Book::factory()->create();

        $this->deleteJson("/api/authors/{$book->author_id}")->assertStatus(409);
        $this->assertDatabaseHas('authors', ['id' => $book->author_id]);
    }

    public function test_renaming_author_updates_book_search(): void
    {
        $book = Book::factory()->create(['title' => 'Тест']);
        $this->patchJson("/api/authors/{$book->author_id}", ['name' => 'Уникальный Автор'])->assertOk();

        $this->getJson('/api/books?search='.urlencode('уникальный'))->assertJsonPath('total', 1);
    }

    public function test_reader_crud_and_unique_email(): void
    {
        $data = ['name' => 'Читатель', 'email' => 'r@example.com'];
        $id = $this->postJson('/api/readers', $data)->assertCreated()->json('data.id');

        $this->postJson('/api/readers', $data)->assertStatus(422)->assertJsonValidationErrors(['email']);
        $this->patchJson("/api/readers/{$id}", ['email' => 'r@example.com'])->assertOk(); // свой email не конфликтует
        $this->deleteJson("/api/readers/{$id}")->assertOk();
    }

    public function test_reader_with_active_borrowing_cannot_be_deleted(): void
    {
        $reader = Reader::factory()->create();
        Borrowing::factory()->create(['reader_id' => $reader->id]);

        $this->deleteJson("/api/readers/{$reader->id}")->assertStatus(409);
    }
}
