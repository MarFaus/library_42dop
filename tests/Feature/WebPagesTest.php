<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Reader;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_render_with_seed_data(): void
    {
        $this->seed(DatabaseSeeder::class);
        $book = Book::first();

        $this->get('/')->assertRedirect('/books');
        foreach (['/books', "/books/{$book->id}", '/authors', '/readers', '/borrowings', '/api-docs'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/books?search='.urlencode('гарри'))->assertOk()->assertSee('Гарри Поттер');
        $this->get('/borrowings')->assertSee('Просрочена')->assertSee('Возвращена');
    }

    public function test_issue_and_return_through_web_form(): void
    {
        $book = Book::factory()->create(['quantity' => 1]);
        $reader = Reader::factory()->create();

        $this->post('/borrowings', ['book_id' => $book->id, 'reader_id' => $reader->id, 'return_date' => today()->addDays(5)->toDateString()])
            ->assertRedirect('/borrowings');
        $this->assertDatabaseHas('books', ['id' => $book->id, 'quantity' => 0]);

        $this->post('/borrowings/'.\App\Models\Borrowing::first()->id.'/return')->assertRedirect('/borrowings');
        $this->assertDatabaseHas('books', ['id' => $book->id, 'quantity' => 1]);
    }
}
