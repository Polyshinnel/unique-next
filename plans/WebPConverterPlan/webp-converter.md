# План: конвертация загружаемых изображений в WebP

## Цель

Сделать общий серверный механизм, который при загрузке изображения через Filament:

- принимает `jpg`, `jpeg`, `JPEG`, `png` и уже готовые `webp`;
- сохраняет загруженный файл сразу, без ожидания конвертации;
- после сохранения записи запускает фоновую задачу;
- в задаче конвертирует `jpg`/`jpeg`/`png` в `webp`;
- после успешной конвертации заменяет путь в базе на `webp`;
- удаляет исходный `jpg`/`jpeg`/`png`, если он больше не используется;
- сначала подключается к баннерам, затем переиспользуется для `og_image`, отгрузок и других изображений.

Идея с очередью нормальная и для админки предпочтительная: создание/редактирование контента не тормозит на CPU-операции, а цель по сжатию достигается автоматически через worker. В течение короткого времени после сохранения в базе может лежать исходный путь, но это приемлемо: файл уже существует, фронт не ломается, а job позже заменит его на WebP.

GIF/BMP пока не конвертировать автоматически. GIF может быть анимированным, BMP редко нужен в пользовательском контенте и лучше не расширять поверхность без отдельной необходимости.

## Текущий контекст проекта

- Баннеры загружаются здесь: `app/Filament/Resources/Banners/Schemas/BannerForm.php`.
- Поле: `FileUpload::make('image')`, диск `public`, директория `banners`.
- В таблице `banners.image` хранится строковый путь до файла.
- Общая валидация загрузок уже вынесена в:
  - `app/Filament/Support/ImageUpload.php`;
  - `app/Rules/ValidImageUpload.php`.
- В проекте уже используется очередь и Horizon; `docker-compose.prod.yml` выставляет `QUEUE_CONNECTION=redis`.
- Dockerfile уже ставит `libjpeg-turbo-dev`, `libpng-dev`, `freetype-dev`, `exif`, `gd`, но GD сейчас собирается без явного `webp`.
- В локальном CLI PHP функция `gd_info()` недоступна, поэтому проверку поддержки WebP нужно делать внутри Docker-контейнера `app`.

## Выбор пакета

Использовать готовую библиотеку `intervention/image-laravel`.

Почему она подходит:

- это официальный Laravel integration package для Intervention Image;
- умеет читать изображения через GD/Imagick и кодировать в WebP;
- пакет не привязывает нас к Filament и подходит для observer/job/import-команд;
- не требует внешнего CLI-бинарника вроде `cwebp`.

Команды:

```bash
composer require intervention/image-laravel
php artisan vendor:publish --provider="Intervention\\Image\\Laravel\\ServiceProvider"
```

После публикации настроить `config/image.php` на GD:

```php
'driver' => Intervention\Image\Drivers\Gd\Driver::class,
'options' => [
    'autoOrientation' => true,
    'decodeAnimation' => true,
    'strip' => true,
],
```

`strip => true` желательно для пользовательских загрузок: EXIF и прочие метаданные не нужны в публичных баннерах.

## Docker

В `Dockerfile` добавить WebP-зависимость для GD:

```dockerfile
RUN apk add --no-cache \
    git curl curl-dev libcurl \
    libpng-dev libzip-dev zip unzip \
    oniguruma-dev icu-dev icu-libs \
    freetype-dev libjpeg-turbo-dev libwebp-dev \
    nginx supervisor shadow su-exec bash tzdata \
    nodejs npm
```

И включить WebP при сборке GD:

```dockerfile
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql bcmath exif gd intl \
        opcache pcntl zip mbstring
```

После изменения пересобрать сервисы, где используется этот образ:

```bash
docker-compose build app scheduler
docker-compose up -d app scheduler
```

Проверка внутри контейнера:

```bash
docker-compose exec app php -r "var_export(gd_info()['WebP Support'] ?? null);"
```

Ожидаемый результат: `true`.

## Конфигурация

Добавить отдельный конфиг, чтобы качество, очередь и политика удаления не были захардкожены.

Файл: `config/images.php`

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

В `.env.example`:

```dotenv
IMAGE_WEBP_QUALITY=82
IMAGE_CONVERSION_QUEUE=images
IMAGE_DELETE_ORIGINAL_AFTER_CONVERSION=true
```

82 - нормальный старт для баннеров: обычно заметно меньше JPEG/PNG, без грубой деградации. Если после визуальной проверки нужно меньше веса, можно опустить до 75-80.

## Общий сервис конвертации

Создать общий сервис:

`app/Domain/Media/Services/ImageToWebpConverter.php`

Ответственность класса:

- определить, нужно ли конвертировать файл;
- читать файл с указанного disk/path;
- кодировать JPEG/PNG в WebP;
- сохранять итоговый WebP рядом с исходником или по явно переданному пути;
- не знать ничего о модели `Banner` или Filament-ресурсе.

Предлагаемый публичный API:

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

Логика `shouldConvertPath()`:

- `null`/пустая строка -> `false`;
- расширение привести к lower-case;
- `jpg`, `jpeg`, `png` -> `true`;
- `webp` -> `false`;
- всё остальное -> `false`.

Логика `buildWebpPath()`:

- сохранить директорию исходника;
- заменить расширение на `.webp`;
- если боимся коллизий, добавить суффикс, например `01jabc.webp` из исходного `01jabc.png`;
- пользовательское имя не использовать для новых upload'ов, Filament и так генерирует безопасное random/ULID-имя.

Логика `convertStoredImage()`:

1. Проверить, что исходный файл существует на диске.
2. Прочитать bytes через `Storage::disk($disk)->get($sourcePath)`.
3. Прочитать изображение через `Intervention\Image\Laravel\Facades\Image::read($contents)`.
4. Закодировать в WebP:
   - `$encoded = Image::read($contents)->toWebp(config('images.webp.quality'));`
5. Сохранить через `Storage::disk($disk)->put($targetPath, (string) $encoded, ['visibility' => 'public'])`.
6. Если `put()` вернул `false`, бросить доменное исключение `ImageConversionException`.
7. Вернуть `$targetPath`.

Отдельное исключение:

`app/Domain/Media/Exceptions/ImageConversionException.php`

Нужно, чтобы job мог логировать понятную причину, а тесты не зависели от текста системных исключений.

## Асинхронный сценарий

Основной сценарий делать не через `saveUploadedFileUsing()` с конвертацией, а через observer + queue.

Поток:

1. Filament сохраняет файл как обычно: `banners/xxx.jpg` или `banners/xxx.png`.
2. Eloquent сохраняет `banners.image` с исходным путём.
3. Observer после commit dispatch'ит job.
4. Job читает исходный файл, создаёт `banners/xxx.webp`.
5. Job проверяет, что в модели всё ещё лежит исходный путь.
6. Если путь не изменился, job обновляет поле на `.webp`.
7. После успешного обновления job удаляет исходник, если включён `IMAGE_DELETE_ORIGINAL_AFTER_CONVERSION`.

Почему нужна проверка из пункта 5: пользователь может заменить изображение раньше, чем worker обработает старую задачу. В этом случае старая job не должна перетереть новый путь.

## Job

Создать:

`app/Domain/Media/Jobs/ConvertModelImageToWebpJob.php`

Поля job:

```php
public function __construct(
    public readonly string $modelClass,
    public readonly int|string $modelKey,
    public readonly string $attribute,
    public readonly string $disk,
    public readonly string $sourcePath,
) {}
```

Поведение `handle()`:

1. Найти модель: `$modelClass::query()->find($modelKey)`.
2. Если модели нет - завершить без ошибки.
3. Если текущее значение `$model->{$attribute}` не равно `$sourcePath` - завершить без обновления.
4. Если `ImageToWebpConverter::shouldConvertPath($sourcePath)` вернул `false` - завершить.
5. Сконвертировать файл в WebP.
6. Ещё раз перечитать модель и проверить, что поле всё ещё равно `$sourcePath`.
7. Обновить атрибут на `$webpPath` без повторного запуска observer:

```php
$modelClass::withoutEvents(function () use ($model, $attribute, $webpPath): void {
    $model->forceFill([$attribute => $webpPath])->save();
});
```

8. Удалить исходный файл, если:
   - обновление прошло успешно;
   - `IMAGE_DELETE_ORIGINAL_AFTER_CONVERSION=true`;
   - исходный путь отличается от итогового;
   - ни одна актуальная запись этой же модели/атрибута больше не ссылается на исходный путь.

Настройки job:

- queue: `config('images.webp.queue')`, например `images`;
- attempts: 3;
- backoff: `[60, 300, 900]`;
- логировать warning при невалидном изображении, но не валить весь пользовательский сценарий.

## Observer для баннеров

Создать:

`app/Domain/Banner/Observers/BannerObserver.php`

Сценарии:

- `created`: если `image` имеет расширение `jpg`/`jpeg`/`png`, dispatch `ConvertModelImageToWebpJob::dispatchAfterCommit(...)`;
- `updated`: если `image` изменился и новый путь конвертируемый, dispatch job после commit;
- `updated`: старый файл удалять не сразу, а только если он не нужен и не участвует в активной конвертации. Проще для первого шага: оставить удаление старых файлов только после успешной job и на `forceDeleted`;
- `deleted`: при soft delete файл не удалять, чтобы восстановление записи не ломалось;
- `forceDeleted`: удалить текущий файл окончательно.

Зарегистрировать observer в `AppServiceProvider::boot()`:

```php
use App\Domain\Banner\Models\Banner;
use App\Domain\Banner\Observers\BannerObserver;

Banner::observe(BannerObserver::class);
```

В observer не должно быть логики кодирования: только проверка пути и dispatch job.

## Подключение к Filament

Для асинхронного сценария не нужно конвертировать файл в `saveUploadedFileUsing()`.

В `app/Filament/Support/ImageUpload.php` добавить отдельный helper для полей, которые будут автоматически уходить в WebP через observer/job:

```php
public const ACCEPT_WEBP_CONVERTIBLE_ATTRIBUTE = 'image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp';

public static function webpConvertibleRule(): ValidationRule
{
    return new ValidImageUpload(['jpg', 'jpeg', 'png', 'webp']);
}

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

Для первого шага заменить в `BannerForm` настройку поля:

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

Важно: при таком подходе сразу после save в БД может быть `banners/xxx.png`, а через несколько секунд после worker - `banners/xxx.webp`.

## Валидация

Текущая `ValidImageUpload` уже:

- проверяет расширение через lower-case;
- разрешает `jpg`, `jpeg`, `png`, `gif`, `bmp`, `webp`;
- проверяет фактическую картинку через `getimagesizefromstring()`.

Для WebP-конвертирующих полей нужно добавить отдельный rule и accept-строку, не ломая существующие формы:

```php
public const ACCEPT_WEBP_CONVERTIBLE_ATTRIBUTE = 'image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp';

public static function webpConvertibleRule(): ValidationRule
{
    return new ValidImageUpload(['jpg', 'jpeg', 'png', 'webp']);
}
```

Для этого `ValidImageUpload` нужно слегка расширить: принимать список разрешённых расширений в конструкторе, а текущий список оставить значением по умолчанию. Тогда старые поля продолжают принимать PNG/GIF/BMP, а новые `webpConvertibleUpload()` принимают JPEG/PNG/WebP.

## Что делать с уже WebP

Если загружен `webp`, job не нужна:

- файл сохраняется как есть;
- observer не dispatch'ит конвертацию;
- в базе сразу остаётся путь `.webp`.

Если позже нужно принудительно пересжимать все WebP, добавить флаг конфигурации:

```php
'reencode_existing_webp' => false,
```

## PNG и альфа-канал

PNG включаем в автоматическую конвертацию.

Риск: WebP поддерживает прозрачность, но при lossy-кодировании возможны артефакты вокруг прозрачных краёв. Для типичных пользовательских баннеров это обычно не критично, а выигрыш по весу важнее.

Если позже окажется, что прозрачность важна для отдельных типов изображений, можно сделать разные политики:

- баннеры: JPEG/PNG -> lossy WebP;
- логотипы/иконки: PNG не конвертировать или использовать lossless WebP;
- OG-картинки: JPEG/PNG -> lossy WebP.

## Удаление старых файлов

Filament сам не удаляет старые файлы, когда поле перезаписывается. В асинхронном сценарии удаление нужно делать аккуратно.

Правила:

- исходник `jpg`/`jpeg`/`png` удалять только после успешной замены поля на `.webp`;
- если job видит, что поле уже изменилось, исходник не трогать в первой реализации;
- при `forceDeleted` удалять текущий файл записи;
- при обычном soft delete файл не удалять.

Отдельной задачей можно добавить периодическую команду очистки orphan-файлов в `storage/app/public/banners`, но не смешивать это с первой реализацией.

## Тесты

### Unit: `ImageToWebpConverterTest`

Файл: `tests/Unit/Media/ImageToWebpConverterTest.php`

Проверить:

- `jpg` конвертируется в `.webp`;
- `jpeg` конвертируется в `.webp`;
- `JPEG`/верхний регистр расширения считается JPEG;
- `png` конвертируется в `.webp`;
- `webp` не требует конвертации;
- файл появляется на `Storage::fake('public')`;
- возвращённый путь начинается с нужной директории, например `banners/`;
- содержимое имеет WebP-сигнатуру:
  - `RIFF` в байтах 0-3;
  - `WEBP` в байтах 8-11;
- при невалидных bytes бросается `ImageConversionException`.

Для тестового изображения можно генерировать небольшой JPEG/PNG программно через GD внутри теста, но тесты нужно запускать в Docker, где GD собран с JPEG/PNG/WebP.

### Unit/Feature: `ConvertModelImageToWebpJobTest`

Проверить:

- job заменяет `banners.image` с `.jpg` на `.webp`;
- job заменяет `banners.image` с `.png` на `.webp`;
- исходный файл удаляется после успешной замены, если включён флаг удаления;
- если поле модели уже изменилось до выполнения job, job не перезаписывает новое значение;
- если модель удалена, job завершается без ошибки;
- если исходный файл отсутствует, job логирует проблему и не меняет БД.

### Observer: `BannerObserverTest`

Проверить:

- создание баннера с `.jpg` dispatch'ит `ConvertModelImageToWebpJob`;
- создание баннера с `.png` dispatch'ит job;
- создание баннера с `.webp` не dispatch'ит job;
- обновление `image` dispatch'ит job только при конвертируемом новом пути.

### API

Обновить `tests/Feature/BannerControllerTest.php`:

- тестовые fixture-пути можно оставить как явные `.webp`, если проверяем итоговое состояние;
- API должен вернуть ровно тот путь, который хранится в БД.

## Ручная проверка

1. Пересобрать контейнеры.
2. Проверить `gd_info()['WebP Support']`.
3. Убедиться, что queue worker/Horizon запущен.
4. Зайти в Filament.
5. Создать баннер с `.jpg`.
6. Сразу после сохранения убедиться, что запись создана и исходный файл доступен.
7. После обработки очереди убедиться, что в `storage/app/public/banners` появился `.webp`.
8. Убедиться, что в БД `banners.image` заменился на путь с `.webp`.
9. Повторить для `.png`.
10. Проверить `/api/banners`: поле `image` отдаёт актуальный путь.
11. Проверить фронт главной страницы: фон баннера грузится.

## Расширение после баннеров

После успешного баннера тем же helper + observer/job можно подключить:

- `app/Filament/Resources/Products/Schemas/ProductForm.php` для `og_image`;
- `app/Filament/Resources/Categories/Schemas/CategoryForm.php` для `og_image`;
- `app/Filament/Resources/PageSeos/Schemas/PageSeoForm.php` для `og_image`;
- `app/Filament/Resources/Shipments/Schemas/ShipmentForm.php` для `images.file_path`.

Для `Product`, `Category`, `PageSeo` и `ShipmentImage` лучше не писать отдельные job-классы. `ConvertModelImageToWebpJob` должен быть универсальным: модель, ключ, атрибут, диск, исходный путь.

Для XML-импорта товаров не привязываться к Filament. Там можно:

- сохранить исходник как сейчас;
- сразу dispatch'ить `ConvertModelImageToWebpJob` для `ProductImage.file_path`;
- или конвертировать синхронно внутри import job, потому что импорт и так уже фоновый.

## Порядок реализации

1. Добавить `intervention/image-laravel`.
2. Опубликовать `config/image.php`.
3. Добавить `config/images.php` и env-переменные.
4. Обновить `Dockerfile`: `libwebp-dev` и `--with-webp`.
5. Создать `app/Domain/Media/Services/ImageToWebpConverter.php`.
6. Создать `app/Domain/Media/Exceptions/ImageConversionException.php`.
7. Создать `app/Domain/Media/Jobs/ConvertModelImageToWebpJob.php`.
8. Расширить `ValidImageUpload` конструктором со списком расширений.
9. Добавить `ImageUpload::webpConvertibleUpload()`.
10. Подключить helper в `BannerForm`.
11. Создать и зарегистрировать `BannerObserver`.
12. Добавить/обновить тесты.
13. Пересобрать Docker и проверить GD WebP.
14. Запустить worker/Horizon и выполнить ручную проверку через Filament и `/api/banners`.
15. После подтверждения распространить на остальные Filament-поля изображений.

## Риски и решения

- **GD без WebP**: сборка пройдёт, но конвертация упадёт. Решение: обязательная проверка `gd_info()['WebP Support']`.
- **Очередь не работает**: записи останутся с исходными JPEG/PNG. Это не ломает контент, но сжатие не произойдёт. Решение: мониторинг Horizon и отдельная artisan-команда backfill.
- **Гонка при быстрой замене изображения**: старая job может прийти позже новой. Решение: job обновляет модель только если текущее значение поля равно исходному path.
- **PNG с важной прозрачностью**: WebP поддерживает alpha, но lossy может дать артефакты. Решение: при необходимости добавить per-field политику или lossless WebP для отдельных сущностей.
- **Старые файлы остаются на диске**: удалять исходник после успешной замены; отдельно добавить cleanup-команду для orphan-файлов.
- **Существующие записи с `.jpg`/`.png`**: не мигрировать автоматически в первом шаге. Отдельно можно сделать artisan-команду backfill-конвертации.
- **GIF/BMP**: не конвертировать без отдельного правила. Для GIF WebP может потерять анимацию.

## Источники

- Intervention Image v3 installation: https://image.intervention.io/v3/getting-started/installation
- Intervention Image Laravel integration: https://image.intervention.io/v3/getting-started/frameworks
- Intervention Image WebP output: https://image.intervention.io/v3/basics/image-output
- Filament FileUpload docs: https://filamentphp.com/docs/4.x/forms/file-upload
