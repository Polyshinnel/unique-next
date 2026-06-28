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
