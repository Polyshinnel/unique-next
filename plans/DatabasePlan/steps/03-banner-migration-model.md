# Шаг 03 — Контекст `Banner`

## Цель

Таблица `banners` (баннеры главной страницы) + Eloquent-модель с `softDeletes`.

## Миграция `banners`

Файл: `database/migrations/<ts>_create_banners_table.php`

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | `id()` | нет | PK |
| `image` | `string(512)` | да | Путь к изображению |
| `title` | `string(255)` | да | Заголовок |
| `text` | `text` | да | Текст баннера |
| `button_one_text` | `string(255)` | да | Текст кнопки 1 |
| `button_one_url` | `string(512)` | да | Ссылка кнопки 1 |
| `button_two_text` | `string(255)` | да | Текст кнопки 2 |
| `button_two_url` | `string(512)` | да | Ссылка кнопки 2 |
| `sort_order` | `unsignedInteger` default 0 | нет | Порядок |
| `is_active` | `boolean` default true | нет | Публикация |
| `timestamps` | — | да | Системные метки |
| `deleted_at` | `softDeletes` | да | Мягкое удаление |

```php
Schema::create('banners', function (Blueprint $table) {
    $table->id();
    $table->string('image', 512)->nullable();
    $table->string('title')->nullable();
    $table->text('text')->nullable();
    $table->string('button_one_text')->nullable();
    $table->string('button_one_url', 512)->nullable();
    $table->string('button_two_text')->nullable();
    $table->string('button_two_url', 512)->nullable();
    $table->unsignedInteger('sort_order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
});
```

## Модель `Banner`

Файл: `app/Domain/Banner/Models/Banner.php`, неймспейс `App\Domain\Banner\Models`.

- Трейт `Illuminate\Database\Eloquent\SoftDeletes`.
- `$fillable`: все контентные поля + `sort_order`, `is_active`.
- `$casts`: `is_active` → `bool`, `sort_order` → `int`.
- Опционально scope `active()` (`where('is_active', true)`), сортировка по `sort_order`.

## Definition of Done

- [ ] Миграция `banners` создана и применяется.
- [ ] Модель `Banner` с `SoftDeletes`, `$fillable`, `$casts`.
