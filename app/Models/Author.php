<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Author extends Model
{
    /** @use HasFactory<\Database\Factories\AuthorFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'birth_date',
        'country',
        'biography',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date:Y-m-d',
        ];
    }

    protected static function booted(): void
    {
        // Имя автора входит в поисковый индекс его книг: при смене имени индекс обновляется.
        static::saved(function (Author $author) {
            if ($author->wasChanged('name')) {
                $author->books()->get()->each->save();
            }
        });
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
}
