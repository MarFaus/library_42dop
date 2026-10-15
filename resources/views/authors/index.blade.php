@extends('layouts.app')
@section('title', 'Авторы · Библиотека')
@section('content')
<h1>Авторы</h1>
<div class="grid grid--wide">
    @forelse ($authors as $a)
        <article class="panel">
            <h3>{{ $a->name }}</h3>
            <p class="muted">{{ $a->country }}@if ($a->birth_date) · род. {{ $a->birth_date->format('Y') }}@endif</p>
            <p>{{ \Illuminate\Support\Str::limit($a->biography, 140) }}</p>
            <span class="badge">Книг: {{ $a->books_count }}</span>
        </article>
    @empty
        <p class="empty">Авторов пока нет.</p>
    @endforelse
</div>
{{ $authors->links() }}
@endsection
