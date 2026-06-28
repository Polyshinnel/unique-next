# Шаг 07 — `ImageDownloader`

## Цель

Скачать изображения товара (`media/media_item`) на диск `public` и сформировать строки
`product_images`. Идемпотентно (dedup по `external_id`), отказоустойчиво (ошибка одного файла
не валит импорт товара).

## Расположение

`app/Domain/Catalog/Import/Services/ImageDownloader.php`,
неймспейс `App\Domain\Catalog\Import\Services`.

## Поведение

```php
final class ImageDownloader
{
    public function download(Product $product, MediaItemData $media): ?ProductImage
    {
        // 1. dedup: уже есть product_images по (product_id, external_id) с file_path → вернуть его
        // 2. скачать file_url (timeout, проверка 2xx + content-type image/*)
        // 3. сохранить на диск config('catalog_import.image_disk')
        // 4. upsert строки product_images
    }
}
```

### Путь хранения (по папкам)

Фото **обязательно** хранить по папкам — у товара несколько изображений, поэтому каждое
объявление получает собственную директорию внутри общего корня:

```
products/                         ← корневая папка всех фото товаров
└── {product_id}/                 ← папка конкретного товара (id из таблицы products)
    ├── {media_external_id}_{safe_file_name}
    ├── {media_external_id}_{safe_file_name}
    └── ...
```

- **Корень** — `products` (общая для всех товаров).
- **Папка товара** — `products/{product_id}`, где `product_id` — локальный `id` записи в `products`
  (товар к моменту скачивания уже создан в шаге 08, id известен). Все изображения данного товара
  складываются только сюда.
- **Имя файла** — `{media_external_id}_{safe_file_name}`, где `safe_file_name` — хеш или транслит,
  чтобы избежать кириллицы/коллизий; `media_external_id` гарантирует уникальность в рамках папки.
- Диск: `config('catalog_import.image_disk')` (по умолчанию `public`).

```php
$dir  = "products/{$product->id}";
$path = "{$dir}/{$media->externalId}_{$safeFileName}";
// Storage::disk(...)->put($path, $contents) сам создаёт директорию products/{id}
```

> Можно использовать `product_external_id` вместо `product_id`, если папки должны быть стабильны
> между пересозданиями БД — согласовать. По умолчанию папка именуется локальным `product_id`.

### Маппинг в `product_images` (раздел 2.7)

| Поле | Источник |
|---|---|
| `external_id` | `media_item/@id` |
| `file_name` | `file_name` |
| `file_url` | оригинальный `file_url` |
| `mime_type` | `mime_type` |
| `file_size` | `file_size` |
| `sort_order` | `sort_order` |
| `is_main` | `is_main_image` (1/0 → bool) |
| `file_path` | относительный путь на диске после скачивания |

### Сохранение

```php
$contents = Http::timeout(config('catalog_import.http_timeout'))->get($media->fileUrl);
if (! $contents->successful()) {
    Log::warning('Image download failed', [...]);
    return null;   // не валим импорт товара
}
Storage::disk(config('catalog_import.image_disk'))->put($path, $contents->body());
```

## Правила

- **Dedup:** если `product_images` по (`product_id`, `external_id`) уже существует и файл на месте —
  не перекачивать.
- **Только изображения:** `@type="image"`; прочие типы (video и т.п.) пропускать.
- **Отказоустойчивость:** ошибки логировать (`Log::warning`) и продолжать с остальными файлами.
- **storage:link:** перед запуском должен быть выполнен `php artisan storage:link`.
- (Опц.) скачивание картинок можно вынести в под-jobs по товару (раздел 10 плана), чтобы дробить
  нагрузку — но базовая реализация синхронна внутри импорта товара.

## Definition of Done

- [ ] `ImageDownloader::download()` сохраняет файл на диск `public` и upsert'ит `product_images`.
- [ ] Фото хранятся по папкам: корень `products/`, папка товара `products/{product_id}/`, в ней все его изображения.
- [ ] Dedup по (`product_id`, `external_id`); повторно не качает.
- [ ] Ошибка скачивания одного файла логируется и не роняет импорт товара.
- [ ] `file_path` — относительный путь на диске; `file_url` — оригинальный URL.
