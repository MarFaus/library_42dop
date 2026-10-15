<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    /** @use HasFactory<\Database\Factories\BookFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'author_id',
        'genre',
        'year',
        'isbn',
        'quantity',
        'available',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'quantity' => 'integer',
            'available' => 'boolean',
        ];
    }

    /** Служебный столбец не нужен клиентам API. */
    protected $hidden = ['search_index'];

    protected static function booted(): void
    {
        // Поиск работает по столбцу search_index в нижнем регистре.
        static::saving(function (Book $book) {
            $book->search_index = mb_strtolower(implode(' ', array_filter([
                $book->title,
                $book->author?->name,
                $book->genre,
                $book->isbn,
            ])));
        });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function borrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class);
    }

    /** Выдачи, по которым книгу ещё не вернули. */
    public function activeBorrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class)->whereNull('returned_at');
    }

    /** Поиск по названию, автору, жанру и ISBN без учёта регистра. */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        // Символы ! % _ экранируются, чтобы поиск «100%» не работал как шаблон.
        $needle = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($term)).'%';

        return $query->whereRaw("search_index like ? escape '!'", [$needle]);
    }

    /** Оттенок корешка книги на странице: зависит от жанра, поэтому жанры различимы по цвету. */
    public function getSpineHueAttribute(): int
    {
        return crc32((string) ($this->genre ?: $this->title)) % 360;
    }
}
