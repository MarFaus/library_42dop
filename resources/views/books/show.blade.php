@extends('layouts.app')
@section('title', $book->title.' · Библиотека')
@section('content')
<a class="back" href="{{ route('books.index') }}">← К каталогу</a>
<article class="detail" style="--hue: {{ $book->spine_hue }}">
    <div class="detail__cover" aria-hidden="true"><span>{{ $book->title }}</span></div>
    <div>
        <span class="tag">{{ $book->genre }}</span>
        <h1>{{ $book->title }}</h1>
        <p class="card__author">{{ $book->author?->name }} · {{ $book->year }} · ISBN {{ $book->isbn }}</p>
        <p class="lead">{{ $book->description }}</p>
        <p>
            @if ($book->available)
                <span class="badge badge--ok">В наличии: {{ $book->quantity }}</span>
                <a class="btn" href="{{ route('borrowings.index', ['book_id' => $book->id]) }}#issue">Выдать читателю</a>
            @else
                <span class="badge badge--no">Все экземпляры на руках</span>
            @endif
        </p>
        @if ($book->activeBorrowings->isNotEmpty())
            <h2>Сейчас на руках</h2>
            <ul class="plain">
                @foreach ($book->activeBorrowings as $b)
                    <li>{{ $b->reader->name }} · вернуть до {{ $b->return_date->format('d.m.Y') }}
                        @if ($b->is_overdue)<span class="badge badge--late">просрочена</span>@endif</li>
                @endforeach
            </ul>
        @endif
    </div>
</article>
@endsection
