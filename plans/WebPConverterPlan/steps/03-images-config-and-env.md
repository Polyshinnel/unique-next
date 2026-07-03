# Шаг 03. Добавить config/images.php и env-переменные

## Цель

Вынести качество WebP, имя очереди, политику удаления исходника и список конвертируемых расширений в конфигурацию.

## Зависимости

- Нет жесткой зависимости от предыдущих шагов, но выполнять после настройки пакета удобнее.

## Файлы

- Создать: `config/images.php`.
- Изменить: `.env.example`.

## Задачи

1. Создать `config/images.php`.
2. Добавить секцию `webp`:

```php
<?php

return [
    'webp' => [
        'quality' => env('IMAGE_WEBP_QUALITY', 82),
        'queue' => env('IMAGE_CONVERSION_QUEUE', 'images'),
        'delete_original_after_conversion' => env('IMAGE_DELETE_ORIGINAL_AFTER_CONVERSION', true),
        'convert_extensions' => ['jpg', 'jpeg', 'png'],
    ],
];
```

3. Добавить в `.env.example`:

```dotenv
IMAGE_WEBP_QUALITY=82
IMAGE_CONVERSION_QUEUE=images
IMAGE_DELETE_ORIGINAL_AFTER_CONVERSION=true
```

4. Использовать `config('images.webp.*')` в сервисе и job вместо захардкоженных значений.
5. Не добавлять пока `reencode_existing_webp`: по плану существующие WebP не пересжимаются.

## Важные правила

- Стартовое качество: `82`.
- Очередь по умолчанию: `images`.
- Исходники удаляются только после успешной замены пути в модели.
- Конвертируются только `jpg`, `jpeg`, `png`.

## Проверка

```bash
php artisan config:clear
php artisan tinker
```

В tinker:

```php
config('images.webp.quality')
config('images.webp.queue')
config('images.webp.convert_extensions')
```

## Готово, когда

- Конфиг добавлен.
- `.env.example` обновлен.
- Значения доступны через Laravel config.
