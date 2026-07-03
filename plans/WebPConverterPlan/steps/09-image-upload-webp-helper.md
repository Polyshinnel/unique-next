# Шаг 09. Добавить ImageUpload::webpConvertibleUpload()

## Цель

Добавить в общий Filament helper отдельную настройку для полей, которые принимают JPEG/PNG/WebP и после сохранения могут быть конвертированы в WebP через observer/job.

## Зависимости

- Завершен шаг 08.
- Существующий helper `ImageUpload`.

## Файлы

- Изменить: `app/Filament/Support/ImageUpload.php`.

## Задачи

1. Добавить accept-строку:

```php
public const ACCEPT_WEBP_CONVERTIBLE_ATTRIBUTE = 'image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp';
```

2. Добавить метод правила:

```php
public static function webpConvertibleRule(): ValidationRule
{
    return new ValidImageUpload(['jpg', 'jpeg', 'png', 'webp']);
}
```

3. Добавить helper:

```php
public static function webpConvertibleUpload(FileUpload $upload, string $disk, string $directory): FileUpload
{
    return $upload
        ->disk($disk)
        ->directory($directory)
        ->visibility('public')
        ->rules([self::webpConvertibleRule()])
        ->extraInputAttributes([
            'accept' => self::ACCEPT_WEBP_CONVERTIBLE_ATTRIBUTE,
        ]);
}
```

4. Убедиться, что файл импортирует нужные классы:
   - `Filament\Forms\Components\FileUpload`;
   - `Illuminate\Contracts\Validation\ValidationRule`;
   - `App\Rules\ValidImageUpload`.
5. Не менять существующие helper-методы, если они используются другими формами.
6. Не добавлять в helper логику конвертации или dispatch job.

## Важные правила

- Helper только настраивает upload, disk, directory, visibility, validation и accept.
- Асинхронная конвертация запускается observer'ом, а не формой.
- GIF/BMP для этого helper не принимаются.

## Проверка

- Типы и imports корректны.
- Вызов helper компилируется в форме баннера на следующем шаге.
- Старые helper-методы продолжают работать.

## Готово, когда

- `ImageUpload` содержит отдельный WebP-convertible helper.
- Helper можно использовать в `BannerForm`.
