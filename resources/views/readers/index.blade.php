@extends('layouts.app')
@section('title', 'Читатели · Библиотека')
@section('content')
<h1>Читатели</h1>
<table class="table">
    <thead><tr><th>Имя</th><th>Email</th><th>Телефон</th><th>На руках</th></tr></thead>
    <tbody>
    @forelse ($readers as $r)
        <tr>
            <td data-l="Имя">{{ $r->name }}</td>
            <td data-l="Email">{{ $r->email }}</td>
            <td data-l="Телефон">{{ $r->phone }}</td>
            <td data-l="На руках">{{ $r->active_borrowings_count }} из {{ \App\Services\BorrowingService::MAX_ACTIVE_PER_READER }}</td>
        </tr>
    @empty
        <tr><td colspan="4" class="empty">Читателей пока нет.</td></tr>
    @endforelse
    </tbody>
</table>
{{ $readers->links() }}
@endsection
