# Шаг 04 — Контекст `Shipment`

## Цель

4 таблицы и модели: `shipments`, `shipment_tags`, `shipment_tag` (pivot), `shipment_images`.
Порядок миграций: `shipments` → `shipment_tags` → `shipment_tag` → `shipment_images`.

## 4.1 Миграция `shipments`

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | `id()` | нет | PK |
| `shipment_date` | `date` | нет | Дата отгрузки |
| `location` | `string(255)` | да | Локация |
| `title` | `string(255)` | нет | Название |
| `short_description` | `text` | да | Краткое описание |
| `sort_order` | `unsignedInteger` default 0 | нет | Порядок |
| `is_active` | `boolean` default true | нет | Публикация |
| `timestamps` | — | да | Метки |
| `deleted_at` | `softDeletes` | да | Мягкое удаление |

## 4.2 Миграция `shipment_tags`

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | `id()` | нет | PK |
| `name` | `string(255)` unique | нет | Название тега |
| `timestamps` | — | да | Метки |

## 4.3 Миграция `shipment_tag` (pivot many-to-many)

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | `id()` | нет | PK |
| `shipment_id` | `foreignId` → `shipments.id` | нет | onDelete cascade |
| `shipment_tag_id` | `foreignId` → `shipment_tags.id` | нет | onDelete cascade |

- Уникальный индекс `unique(['shipment_id', 'shipment_tag_id'])`.

```php
Schema::create('shipment_tag', function (Blueprint $table) {
    $table->id();
    $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
    $table->foreignId('shipment_tag_id')->constrained()->cascadeOnDelete();
    $table->unique(['shipment_id', 'shipment_tag_id']);
});
```

## 4.4 Миграция `shipment_images`

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | `id()` | нет | PK |
| `shipment_id` | `foreignId` → `shipments.id` | нет | onDelete cascade |
| `file_name` | `string(255)` | нет | Имя файла |
| `file_path` | `string(512)` | нет | Путь |
| `is_main` | `boolean` default false | нет | Главное изображение |
| `sort_order` | `unsignedInteger` default 0 | нет | Порядок |
| `timestamps` | — | да | Метки |

> Единственность главного изображения на отгрузку гарантируется в Action (сбрасывать
> `is_main` у прочих изображений отгрузки при установке нового главного).

## Модели

Неймспейс `App\Domain\Shipment\Models`.

- **`Shipment`** — `SoftDeletes`; `$casts`: `shipment_date` → `date`, `is_active` → `bool`,
  `sort_order` → `int`. Связи:
  - `tags()` → `belongsToMany(ShipmentTag::class, 'shipment_tag')`
  - `images()` → `hasMany(ShipmentImage::class)`
  - `mainImage()` → `hasOne(ShipmentImage::class)->where('is_main', true)`
- **`ShipmentTag`** — `$fillable`: `name`; `shipments()` → `belongsToMany(Shipment::class, 'shipment_tag')`.
- **`ShipmentImage`** — `$fillable`: `shipment_id`, `file_name`, `file_path`, `is_main`, `sort_order`;
  `$casts`: `is_main` → `bool`; `shipment()` → `belongsTo(Shipment::class)`.

## Definition of Done

- [ ] 4 миграции созданы в правильном порядке и применяются.
- [ ] Уникальный индекс на `shipment_tag`.
- [ ] Модели `Shipment`, `ShipmentTag`, `ShipmentImage` со связями.
