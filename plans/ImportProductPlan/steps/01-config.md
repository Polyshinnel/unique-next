# Шаг 01 — Конфиг импорта и переменные окружения

## Цель

Вынести параметры импорта (URL фида, диск изображений, таймауты) в конфиг и `.env`,
чтобы код импортёра не содержал хардкода и легко переключался между средами.

## Файл `config/catalog_import.php`

Создать новый конфиг:

```php
<?php

return [
    // URL XML-фида панели
    'feed_url' => env(
        'CATALOG_IMPORT_FEED_URL',
        'https://panel.uniqset.com/storage/exports/advertisements.xml',
    ),

    // Диск для сохранения изображений товаров
    'image_disk' => env('CATALOG_IMPORT_IMAGE_DISK', 'public'),

    // Таймаут HTTP-запросов (сек) для фида и картинок
    'http_timeout' => (int) env('CATALOG_IMPORT_HTTP_TIMEOUT', 60),

    // Очередь, в которую ставится job импорта
    'queue' => env('CATALOG_IMPORT_QUEUE', 'imports'),

    // Поведение по умолчанию для существующих товаров (false = строго пропускать)
    'update_existing' => (bool) env('CATALOG_IMPORT_UPDATE_EXISTING', false),
];
```

## Файл `.env.example`

Добавить блок переменных:

```dotenv
# Импорт каталога
CATALOG_IMPORT_FEED_URL=https://panel.uniqset.com/storage/exports/advertisements.xml
CATALOG_IMPORT_IMAGE_DISK=public
CATALOG_IMPORT_HTTP_TIMEOUT=60
CATALOG_IMPORT_QUEUE=imports
CATALOG_IMPORT_UPDATE_EXISTING=false
```

## Замечания

- Значения читаются через `config('catalog_import.*')`, а не напрямую `env()` в коде (кэш конфига).
- После правок прогнать `php artisan config:clear` (и `config:cache` в проде).
- `image_disk` должен существовать в `config/filesystems.php` (по умолчанию `public`).

## Definition of Done

- [ ] `config/catalog_import.php` создан со всеми ключами.
- [ ] Переменные добавлены в `.env.example`.
- [ ] Значения доступны через `config('catalog_import.*')`.
