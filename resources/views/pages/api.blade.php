@extends('layouts.app')
@section('title', 'API · Библиотека')
@section('content')
<h1>REST API</h1>
<p class="lead">Базовый адрес: <code>{{ url('/api') }}</code>. Данные в JSON, авторизации нет (учебный проект). Для запросов указывайте заголовок <code>Accept: application/json</code>.</p>
@php
$rows = [
 ['GET','/books?search=','Список книг, по 10 на страницу, поиск без учёта регистра','200'],
 ['GET','/books/{id}','Одна книга с автором','200 / 404'],
 ['POST','/books','Создать книгу','201 / 422'],
 ['PUT, PATCH','/books/{id}','Изменить книгу (PATCH можно частично)','200 / 404 / 422'],
 ['DELETE','/books/{id}','Удалить книгу','200 / 404 / 422'],
 ['GET','/authors','Список авторов','200'],
 ['GET','/authors/{id}','Автор с его книгами','200 / 404'],
 ['POST','/authors','Создать автора','201 / 422'],
 ['PUT, PATCH','/authors/{id}','Изменить автора','200 / 404 / 422'],
 ['DELETE','/authors/{id}','Удалить автора без книг','200 / 404 / 409'],
 ['GET','/readers','Список читателей','200'],
 ['GET','/readers/{id}','Читатель','200 / 404'],
 ['POST','/readers','Создать читателя','201 / 422'],
 ['PUT, PATCH','/readers/{id}','Изменить читателя','200 / 404 / 422'],
 ['DELETE','/readers/{id}','Удалить читателя без книг на руках','200 / 404 / 409'],
 ['GET','/borrowings','Список выдач','200'],
 ['GET','/borrowings/{id}','Одна выдача','200 / 404'],
 ['POST','/borrowings','Выдать книгу: book_id, reader_id, return_date','201 / 422'],
 ['PATCH','/borrowings/{id}/return','Принять книгу обратно','200 / 404 / 422'],
];
@endphp
<table class="table">
    <thead><tr><th>Метод</th><th>Путь</th><th>Что делает</th><th>Коды</th></tr></thead>
    <tbody>
    @foreach ($rows as $r)
        <tr><td data-l="Метод"><b>{{ $r[0] }}</b></td><td data-l="Путь"><code>{{ $r[1] }}</code></td><td data-l="Что делает">{{ $r[2] }}</td><td data-l="Коды">{{ $r[3] }}</td></tr>
    @endforeach
    </tbody>
</table>
<p class="muted">Коллекция для Postman: <a href="{{ asset('postman/Library-API.postman_collection.json') }}">Library-API.postman_collection.json</a>.</p>
@endsection
