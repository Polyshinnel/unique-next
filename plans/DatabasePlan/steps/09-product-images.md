# Шаг 09 — Изображения товара (1:N)

## Цель

Таблица `product_images` (отношение **1:N** к `products`) + модель. XML-источник `media/media_item`.

## Миграция `product_images`

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | `id()` | нет | PK |
| `external_id` | `unsignedBigInteger` unique | да | ID из XML (`media_item id`) |
| `product_id` | `foreignId` → `products.id`, onDelete cascade | нет | Товар |
| `file_name` | `string(255)` | нет | Имя файла |
| `file_path` | `string(512)` | нет | Путь |
| `file_url` | `string(512)` | да | Полный URL (XML `file_url`) |
| `mime_type` | `string(100)` | да | MIME-тип |
| `file_size` | `unsignedInteger` | да | Размер, байт |
| `is_main` | `boolean` default false | нет | Главное (XML `is_main_image`) |
| `sort_order` | `unsignedInteger` default 0 | нет | Порядок |
| `timestamps` | — | да | Метки |

```php
Schema::create('product_images', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('external_id')->nullable()->unique();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->string('file_name');
    $table->string('file_path', 512);
    $table->string('file_url', 512)->nullable();
    $table->string('mime_type', 100)->nullable();
    $table->unsignedInteger('file_size')->nullable();
    $table->boolean('is_main')->default(false);
    $table->unsignedInteger('sort_order')->default(0);
    $table->timestamps();
});
```

> Единственность главного изображения на товар — на уровне Action (при установке нового
> `is_main` сбрасывать у остальных изображений того же товара).

## Модель `ProductImage`

Неймспейс `App\Domain\Catalog\Models`.

- `$fillable`: `external_id`, `product_id`, `file_name`, `file_path`, `file_url`,
  `mime_type`, `file_size`, `is_main`, `sort_order`.
- `$casts`: `is_main` → `bool`, `sort_order` / `file_size` → `int`.
- Связь `product()` → `belongsTo(Product::class)`.

## Дополнение модели `Product`

```php
public function images()   { return $this->hasMany(ProductImage::class)->orderBy('sort_order'); }
public function mainImage() { return $this->hasOne(ProductImage::class)->where('is_main', true); }
```

## Definition of Done

- [ ] Миграция `product_images` создана и применяется (после `products`).
- [ ] `external_id` unique, FK cascade.
- [ ] Модель `ProductImage` + связи `images()` / `mainImage()` в `Product`.
