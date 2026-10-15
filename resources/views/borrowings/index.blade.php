@extends('layouts.app')
@section('title', 'Выдачи · Библиотека')
@section('content')
<h1>Выдачи</h1>
<section class="panel" id="issue">
    <h2>Выдать книгу</h2>
    @if ($errors->any())
        <div class="flash flash--err" role="alert">
            @foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif
    <form class="form" method="POST" action="{{ route('borrowings.store') }}">
        @csrf
        <label>Книга
            <select name="book_id" required>
                <option value="">Выберите книгу</option>
                @foreach ($books as $b)
                    <option value="{{ $b->id }}" @selected(old('book_id', $selectedBook) == $b->id)>{{ $b->title }} (экз.: {{ $b->quantity }})</option>
                @endforeach
            </select>
        </label>
        <label>Читатель
            <select name="reader_id" required>
                <option value="">Выберите читателя</option>
                @foreach ($readers as $r)
                    <option value="{{ $r->id }}" @selected(old('reader_id') == $r->id)>{{ $r->name }}</option>
                @endforeach
            </select>
        </label>
        <label>Вернуть до
            <input type="date" name="return_date" required min="{{ today()->toDateString() }}" value="{{ old('return_date', today()->addDays(14)->toDateString()) }}">
        </label>
        <button class="btn" type="submit">Выдать</button>
    </form>
    <p class="muted">Не больше {{ $max }} книг на читателя, одну и ту же книгу дважды выдать нельзя.</p>
</section>

<table class="table">
    <thead><tr><th>Книга</th><th>Читатель</th><th>Выдана</th><th>Вернуть до</th><th>Статус</th><th></th></tr></thead>
    <tbody>
    @forelse ($borrowings as $b)
        <tr>
            <td data-l="Книга"><a href="{{ route('books.show', $b->book_id) }}">{{ $b->book->title }}</a></td>
            <td data-l="Читатель">{{ $b->reader->name }}</td>
            <td data-l="Выдана">{{ $b->borrowed_at->format('d.m.Y') }}</td>
            <td data-l="Вернуть до">{{ $b->return_date->format('d.m.Y') }}</td>
            <td data-l="Статус"><span class="badge badge--{{ $b->status }}">{{ $b->status_label }}</span></td>
            <td>
                @if ($b->is_active)
                    <form method="POST" action="{{ route('borrowings.return', $b) }}">@csrf
                        <button class="btn btn--small" type="submit">Принять</button></form>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="6" class="empty">Выдач пока нет.</td></tr>
    @endforelse
    </tbody>
</table>
{{ $borrowings->links() }}
@endsection
