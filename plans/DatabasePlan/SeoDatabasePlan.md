# SeoDatabasePlan — SEO статических страниц (Laravel 13 + MySQL 8.4, DDD)

## 1. Обзор

Документ описывает структуру хранения SEO-данных (`title`, `description`, `og_image`)
для **статических страниц** сайта `uniqset2.com` и способ их получения фронтендом
(Next.js, app root в `resources/js`).

Под статическими страницами понимаются страницы с фиксированным URL, не зависящие
от записей каталога/отгрузок (у `products` и `categories` SEO-поля уже есть в
`DatabaseTextPlan.md`, разделы 6.1 и 6.2). Для них вводится отдельный контекст.

### Контекст

| Контекст (Domain) | Назначение | Таблица |
|---|---|---|
| `Seo` | SEO статических страниц | `page_seo` |

Соглашения (кодировка, ключи, метки) — те же, что в `DatabaseTextPlan.md`, раздел 2.

---

## 2. DDD-структура

```text
app/
└── Domain/
    └── Seo/
        ├── Models/         # PageSeo
        ├── Data/           # DTO (PageSeoData)
        ├── Actions/        # UpsertPageSeoAction и т.п.
        └── Repositories/   # PageSeoRepository (lookup по key/path)
```

PSR-4 (`App\ => app/`) уже настроен — изменений в `composer.json` не требуется.

---

## 3. Таблица `page_seo`

Каждая строка — SEO одной статической страницы. Идентификация двойная:
стабильный машинный ключ `key` (для ссылок из кода) **и** `path` (URL страницы,
для поиска по адресу). Оба — уникальны.

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | bigint UNSIGNED PK | нет | Идентификатор |
| `key` | varchar(64) | нет | Машинный ключ страницы (unique), напр. `home`, `services` |
| `path` | varchar(191) | нет | URL-путь страницы (unique), напр. `/`, `/services` |
| `name` | varchar(255) | да | Человекочитаемое название (для админки) |
| `title` | varchar(255) | да | SEO `<title>` |
| `description` | varchar(255) | да | SEO meta description |
| `og_image` | varchar(512) | да | Путь/URL OG-изображения |
| `is_active` | boolean | нет (default true) | Признак использования записи |
| `created_at` / `updated_at` | timestamp | да | Системные метки |

> `description` — `varchar(255)` по требованию (тип `string`). Если потребуются более
> длинные описания, поле меняется на `text`.
>
> `path` ограничен `191` символами, чтобы безопасно ложиться в `unique`-индекс при
> `utf8mb4` (лимит индекса 767 байт). Для статических путей этого достаточно.

### Миграция (фрагмент)

```php
Schema::create('page_seo', function (Blueprint $table) {
    $table->id();
    $table->string('key', 64)->unique();
    $table->string('path', 191)->unique();
    $table->string('name')->nullable();
    $table->string('title')->nullable();
    $table->string('description')->nullable();
    $table->string('og_image', 512)->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

### Модель `PageSeo`

Файл: `app/Domain/Seo/Models/PageSeo.php`, неймспейс `App\Domain\Seo\Models`.

- `$table = 'page_seo';`
- `$fillable = ['key', 'path', 'name', 'title', 'description', 'og_image', 'is_active'];`
- `$casts = ['is_active' => 'bool'];`

---

## 4. Список статических страниц (сидер)

URL-пути взяты из текущих маршрутов Next.js (`resources/js/app/**/page.tsx`).

| `key` | `path` | Страница | Источник маршрута |
|---|---|---|---|
| `home` | `/` | Главная | `app/page.tsx` |
| `services` | `/services` | Услуги | `app/services/page.tsx` |
| `catalog` | `/catalog` | Каталог оборудования | `app/catalog/page.tsx` |
| `shipments` | `/otgruzki` | Отгрузки | `app/otgruzki/page.tsx` |
| `company` | `/about` | Компания | `app/about/page.tsx` |
| `contacts` | `/contacts` | Контакты | `app/contacts/page.tsx` |
| `why_we` | `/why-we` | Почему мы | `app/why-we/page.tsx` |
| `vacancy` | `/vacancy` | Вакансии | `app/vacancy/page.tsx` |
| `privacy_policy` | `/private-policy` | Политика конфиденциальности | `app/private-policy/page.tsx` |
| `labor_safety` | `/ohrana-truda` | Охрана труда | `app/ohrana-truda/page.tsx` |
| `equipment_sale` | `/services/prodazha-oborudovaniya` | Продажа оборудования | `app/services/prodazha-oborudovaniya/page.tsx` |
| `equipment_buyout` | `/services/vykup` | Выкуп оборудования | `app/services/vykup/page.tsx` |
| `tools_sale` | `/services/prodazha-instrumenta` | Продажа инструмента | `app/services/prodazha-instrumenta/page.tsx` |
| `equipment_import` | `/services/import-oborudovaniya` | Импорт оборудования | `app/services/import-oborudovaniya/page.tsx` |

Сидер `PageSeoSeeder` создаёт 14 записей через `updateOrCreate(['key' => ...], [...])`,
что делает повторный прогон идемпотентным и безопасным при добавлении новых страниц.

---

## 5. Как фронтенд получает SEO «по URL»

Главный вопрос: **получать данные по URL**. Да — это рабочий и рекомендуемый подход,
с одной оговоркой про стабильность.

### Рекомендация: поиск по `key`, fallback по `path`

- **В коде страницы** запрашивайте SEO по стабильному `key` (например, `home`),
  а не по URL-строке. Так SEO не «сломается», если URL поменяется (например,
  `/otgruzki` → `/shipments`) — достаточно обновить `path` в БД.
- **Поиск по `path`** оставляйте как универсальный механизм (middleware, общий лэйаут,
  карта сайта), когда `key` страницы заранее неизвестен.

Оба варианта дёшевы: `key` и `path` — уникальные индексированные столбцы.

### Архитектура запроса (как это работает в проекте)

Next.js и Laravel живут в одном контейнере за общим nginx (`docker/nginx/default.conf`):
`/api`, `/sanctum`, `/admin` → PHP-FPM (Laravel); всё остальное → Next.js SSR (порт 3000).

SEO-данные **обязательно получаются на сервере при рендеринге** (`generateMetadata`),
а не в браузере — иначе краулеры и OG-скраперы не увидят теги в первоначальном HTML.

Уже есть готовый клиент `resources/js/lib/api.ts` с двумя режимами:

- `api.server.get()` — SSR-запрос на `BACKEND_URL/api` (`http://127.0.0.1/api`),
  идёт через nginx → PHP-FPM **на сервере**. Используется для SEO.
- `api.get()` — браузерный same-origin запрос на `/api`.

Поток данных:

```text
Браузер/краулер → GET /services
  → nginx → Next.js SSR (3000)
     → generateMetadata() на сервере
        → api.server.get('/seo/by-key/services')
           → http://127.0.0.1/api/seo/by-key/services
              → nginx → PHP-FPM (Laravel) → page_seo → JSON
     → Next.js рендерит <title>/<meta>/og в HTML
  ← готовый HTML с SEO-тегами
```

### Backend API (Laravel)

Тонкий контроллер отдаёт SEO. Два эндпоинта в `routes/api.php`:

```php
// routes/api.php
use App\Http\Controllers\Api\PageSeoController;

Route::get('/seo/by-key/{key}', [PageSeoController::class, 'byKey']);
Route::get('/seo/by-path', [PageSeoController::class, 'byPath']); // ?path=/services
```

```php
final class PageSeoController
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
        $path = '/' . ltrim($request->query('path', '/'), '/');

        return PageSeo::query()
            ->where('path', $path)
            ->where('is_active', true)
            ->firstOrFail();
    }
}
```

> Нормализуйте `path` перед поиском: ведущий слэш, без trailing-слэша (кроме `/`),
> без query-параметров. Та же нормализация — в сидере, чтобы значения совпадали.

### Frontend (Next.js, app router) — что добавить в сами страницы

Чтобы страница получала SEO по `key`, в неё нужно добавить **серверную функцию
`generateMetadata`**, использующую существующий клиент `api.server.get`. Это всё, что
требуется на стороне страницы — компонент рендера трогать не нужно.

**Шаг 1. Тип ответа и общий хелпер** (один раз, напр. в `resources/js/lib/seo.ts`):

```ts
import type { Metadata } from 'next';
import { api } from '@/lib/api';

export interface PageSeo {
    key: string;
    path: string;
    title: string | null;
    description: string | null;
    og_image: string | null;
}

// Запрос по key на сервере; null, если записи нет (404) — страница отдаст дефолты.
export async function getPageSeo(key: string): Promise<PageSeo | null> {
    try {
        return await api.server.get<PageSeo>(`/seo/by-key/${key}`, {
            // SEO меняется редко — кэшируем и периодически ревалидируем.
            next: { revalidate: 3600, tags: [`seo:${key}`] },
        } as never);
    } catch {
        return null;
    }
}

// Превращает запись page_seo в объект Metadata с дефолтами.
export function toMetadata(seo: PageSeo | null, fallback: Metadata): Metadata {
    if (!seo) return fallback;

    return {
        title: seo.title ?? fallback.title,
        description: seo.description ?? fallback.description,
        openGraph: seo.og_image
            ? { images: [{ url: seo.og_image }] }
            : fallback.openGraph,
    };
}
```

**Шаг 2. На каждой статической странице** заменяем статический `export const metadata`
на `generateMetadata` с нужным `key` (см. таблицу раздела 4):

```ts
// app/services/page.tsx
import type { Metadata } from 'next';
import { getPageSeo, toMetadata } from '@/lib/seo';

export async function generateMetadata(): Promise<Metadata> {
    const seo = await getPageSeo('services');

    return toMetadata(seo, {
        title: 'Услуги | ЮНИК С',
        description:
            'Услуги ЮНИК С: продажа, выкуп, поставка, импорт и сопровождение сделок.',
    });
}
```

Итого по страницам:

- **Удалить** статический `export const metadata = {...}` (там, где он есть —
  `app/services/page.tsx`, `app/catalog/page.tsx`, `app/contacts/page.tsx` и т.д.).
- **Добавить** `export async function generateMetadata()` с вызовом `getPageSeo('<key>')`.
- **Оставить дефолты** в `toMetadata(...)` — на случай, если записи в БД ещё нет.
- Текущие статические значения `metadata` сначала перенести в сидер `page_seo`
  (раздел 4), затем заменить на `generateMetadata`.
- `generateMetadata` — серверная функция, поэтому используем именно `api.server.get`
  (не `api.get` и не клиентские хуки): теги попадут в первоначальный HTML.

---

## 6. Порядок реализации

1. Миграция `page_seo` (раздел 3).
2. Модель `App\Domain\Seo\Models\PageSeo`.
3. `PageSeoSeeder` — 14 записей (раздел 4), `updateOrCreate` по `key`.
4. `PageSeoController` + маршруты `by-key` / `by-path` (раздел 5).
5. Перенос текущих `metadata` страниц в `generateMetadata` с запросом к API.

### Сидер (фрагмент)

```php
$pages = [
    ['key' => 'home', 'path' => '/', 'name' => 'Главная'],
    ['key' => 'services', 'path' => '/services', 'name' => 'Услуги'],
    // ... остальные 12 строк из таблицы раздела 4
];

foreach ($pages as $page) {
    PageSeo::updateOrCreate(['key' => $page['key']], $page);
}
```

---

## 7. Чеклист

- [ ] Миграция `page_seo` создана и применяется (`key`/`path` unique).
- [ ] Модель `PageSeo` с `$fillable` и `$casts`.
- [ ] Сидер на 14 страниц, идемпотентный (`updateOrCreate` по `key`).
- [ ] API `by-key` и `by-path` с нормализацией `path`.
- [ ] Статические `metadata` страниц переведены на `generateMetadata` с дефолтами.
- [ ] Кэширование ответов SEO на стороне Next.js.
