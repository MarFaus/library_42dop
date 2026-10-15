@extends('layouts.app')
@section('title', 'Книги · Библиотека')
@section('content')
<section class="hero">
    <h1>Каталог книг</h1>
    <form class="search" method="GET" action="{{ route('books.index') }}">
        <input type="search" name="search" value="{{ $search }}" placeholder="Название, автор, жанр или ISBN" aria-label="Поиск">
        <button class="btn" type="submit">Найти</button>
        @if ($search)<a class="btn btn--ghost" href="{{ route('books.index') }}">Сбросить</a>@endif
    </form>
</section>
<div class="grid">
    @forelse ($books as $book)
        @include('books._card', ['book' => $book])
    @empty
        <p class="empty">Ничего не найдено{{ $search ? ' по запросу «'.$search.'»' : '' }}.</p>
    @endforelse
</div>
{{ $books->links() }}
@endsection
