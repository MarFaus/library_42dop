<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Borrowing extends Model
{
    /** @use HasFactory<\Database\Factories\BorrowingFactory> */
    use HasFactory;

    protected $fillable = [
        'book_id',
        'reader_id',
        'borrowed_at',
        'return_date',
        'returned_at',
    ];

    /** Эти поля попадают в JSON вместе с обычными столбцами. */
    protected $appends = [
        'is_active',
        'is_overdue',
    ];

    protected function casts(): array
    {
        return [
            'borrowed_at' => 'date:Y-m-d',
            'return_date' => 'date:Y-m-d',
            'returned_at' => 'datetime',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function reader(): BelongsTo
    {
        return $this->belongsTo(Reader::class);
    }

    /** Книга всё ещё у читателя. */
    public function getIsActiveAttribute(): bool
    {
        return $this->returned_at === null;
    }

    /** Книга у читателя, а срок возврата уже прошёл. */
    public function getIsOverdueAttribute(): bool
    {
        return $this->is_active && $this->return_date->lt(today());
    }

    /** returned | overdue | active */
    public function getStatusAttribute(): string
    {
        if (! $this->is_active) {
            return 'returned';
        }

        return $this->is_overdue ? 'overdue' : 'active';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'returned' => 'Возвращена',
            'overdue' => 'Просрочена',
            default => 'Выдана',
        };
    }
}
