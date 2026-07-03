# Шаг 06. Создать ImageToWebpConverter

## Цель

Создать общий сервис, который умеет определить необходимость конвертации, построить путь `.webp`, прочитать файл с storage disk, закодировать его в WebP и сохранить результат.

## Зависимости

- Завершен шаг 01.
- Завершен шаг 02.
- Завершен шаг 03.
- Завершен шаг 05.

## Файлы

- Создать: `app/Domain/Media/Services/ImageToWebpConverter.php`.

## Публичный API

```php
namespace App\Domain\Media\Services;

final class ImageToWebpConverter
{
    public function shouldConvertPath(?string $path): bool;

    public function buildWebpPath(string $path): string;

    public function convertStoredImage(
        string $disk,
        string $sourcePath,
        ?string $targetPath = null,
    ): string;
}
```

## Задачи

1. Создать директорию `app/Domain/Media/Services`, если ее нет.
2. Реализовать `shouldConvertPath(?string $path): bool`:
   - `null` и пустая строка дают `false`;
   - расширение приводится к lower-case;
   - `jpg`, `jpeg`, `png` дают `true`;
   - `webp` дает `false`;
   - все прочее дает `false`;
   - список конвертируемых расширений брать из `config('images.webp.convert_extensions')`.
3. Реализовать `buildWebpPath(string $path): string`:
   - сохранить исходную директорию;
   - заменить расширение на `.webp`;
   - не использовать пользовательское имя для нового upload, так как Filament уже генерирует безопасное имя.
4. Реализовать `convertStoredImage(...)`:
   - проверить, что файл существует на `Storage::disk($disk)`;
   - прочитать bytes через `get($sourcePath)`;
   - прочитать изображение через `Intervention\Image\Laravel\Facades\Image::read($contents)`;
   - закодировать в WebP с качеством `config('images.webp.quality')`;
   - сохранить результат через `Storage::disk($disk)->put($targetPath, (string) $encoded, ['visibility' => 'public'])`;
   - если `put()` вернул `false`, бросить `ImageConversionException`;
   - вернуть итоговый `$targetPath`.
5. Обернуть ошибки чтения/кодирования в `ImageConversionException`, сохраняя предыдущую ошибку как `$previous`.
6. Не добавлять зависимости от `Banner`, Filament или Eloquent.

## Важные правила

- `webp` не конвертируется и не пересжимается.
- GIF/BMP не конвертируются.
- Сервис не обновляет базу и не удаляет исходник.
- Сервис должен подходить для observer/job/import-команд.

## Проверка

Проверка покрывается тестами шага 12:

- JPEG превращается в WebP.
- PNG превращается в WebP.
- Верхний регистр расширения распознается.
- WebP не требует конвертации.
- Невалидные bytes дают `ImageConversionException`.

## Готово, когда

- Сервис создан.
- Его методы не зависят от конкретной модели.
- Результат сохраняется на тот же disk и возвращается как path.
