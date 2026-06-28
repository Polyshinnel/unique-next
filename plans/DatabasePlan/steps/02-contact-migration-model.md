# Шаг 02 — Контекст `Contact`

## Цель

Таблица `contacts` (singleton-настройка контактов сайта) + Eloquent-модель.

## Миграция `contacts`

Файл: `database/migrations/<ts>_create_contacts_table.php`

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | `id()` | нет | PK |
| `address` | `string(255)` | да | Адрес |
| `phone` | `string(50)` | да | Телефон |
| `email` | `string(255)` | да | Почта |
| `work_schedule` | `string(255)` | да | График работы |
| `latitude` | `decimal(10,7)` | да | Широта |
| `longitude` | `decimal(10,7)` | да | Долгота |
| `telegram` | `string(255)` | да | Telegram |
| `vk` | `string(255)` | да | ВК |
| `max` | `string(255)` | да | MAX |
| `inn` | `string(20)` | да | ИНН |
| `ogrn` | `string(20)` | да | ОГРН |
| `timestamps` | — | да | `created_at` / `updated_at` |

> Опционально (если потребуется несколько блоков): `is_active` (bool), `sort_order` (int).
> Опционально: `coordinates` `string(255)` для строки координат «как есть».

```php
Schema::create('contacts', function (Blueprint $table) {
    $table->id();
    $table->string('address')->nullable();
    $table->string('phone', 50)->nullable();
    $table->string('email')->nullable();
    $table->string('work_schedule')->nullable();
    $table->decimal('latitude', 10, 7)->nullable();
    $table->decimal('longitude', 10, 7)->nullable();
    $table->string('telegram')->nullable();
    $table->string('vk')->nullable();
    $table->string('max')->nullable();
    $table->string('inn', 20)->nullable();
    $table->string('ogrn', 20)->nullable();
    $table->timestamps();
});
```

## Модель `Contact`

Файл: `app/Domain/Contact/Models/Contact.php`, неймспейс `App\Domain\Contact\Models`.

- `$fillable`: все поля выше (кроме `id`/timestamps).
- `$casts`: `latitude` / `longitude` → `decimal:7`.
- Без связей (изолированная сущность).

## Проверка

- `php artisan migrate` создаёт таблицу.
- `Contact::create([...])` сохраняет запись.

## Definition of Done

- [ ] Миграция `contacts` создана и применяется.
- [ ] Модель `Contact` с `$fillable` и `$casts`.
