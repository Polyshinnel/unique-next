# Шаг 5.1 — Laravel: настроить API-маршруты

**Этап:** 5. Связка Laravel API ↔ Next.js  
**Статус:** [x] Выполнен

## Описание

Настроить Laravel для работы как API-бэкенд: создать файл маршрутов `routes/api.php`, установить и настроить Laravel Sanctum для SPA-аутентификации, настроить CORS.

## Действия

### 1. Установить API-маршруты

Если `routes/api.php` отсутствует:

```bash
php artisan install:api
```

Это создаст:
- `routes/api.php`
- Установит Laravel Sanctum
- Добавит middleware `api` и Sanctum

### 2. Настроить Laravel Sanctum для SPA

Sanctum используется для cookie-based аутентификации (SPA mode).

В `.env.example` добавить:

```env
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:28080
```

### 3. Настроить CORS

Файл `config/cors.php` (если отсутствует — `php artisan config:publish cors`):

```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:28080'),
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

### 4. Базовые маршруты в `routes/api.php`

```php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HealthController;

Route::get('/health', HealthController::class);

// Будущие маршруты:
// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/user', fn (Request $request) => $request->user());
// });
```

## Ключевые моменты

- **`supports_credentials: true`** — обязательно для Sanctum (передача cookies)
- **`SANCTUM_STATEFUL_DOMAINS`** — домены, для которых Sanctum использует cookie-аутентификацию
- Все API-маршруты автоматически получают префикс `/api`

## Зависимости

- Шаг 2.4 (HealthController создан)

## Критерий завершения

- `routes/api.php` существует с базовыми маршрутами
- Sanctum установлен и настроен
- CORS разрешает запросы с фронтенда
