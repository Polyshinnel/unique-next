# Шаг 09 — Тесты и финальный чеклист

## Цель

Покрыть тестами миграцию/модель/сидер, API и доступ к Filament-ресурсу, зафиксировать
итоговую готовность.

## Тесты (Laravel, `tests/Feature`)

Создать `tests/Feature/PageSeoTest.php`. Использовать `RefreshDatabase`.

Сценарии:

1. **Миграция применяется, поля на месте.**
   - После `migrate` таблица `page_seo` существует, есть unique на `key` и `path`.

2. **Сидер идемпотентен.**
   - `php artisan db:seed --class=PageSeoSeeder` создаёт 14 записей.
   - Повторный прогон оставляет 14 (без дублей).

3. **`GET /api/seo/by-key/{key}` отдаёт запись.**
   ```php
   PageSeo::factory()?->create(...); // или updateOrCreate вручную
   $this->getJson('/api/seo/by-key/services')
        ->assertOk()
        ->assertJsonFragment(['key' => 'services']);
   ```

4. **`by-key` для несуществующего/неактивного → 404.**
   ```php
   $this->getJson('/api/seo/by-key/nope')->assertNotFound();
   ```

5. **`GET /api/seo/by-path?path=...` находит по нормализованному пути.**
   - `/services`, `/services/` и `/services?x=1` → одна и та же запись.
   - Проверяет, что нормализация в контроллере совпадает с сидером.

6. **Filament-ресурс доступен (smoke-тест).**
   - Аутентифицированный пользователь открывает `/admin/page-seos` → `200`.
   - (Опционально) Livewire-тест `Pages\CreatePageSeo` создаёт запись и валидирует unique `key`.

## Запуск

```bash
php artisan test --filter=PageSeoTest
```

## Финальный чеклист (раздел 7 плана + Filament)

- [ ] Миграция `page_seo` создана и применяется (`key`/`path` unique). — шаг 02
- [ ] Модель `PageSeo` с `$fillable` и `$casts`. — шаг 03
- [ ] Сидер на 14 страниц, идемпотентный (`updateOrCreate` по `key`). — шаг 04
- [ ] API `by-key` и `by-path` с нормализацией `path`. — шаг 05
- [ ] Хелпер `lib/seo.ts` (`getPageSeo` + `toMetadata`, SSR-запрос). — шаг 06
- [ ] Статические `metadata` страниц переведены на `generateMetadata` с дефолтами. — шаг 07
- [ ] Кэширование ответов SEO на стороне Next.js (`next.revalidate`/`tags`). — шаг 06
- [ ] Filament-ресурс `PageSeoResource` управляет данными `page_seo` (CRUD). — шаг 08
- [ ] Тесты API/миграции/сидера/Filament зелёные. — шаг 09

## Definition of Done

- [ ] `PageSeoTest` покрывает миграцию, сидер, оба эндпоинта, нормализацию `path` и доступ к Filament-ресурсу.
- [ ] `php artisan test --filter=PageSeoTest` проходит.
- [ ] Финальный чеклист закрыт.
