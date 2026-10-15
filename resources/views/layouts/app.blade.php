<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Библиотека')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Golos+Text:wght@400;500;600&family=Literata:opsz,wght@7..72,500;7..72,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/library.css') }}">
</head>
<body>
<header class="top">
    <div class="wrap top__row">
        <a class="brand" href="{{ route('books.index') }}"><span class="brand__mark">Б</span> Библиотека</a>
        <nav class="nav">
            <a href="{{ route('books.index') }}" @class(['is-on' => request()->routeIs('books.*')])>Книги</a>
            <a href="{{ route('authors.index') }}" @class(['is-on' => request()->routeIs('authors.*')])>Авторы</a>
            <a href="{{ route('readers.index') }}" @class(['is-on' => request()->routeIs('readers.*')])>Читатели</a>
            <a href="{{ route('borrowings.index') }}" @class(['is-on' => request()->routeIs('borrowings.*')])>Выдачи</a>
            <a href="{{ route('api.docs') }}" @class(['is-on' => request()->routeIs('api.docs')])>API</a>
        </nav>
    </div>
</header>
<main class="wrap main">
    @if (session('status'))
        <div class="flash flash--ok" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any() && ! request()->routeIs('borrowings.*'))
        <div class="flash flash--err" role="alert">{{ $errors->first() }}</div>
    @endif
    @yield('content')
</main>
<footer class="foot wrap">Учебный проект · Laravel {{ app()->version() }} · REST API на <code>/api</code></footer>
</body>
</html>
