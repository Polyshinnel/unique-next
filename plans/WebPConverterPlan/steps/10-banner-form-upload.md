# Шаг 10. Подключить helper в BannerForm

## Цель

Настроить поле изображения баннера так, чтобы оно принимало `jpg`, `jpeg`, `png`, `webp`, сохраняло файл сразу и оставляло конвертацию фоновому observer/job-сценарию.

## Зависимости

- Завершен шаг 09.
- Существующая форма баннеров.

## Файлы

- Изменить: `app/Filament/Resources/Banners/Schemas/BannerForm.php`.

## Задачи

1. Найти поле:

```php
FileUpload::make('image')
```

2. Заменить настройку upload на helper:

```php
ImageUpload::webpConvertibleUpload(
    FileUpload::make('image')->label('Изображение'),
    disk: 'public',
    directory: 'banners',
)
    ->image()
    ->imagePreviewHeight('200')
    ->openable()
    ->downloadable()
    ->columnSpanFull()
    ->helperText('Файл будет сохранён сразу, затем автоматически преобразован в WebP в фоне.');
```

3. Сохранить существующие визуальные настройки поля, если они уже были:
   - preview height;
   - openable/downloadable;
   - column span;
   - label/helper text, если отличаются в текущем коде.
4. Убедиться, что форма импортирует `ImageUpload`.
5. Не использовать `saveUploadedFileUsing()` для синхронной конвертации.

## Важные правила

- После сохранения в БД временно может быть `banners/xxx.jpg` или `banners/xxx.png`.
- После обработки очереди путь должен стать `banners/xxx.webp`.
- Если загружен `webp`, путь остается `.webp` сразу.

## Проверка

- Filament-форма открывается без ошибок.
- Поле принимает JPEG/PNG/WebP.
- GIF/BMP не проходят валидацию для баннеров.

## Готово, когда

- `BannerForm` использует `ImageUpload::webpConvertibleUpload()`.
- Форма не содержит логики конвертации.
