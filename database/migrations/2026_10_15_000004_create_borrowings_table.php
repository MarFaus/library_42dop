<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrowings', function (Blueprint $table) {
            $table->id();
            // При удалении книги или читателя уходит и история выдач.
            // Удалять можно только если нет активных выдач (проверяют сервисы и контроллеры).
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reader_id')->constrained()->cascadeOnDelete();
            $table->date('borrowed_at');
            // Срок возврата, который назначили при выдаче.
            $table->date('return_date');
            // Когда книгу вернули на самом деле. null означает, что выдача активна.
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();

            $table->index('returned_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrowings');
    }
};
