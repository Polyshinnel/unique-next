# SeoDatabasePlan — Шаги исполнения (индекс)

Разбивка плана [`SeoDatabasePlan.md`](../SeoDatabasePlan.md) на последовательные шаги.
Каждый шаг — отдельный `.md` файл с подробными инструкциями. Выполнять по порядку.

Цель: хранить SEO-данные статических страниц (`title`, `description`, `og_image`) в
таблице `page_seo` и отдавать их фронтенду (Next.js, app root в `resources/js`) на
сервере при рендеринге через `generateMetadata`.

## Стек

- **Laravel** `^13.8`, **PHP** `^8.3`
- **MySQL** `8.4`, кодировка `utf8mb4` / `utf8mb4_unicode_ci`
- **PSR-4**: `App\ => app/` (DDD-неймспейс `App\Domain\Seo\...` подхватывается без правки `composer.json`)
- **Next.js** (app router), общий nginx: `/api` → PHP-FPM (Laravel), остальное → Next.js SSR (3000)

## Порядок выполнения

| # | Файл | Назначение |
|---|---|---|
| 01 | [`01-ddd-structure.md`](01-ddd-structure.md) | Каркас DDD-директорий `app/Domain/Seo/*` |
| 02 | [`02-migration-page-seo.md`](02-migration-page-seo.md) | Миграция таблицы `page_seo` |
| 03 | [`03-model-page-seo.md`](03-model-page-seo.md) | Модель `App\Domain\Seo\Models\PageSeo` |
| 04 | [`04-seeder-page-seo.md`](04-seeder-page-seo.md) | `PageSeoSeeder` — 14 статических страниц |
| 05 | [`05-api-controller-routes.md`](05-api-controller-routes.md) | `PageSeoController` + маршруты `by-key` / `by-path` |
| 06 | [`06-frontend-seo-helper.md`](06-frontend-seo-helper.md) | Хелпер `resources/js/lib/seo.ts` (тип + `getPageSeo` + `toMetadata`) |
| 07 | [`07-frontend-generate-metadata.md`](07-frontend-generate-metadata.md) | Перевод страниц на `generateMetadata` |
| 08 | [`08-filament-page-seo-resource.md`](08-filament-page-seo-resource.md) | Управление `page_seo` через Filament (CRUD в `/admin`) |
| 09 | [`09-tests-checklist.md`](09-tests-checklist.md) | Тесты API/миграции/Filament и финальный чеклист |

## Общие соглашения

- Под статическими понимаются страницы с фиксированным URL, не зависящие от записей
  каталога/отгрузок. SEO `products`/`categories` живёт отдельно (`DatabaseTextPlan.md`, разделы 6.1, 6.2).
- Идентификация двойная: `key` (стабильный машинный ключ для кода) **и** `path` (URL).
  Оба уникальны и индексированы.
- В коде страниц запрашивать SEO по `key` (устойчиво к смене URL); `by-path` — резервный механизм.
- SEO получать **на сервере** (`generateMetadata` + `api.server.get`), не в браузере —
  иначе краулеры и OG-скраперы не увидят теги в первоначальном HTML.
- `path` нормализуется одинаково в сидере и в контроллере: ведущий слэш, без trailing-слэша
  (кроме `/`), без query-параметров.

## Definition of Done (общий чеклист, раздел 7 плана)

- [ ] Миграция `page_seo` создана и применяется (`key`/`path` unique).
- [ ] Модель `PageSeo` с `$fillable` и `$casts`.
- [ ] Сидер на 14 страниц, идемпотентный (`updateOrCreate` по `key`).
- [ ] API `by-key` и `by-path` с нормализацией `path`.
- [ ] Статические `metadata` страниц переведены на `generateMetadata` с дефолтами.
- [ ] Кэширование ответов SEO на стороне Next.js.
- [ ] Filament-ресурс `PageSeoResource` для управления данными `page_seo`.
