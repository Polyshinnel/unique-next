# Шаг 11. Создать и зарегистрировать BannerObserver

## Цель

Подключить баннеры к асинхронной конвертации: после создания или изменения изображения observer должен ставить универсальную job в очередь после commit.

## Зависимости

- Завершен шаг 07.
- Завершен шаг 10.
- Существующая модель `Banner`.

## Файлы

- Создать: `app/Domain/Banner/Observers/BannerObserver.php`.
- Изменить: `app/Providers/AppServiceProvider.php`.

## Задачи

1. Создать директорию `app/Domain/Banner/Observers`, если ее нет.
2. Создать `BannerObserver`.
3. В `created(Banner $banner)`:
   - взять `$banner->image`;
   - если путь конвертируемый, вызвать `ConvertModelImageToWebpJob::dispatchAfterCommit(...)`.
4. В `updated(Banner $banner)`:
   - проверить, что `image` изменился;
   - взять новый путь;
   - если путь конвертируемый, вызвать `dispatchAfterCommit(...)`.
5. Передавать в job:
   - `modelClass`: `Banner::class`;
   - `modelKey`: `$banner->getKey()`;
   - `attribute`: `'image'`;
   - `disk`: `'public'`;
   - `sourcePath`: текущий путь изображения.
6. В `deleted(Banner $banner)` ничего не удалять при soft delete.
7. В `forceDeleted(Banner $banner)` удалить текущий файл изображения с диска `public`, если он есть.
8. Зарегистрировать observer в `AppServiceProvider::boot()`:

```php
use App\Domain\Banner\Models\Banner;
use App\Domain\Banner\Observers\BannerObserver;

Banner::observe(BannerObserver::class);
```

9. Не добавлять код чтения/кодирования изображения в observer.

## Важные правила

- Dispatch должен быть after commit, чтобы job не читала модель до фиксации транзакции.
- Observer не должен удалять старый файл при обычном update в первой реализации.
- Старые исходники удаляются job только после успешной конвертации и замены пути.

## Проверка

Тесты шага 12 должны подтвердить:

- создание баннера с `.jpg` dispatch'ит job;
- создание баннера с `.png` dispatch'ит job;
- создание баннера с `.webp` не dispatch'ит job;
- update `image` dispatch'ит job только для нового конвертируемого пути.

## Готово, когда

- Observer создан.
- Observer зарегистрирован.
- Баннеры ставят конвертацию в очередь после commit.
