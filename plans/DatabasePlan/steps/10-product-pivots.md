# Шаг 10 — Pivot-таблицы товара

## Цель

3 pivot-таблицы many-to-many. Создаются **последними** (после `products`, `tags`, `regions`,
`managers`): `product_tag`, `product_region`, `product_manager`.

## 10.1 `product_tag`

| Поле | Тип | Null |
|---|---|---|
| `id` | `id()` | нет |
| `product_id` | `foreignId` → `products.id`, cascade | нет |
| `tag_id` | `foreignId` → `tags.id`, cascade | нет |

- `unique(['product_id', 'tag_id'])`.

## 10.2 `product_region`

| Поле | Тип | Null |
|---|---|---|
| `id` | `id()` | нет |
| `product_id` | `foreignId` → `products.id`, cascade | нет |
| `region_id` | `foreignId` → `regions.id`, cascade | нет |

- `unique(['product_id', 'region_id'])`. Нужна, т.к. в XML `regions` — массив.

## 10.3 `product_manager` (с ролью)

| Поле | Тип | Null |
|---|---|---|
| `id` | `id()` | нет |
| `product_id` | `foreignId` → `products.id`, cascade | нет |
| `manager_id` | `foreignId` → `managers.id`, cascade | нет |
| `role` | `string(50)` | нет |

- `unique(['product_id', 'manager_id', 'role'])`.
- `role` ∈ { `creator`, `owner`, `regional_representative` }.

```php
Schema::create('product_manager', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->foreignId('manager_id')->constrained()->cascadeOnDelete();
    $table->string('role', 50);
    $table->unique(['product_id', 'manager_id', 'role']);
});
```

> Рекомендуется ввести `product_manager` для полной поддержки импорта. Если достаточно
> одного менеджера на товар — можно ограничиться `products.manager_id` (шаг 06).
> Роль может быть оформлена Enum `App\Domain\Catalog\Enums\ManagerRole`.

## Связи в моделях

В `Product` (неймспейс `App\Domain\Catalog\Models`):

```php
public function tags()     { return $this->belongsToMany(Tag::class, 'product_tag'); }
public function regions()  { return $this->belongsToMany(Region::class, 'product_region'); }
public function managers() { return $this->belongsToMany(Manager::class, 'product_manager')->withPivot('role'); }
```

Обратные связи: `Tag::products()`, `Region::products()`, `Manager::products()` (шаг 05).

## Definition of Done

- [ ] 3 pivot-миграции созданы (последними) с уникальными индексами и cascade.
- [ ] belongsToMany-связи в `Product` (+ `withPivot('role')` для менеджеров).
- [ ] (Опц.) Enum `ManagerRole`.
