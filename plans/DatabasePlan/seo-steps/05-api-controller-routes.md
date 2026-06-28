# Шаг 05 — API: контроллер `PageSeoController` + маршруты

## Цель

Отдавать SEO статической страницы по `key` (основной путь) и по `path` (резервный).
Эндпоинты используются на сервере Next.js в `generateMetadata`.

## Действия

Сгенерировать контроллер:

```bash
php artisan make:controller Api/PageSeoController
```

Файл: `app/Http/Controllers/Api/PageSeoController.php` (рядом с существующим `HealthController`).

## Контроллер

```php
<?php

namespace App\Http\Controllers\Api;

use App\Domain\Seo\Models\PageSeo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

final class PageSeoController extends Controller
{
    public function byKey(string $key)
    {
        return PageSeo::query()
            ->where('key', $key)
            ->where('is_active', true)
            ->firstOrFail();
    }

    public function byPath(Request $request)
    {
        $raw = $request->query('path', '/');
        $path = $this->normalizePath($raw);

        return PageSeo::query()
            ->where('path', $path)
            ->where('is_active', true)
            ->firstOrFail();
    }

    private function normalizePath(string $path): string
    {
        // Убрать query/hash, привести к ведущему слэшу, срезать trailing (кроме корня).
        $path = parse_url($path, PHP_URL_PATH) ?? $path;
        $path = '/' . ltrim($path, '/');
        $path = rtrim($path, '/');

        return $path === '' ? '/' : $path;
    }
}
```

## Маршруты

Добавить в `routes/api.php`:

```php
use App\Http\Controllers\Api\PageSeoController;

Route::get('/seo/by-key/{key}', [PageSeoController::class, 'byKey']);
Route::get('/seo/by-path', [PageSeoController::class, 'byPath']); // ?path=/services
```

Итоговый префикс — `/api/seo/...` (группа `api` подключает префикс `/api`).

## Замечания

- `firstOrFail()` отдаёт `404`, если записи нет или `is_active = false`. Фронтенд хелпер
  (шаг 06) ловит ошибку и отдаёт дефолты — страница не падает.
- Нормализация `path` обязана совпадать с той, что в сидере (шаг 04), иначе `by-path` не найдёт строку.
- Контроллер тонкий, без сериализаторов: модель сериализуется в JSON напрямую. При желании
  можно добавить API Resource, но для SEO достаточно полей модели.

## Проверка

```bash
curl -s http://127.0.0.1/api/seo/by-key/services
curl -s "http://127.0.0.1/api/seo/by-path?path=/services"
```

Оба возвращают JSON записи `page_seo`. Несуществующий `key` — `404`.

## Definition of Done

- [ ] `PageSeoController` с методами `byKey` / `byPath`.
- [ ] Маршруты `/api/seo/by-key/{key}` и `/api/seo/by-path` зарегистрированы.
- [ ] `path` нормализуется (совпадает с сидером).
- [ ] Несуществующая/неактивная страница → `404`.
