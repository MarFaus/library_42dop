<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            // Автора с книгами удалить нельзя: ограничение дублирует правило API (ответ 409).
            $table->foreignId('author_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('genre', 100)->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('isbn', 50)->nullable();
            // Сколько экземпляров сейчас на полке. Выдача уменьшает, возврат увеличивает.
            $table->unsignedInteger('quantity')->default(1);
            // Есть ли хотя бы один экземпляр для выдачи (quantity > 0).
            $table->boolean('available')->default(true);
            $table->text('description')->nullable();
            // Название, автор, жанр и ISBN в нижнем регистре: SQLite не умеет
            // искать кириллицу без учёта регистра, поэтому поиск идёт по этому столбцу.
            $table->text('search_index')->nullable();
            $table->timestamps();

            $table->index('title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
