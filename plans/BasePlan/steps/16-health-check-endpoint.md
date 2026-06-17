# Шаг 2.4 — Добавить health-check эндпоинт

**Этап:** 2. Настройка Laravel под MySQL + Redis  
**Статус:** [ ] Не выполнен

## Описание

Создать `GET /api/health` эндпоинт, который возвращает JSON со статусом подключений к внешним сервисам.

## Действия

### 1. Создать контроллер (или использовать closure в routes)

Файл: `app/Http/Controllers/Api/HealthController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $health = [
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'services' => [],
        ];

        // Database
        try {
            DB::connection()->getPdo();
            $health['services']['database'] = 'ok';
        } catch (\Throwable $e) {
            $health['services']['database'] = 'error: ' . $e->getMessage();
            $health['status'] = 'degraded';
        }

        // Redis
        try {
            Redis::ping();
            $health['services']['redis'] = 'ok';
        } catch (\Throwable $e) {
            $health['services']['redis'] = 'error: ' . $e->getMessage();
            $health['status'] = 'degraded';
        }

        // Cache
        try {
            Cache::store()->put('health_check', true, 10);
            Cache::store()->get('health_check');
            $health['services']['cache'] = 'ok';
        } catch (\Throwable $e) {
            $health['services']['cache'] = 'error: ' . $e->getMessage();
            $health['status'] = 'degraded';
        }

        $statusCode = $health['status'] === 'ok' ? 200 : 503;

        return response()->json($health, $statusCode);
    }
}
```

### 2. Добавить маршрут

В `routes/api.php`:

```php
use App\Http\Controllers\Api\HealthController;

Route::get('/health', HealthController::class);
```

## Ожидаемый ответ

```json
{
  "status": "ok",
  "timestamp": "2025-01-01T00:00:00+00:00",
  "services": {
    "database": "ok",
    "redis": "ok",
    "cache": "ok"
  }
}
```

При ошибке любого сервиса — `status: "degraded"`, HTTP 503.

## Зависимости

- Шаг 2.1–2.3 (MySQL и Redis настроены)
- `routes/api.php` должен существовать (см. шаг 5.1 — `php artisan install:api`)

## Критерий завершения

`GET /api/health` возвращает JSON с состоянием всех сервисов.
