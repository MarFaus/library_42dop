# Библиотека

Учебный проект на Laravel 12 (PHP 8.2+): книги, авторы, читатели и выдачи. Есть веб-интерфейс (Blade) и REST API (`/api`), общая бизнес-логика лежит в сервисах `BookService` и `BorrowingService`.

## Запуск (Windows, PowerShell)

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item database\database.sqlite -ItemType File -Force
php artisan migrate:fresh --seed
php artisan serve
```

Сайт: http://127.0.0.1:8000, API: http://127.0.0.1:8000/api

Если `composer install` падает с `curl error 60`, скачайте сертификаты и подключите их:

```powershell
Invoke-WebRequest https://curl.se/ca/cacert.pem -OutFile "$HOME\cacert.pem"
composer config -g cafile "$HOME\cacert.pem"
```

## Тесты

```powershell
php artisan test
```

## Эндпоинты

| Метод | Путь | Описание | Коды |
|---|---|---|---|
| GET | /api/books?search= | список (по 10), поиск без учёта регистра | 200 |
| GET | /api/books/{id} | книга с автором | 200, 404 |
| POST | /api/books | создать | 201, 422 |
| PUT / PATCH | /api/books/{id} | изменить (PATCH частично) | 200, 404, 422 |
| DELETE | /api/books/{id} | удалить (нельзя, если книга на руках) | 200, 404, 422 |
| GET, POST | /api/authors | список / создать | 200, 201, 422 |
| GET, PUT, PATCH, DELETE | /api/authors/{id} | удалить нельзя, если есть книги | 200, 404, 409, 422 |
| GET, POST | /api/readers | список / создать (email уникален) | 200, 201, 422 |
| GET, PUT, PATCH, DELETE | /api/readers/{id} | удалить нельзя, если есть книги на руках | 200, 404, 409, 422 |
| GET | /api/borrowings, /api/borrowings/{id} | выдачи | 200, 404 |
| POST | /api/borrowings | выдать: `book_id`, `reader_id`, `return_date` | 201, 422 |
| PATCH | /api/borrowings/{id}/return | принять книгу | 200, 404, 422 |

Правила выдачи: есть свободный экземпляр; читатель не держит эту же книгу; у читателя не больше 5 книг; срок возврата не в прошлом. Авторизации нет (учебный проект).

Ответы об ошибках всегда JSON, даже без заголовка `Accept`.

## Postman

Импортируйте `public/postman/Library-API.postman_collection.json`. Переменная `base_url` по умолчанию `http://127.0.0.1:8000/api`; дата возврата для запроса выдачи подставляется автоматически.

## Структура

- `app/Services`: бизнес-логика (общая для сайта и API)
- `app/Http/Controllers/Api`, `app/Http/Controllers/Web`
- `routes/api.php`, `routes/web.php`
- `tests/Feature`: тесты API и страниц
