# Шаг 02 — Миграция таблицы `page_seo`

## Цель

Создать таблицу `page_seo` — по строке на каждую статическую страницу с двойной
идентификацией (`key` + `path`, оба unique).

## Действия

Сгенерировать миграцию:

```bash
php artisan make:migration create_page_seo_table
```

Файл: `database/migrations/<ts>_create_page_seo_table.php`.

## Схема

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | `id()` (bigint UNSIGNED PK) | нет | Идентификатор |
| `key` | `string(64)` unique | нет | Машинный ключ страницы, напр. `home`, `services` |
| `path` | `string(191)` unique | нет | URL-путь страницы, напр. `/`, `/services` |
| `name` | `string(255)` | да | Человекочитаемое название (для админки) |
| `title` | `string(255)` | да | SEO `<title>` |
| `description` | `string(255)` | да | SEO meta description |
| `og_image` | `string(512)` | да | Путь/URL OG-изображения |
| `is_active` | `boolean` default `true` | нет | Признак использования записи |
| `timestamps` | — | да | `created_at` / `updated_at` |

## Код миграции (`up`)

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

`down`: `Schema::dropIfExists('page_seo');`

## Замечания

- `description` — `varchar(255)` (тип `string`) по требованию. Если понадобятся длинные
  описания — поменять на `text` (но тогда `description` не покроется индексом, что не нужно).
- `path` ограничен `191` символами, чтобы безопасно ложиться в `unique`-индекс при
  `utf8mb4` (лимит индекса 767 байт). Для статических путей этого достаточно.
- `og_image` — `512`, чтобы вмещать абсолютные URL.

## Проверка

```bash
php artisan migrate
```

## Definition of Done

- [ ] Миграция `create_page_seo_table` создана.
- [ ] `key` и `path` — unique.
- [ ] `is_active` с default `true`.
- [ ] `php artisan migrate` проходит без ошибок.
