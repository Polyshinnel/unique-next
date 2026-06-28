# Шаг 08 — Статусные блоки товара (1:1)

## Цель

3 таблицы с отношением **1:1** к `products` (`product_id` unique). Каждая ссылается на свой
справочник статусов и хранит HTML-комментарий. Создаются **после** `products` и справочников.

| Таблица | XML | FK на справочник | Связь в `Product` |
|---|---|---|---|
| `product_checks` | `check` | `check_status_id` → `check_statuses.id` | `check()` |
| `product_loadings` | `loading` | `shipment_status_id` → `shipment_statuses.id` | `loading()` |
| `product_dismantlings` | `removal` | `dismantle_status_id` → `dismantle_statuses.id` | `dismantling()` |

## Структура

### `product_checks`
| Поле | Тип | Null |
|---|---|---|
| `id` | `id()` | нет |
| `product_id` | `foreignId` → `products.id`, unique, cascade | нет |
| `check_status_id` | `foreignId` → `check_statuses.id`, set null | да |
| `comment` | `longText` | да |
| `timestamps` | — | да |

### `product_loadings`
То же, но `shipment_status_id` → `shipment_statuses.id` (set null).

### `product_dismantlings`
То же, но `dismantle_status_id` → `dismantle_statuses.id` (set null).

```php
Schema::create('product_checks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();
    $table->foreignId('check_status_id')->nullable()->constrained()->nullOnDelete();
    $table->longText('comment')->nullable();
    $table->timestamps();
});
```

## Модели

Неймспейс `App\Domain\Catalog\Models`: `ProductCheck`, `ProductLoading`, `ProductDismantling`.

- `$fillable`: `product_id`, соответствующий `*_status_id`, `comment`.
- Связи: `product()` → `belongsTo(Product::class)`; `status()` → `belongsTo(<Status>::class)`.
- `$table` указать для `product_dismantlings` (мн. число формируется нестандартно).

## Дополнение модели `Product`

```php
public function check()      { return $this->hasOne(ProductCheck::class); }
public function loading()    { return $this->hasOne(ProductLoading::class); }
public function dismantling(){ return $this->hasOne(ProductDismantling::class); }
```

## Definition of Done

- [ ] 3 миграции созданы, `product_id` unique + cascade, FK статусов с `nullOnDelete`.
- [ ] 3 модели со связями `product()` и `status()`.
- [ ] hasOne-связи в `Product`.
