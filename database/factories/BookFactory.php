<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Book> */
class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        return [
            'author_id' => Author::factory(),
            'title' => rtrim(fake()->sentence(3), '.'),
            'genre' => fake()->randomElement(['Роман', 'Фэнтези', 'Детектив', 'Классика', 'Наука']),
            'year' => fake()->numberBetween(1800, 2024),
            'isbn' => fake()->unique()->isbn13(),
            'quantity' => 2,
            'available' => true,
            'description' => fake()->paragraph(),
        ];
    }
}
