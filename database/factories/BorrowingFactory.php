<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Reader;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Borrowing> */
class BorrowingFactory extends Factory
{
    protected $model = Borrowing::class;

    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'reader_id' => Reader::factory(),
            'borrowed_at' => today(),
            'return_date' => today()->addDays(14),
            'returned_at' => null,
        ];
    }
}
