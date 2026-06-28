# Шаг 06 — Таблица `products`

## Цель

Центральная таблица каталога + модель `Product`. Создаётся **после** всех справочников
(шаг 05) и **до** зависимых таблиц (шаги 07–10).

## Миграция `products`

Файл: `database/migrations/<ts>_create_products_table.php`

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | `id()` | нет | PK |
| `external_id` | `unsignedBigInteger` unique | да | ID объявления из XML |
| `name` | `string(255)` | нет | Название (XML `title`) |
| `sku` | `string(100)` unique | да | Артикул |
| `description` | `text` | да | Описание |
| `og_image` | `string(512)` | да | OG-изображение |
| `category_id` | `foreignId` → `categories.id`, set null | да | Категория |
| `manager_id` | `foreignId` → `managers.id`, set null | да | Менеджер-владелец |
| `equipment_state_id` | `foreignId` → `equipment_states.id`, set null | да | Состояние |
| `equipment_availability_id` | `foreignId` → `equipment_availabilities.id`, set null | да | Доступность |
| `product_status_id` | `foreignId` → `product_statuses.id`, set null | да | Статус товара |
| `price` | `decimal(10,2)` | да | Цена |
| `show_price` | `boolean` default true | нет | Отображать цену |
| `price_comment` | `text` | да | Комментарий по цене |
| `product_address` | `string(255)` | да | Адрес товара |
| `published_at` | `timestamp` | да | Дата публикации |
| `timestamps` | — | да | Метки |
| `deleted_at` | `softDeletes` | да | Мягкое удаление |

```php
$table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
$table->foreignId('manager_id')->nullable()->constrained()->nullOnDelete();
$table->foreignId('equipment_state_id')->nullable()->constrained()->nullOnDelete();
$table->foreignId('equipment_availability_id')->nullable()->constrained()->nullOnDelete();
$table->foreignId('product_status_id')->nullable()->constrained()->nullOnDelete();
```

Индексы: `external_id` (unique), `sku` (unique). Индексы на FK создаются `constrained()` автоматически.

## Модель `Product`

Файл: `app/Domain/Catalog/Models/Product.php`, неймспейс `App\Domain\Catalog\Models`.

- Трейт `SoftDeletes`.
- `$casts`: `price` → `decimal:2`, `show_price` → `bool`, `published_at` → `datetime`.
- Связи **belongsTo**: `category()`, `manager()`, `equipmentState()`,
  `equipmentAvailability()`, `productStatus()`.
- Связи **hasOne** (контентные/статусные блоки, шаги 07–08):
  `mainCharacteristics()`, `complectation()`, `technicalCharacteristics()`,
  `mainInfo()`, `additionalInfo()`, `check()`, `loading()`, `dismantling()`.
- Связи **hasMany / belongsToMany** (шаги 09–10):
  `images()` → `hasMany(ProductImage::class)`,
  `mainImage()` → `hasOne(ProductImage::class)->where('is_main', true)`,
  `tags()` → `belongsToMany(Tag::class, 'product_tag')`,
  `regions()` → `belongsToMany(Region::class, 'product_region')`,
  `managers()` → `belongsToMany(Manager::class, 'product_manager')->withPivot('role')`.

> Связи на сущности из шагов 07–10 добавляются по мере создания соответствующих таблиц/моделей.

## Definition of Done

- [ ] Миграция `products` создана и применяется (после справочников).
- [ ] Все FK с корректным `nullOnDelete`.
- [ ] `external_id` / `sku` unique.
- [ ] Модель `Product` с `SoftDeletes`, `$casts`, belongsTo-связями.
