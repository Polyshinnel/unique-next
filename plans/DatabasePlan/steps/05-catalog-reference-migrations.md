# Шаг 05 — Справочники каталога

## Цель

Создать 10 справочных таблиц и модели. Эти таблицы независимы и должны быть созданы
**до** `products`. Большинство имеют `external_id` (unique) для идемпотентного импорта.

Порядок миграций (раздел 9 плана): `regions`, `categories`, `equipment_availabilities`,
`equipment_states`, `check_statuses`, `dismantle_statuses`, `shipment_statuses`,
`tags`, `managers`, `product_statuses`.

## Таблицы

### `regions`
| Поле | Тип | Null |
|---|---|---|
| `id` | `id()` | нет |
| `external_id` | `unsignedBigInteger` unique | да |
| `name` | `string(255)` | нет |
| `city_name` | `string(255)` | да |
| `timestamps` | — | да |

### `categories` (self-ref + SEO)
| Поле | Тип | Null |
|---|---|---|
| `id` | `id()` | нет |
| `external_id` | `unsignedBigInteger` unique | да |
| `name` | `string(255)` | нет |
| `slug` | `string(255)` unique | нет |
| `parent_id` | `foreignId` → `categories.id`, onDelete set null | да |
| `title` | `string(255)` | да |
| `description` | `text` | да |
| `og_image` | `string(512)` | да |
| `timestamps` | — | да |

Индексы: `external_id` (unique), `slug` (unique), `parent_id`.

```php
$table->foreignId('parent_id')->nullable()
      ->constrained('categories')->nullOnDelete();
```

### `equipment_availabilities`
`id`, `external_id` (unique, nullable), `name` `string(255)`, `color` `string(7)` nullable, `timestamps`.

### `equipment_states`
`id`, `external_id` (unique, nullable), `name` `string(255)`, `timestamps`.

### `check_statuses`
`id`, `external_id` (unique, nullable), `name` `string(255)`, `color` `string(7)` nullable, `timestamps`.

### `dismantle_statuses`
`id`, `external_id` (unique, nullable), `name` `string(255)`, `timestamps`.

### `shipment_statuses`
`id`, `external_id` (unique, nullable), `name` `string(255)`, `timestamps`.

> `shipment_statuses` и `dismantle_statuses` — два независимых справочника, оба наполняются
> из XML `install_statuses` (см. шаг 12).

### `tags`
`id`, `name` `string(255)` unique, `timestamps`. (Без `external_id` — upsert по `name`.)

### `managers`
| Поле | Тип | Null |
|---|---|---|
| `id` | `id()` | нет |
| `external_id` | `unsignedBigInteger` unique | да |
| `name` | `string(255)` | нет |
| `phone` | `string(50)` | да |
| `email` | `string(255)` | да |
| `vk` | `string(255)` | да |
| `max` | `string(255)` | да |
| `telegram` | `string(255)` | да |
| `role` | `string(100)` | да |
| `timestamps` | — | да |

> Опционально: `has_whatsapp` / `has_telegram` (`boolean`).

### `product_statuses`
`id`, `external_id` (unique, nullable), `name` `string(255)`, `color` `string(7)` nullable, `timestamps`.

## Модели

Неймспейс `App\Domain\Catalog\Models`. По модели на таблицу:
`Region`, `Category`, `EquipmentAvailability`, `EquipmentState`, `CheckStatus`,
`DismantleStatus`, `ShipmentStatus`, `Tag`, `Manager`, `ProductStatus`.

- Каждой модели задать `$table` (если имя не выводится автоматически) и `$fillable`.
- `Category`: связи `parent()` → `belongsTo(Category::class, 'parent_id')`,
  `children()` → `hasMany(Category::class, 'parent_id')`, `products()` → `hasMany(Product::class)`.
- Справочники-статусы: `products()` / соответствующие `hasMany` к `products`/`product_*`
  (детализируются в шагах 06–08).
- `Tag`: `products()` → `belongsToMany(Product::class, 'product_tag')`.
- `Region`: `products()` → `belongsToMany(Product::class, 'product_region')`.

## Definition of Done

- [ ] 10 миграций созданы в правильном порядке и применяются.
- [ ] `external_id` unique там, где он есть; `slug` unique у `categories`; `name` unique у `tags`.
- [ ] Модели справочников с `$fillable`.
