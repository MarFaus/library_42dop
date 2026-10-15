<article class="card" style="--hue: {{ $book->spine_hue }}">
    <div class="card__spine" aria-hidden="true"></div>
    <div class="card__body">
        <span class="tag">{{ $book->genre }}</span>
        <h3 class="card__title"><a href="{{ route('books.show', $book) }}">{{ $book->title }}</a></h3>
        <p class="card__author">{{ $book->author?->name }} · {{ $book->year }}</p>
        <div class="card__foot">
            @if ($book->available)
                <span class="badge badge--ok">В наличии: {{ $book->quantity }}</span>
                <a class="btn btn--small" href="{{ route('borrowings.index', ['book_id' => $book->id]) }}#issue">Выдать</a>
            @else
                <span class="badge badge--no">Все на руках</span>
            @endif
        </div>
    </div>
</article>
