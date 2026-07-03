# Шаг 12. Добавить и обновить тесты

## Цель

Покрыть новый механизм на уровнях сервиса, job, observer и API, чтобы защитить конвертацию от регрессий и гонок.

## Зависимости

- Завершены шаги 05-11.
- Для тестов кодирования нужен PHP GD с поддержкой JPEG/PNG/WebP. Надежнее запускать тесты внутри Docker после шага 13.

## Файлы

- Создать: `tests/Unit/Media/ImageToWebpConverterTest.php`.
- Создать или изменить: `tests/Feature/Media/ConvertModelImageToWebpJobTest.php`.
- Создать или изменить: `tests/Feature/BannerObserverTest.php`.
- Изменить: `tests/Feature/BannerControllerTest.php`.
- При необходимости добавить helper для генерации маленьких JPEG/PNG изображений.

## Тесты ImageToWebpConverterTest

Проверить:

1. `jpg` конвертируется в `.webp`.
2. `jpeg` конвертируется в `.webp`.
3. `JPEG`/верхний регистр расширения считается JPEG.
4. `png` конвертируется в `.webp`.
5. `webp` не требует конвертации.
6. Файл появляется на `Storage::fake('public')`.
7. Возвращенный путь начинается с нужной директории, например `banners/`.
8. Содержимое имеет WebP-сигнатуру:
   - `RIFF` в байтах 0-3;
   - `WEBP` в байтах 8-11.
9. Невалидные bytes вызывают `ImageConversionException`.

## Тесты ConvertModelImageToWebpJobTest

Проверить:

1. Job заменяет `banners.image` с `.jpg` на `.webp`.
2. Job заменяет `banners.image` с `.png` на `.webp`.
3. Исходный файл удаляется после успешной замены, если включен флаг удаления.
4. Если поле модели изменилось до выполнения job, job не перезаписывает новое значение.
5. Если модель удалена, job завершается без ошибки.
6. Если исходный файл отсутствует, job логирует проблему и не меняет БД.

## Тесты BannerObserverTest

Проверить:

1. Создание баннера с `.jpg` dispatch'ит `ConvertModelImageToWebpJob`.
2. Создание баннера с `.png` dispatch'ит job.
3. Создание баннера с `.webp` не dispatch'ит job.
4. Обновление `image` dispatch'ит job только при конвертируемом новом пути.

## API тесты

Обновить `tests/Feature/BannerControllerTest.php`:

1. Если тест проверяет итоговое состояние, fixture path можно оставить явным `.webp`.
2. API должен вернуть ровно тот путь, который хранится в БД.
3. Не ожидать, что API сам конвертирует или меняет путь.

## Важные правила

- Маленькие JPEG/PNG для тестов можно генерировать программно через GD.
- Тесты конвертации запускать внутри Docker-контейнера, где GD собран с WebP.
- `Storage::fake('public')` использовать для файловых проверок.
- Для observer использовать `Queue::fake()` и assert dispatch.

## Команды проверки

```bash
php artisan test --filter=ImageToWebpConverterTest
php artisan test --filter=ConvertModelImageToWebpJobTest
php artisan test --filter=BannerObserverTest
php artisan test --filter=BannerControllerTest
```

Или внутри контейнера:

```bash
docker-compose exec app php artisan test --filter=ImageToWebpConverterTest
docker-compose exec app php artisan test --filter=ConvertModelImageToWebpJobTest
docker-compose exec app php artisan test --filter=BannerObserverTest
docker-compose exec app php artisan test --filter=BannerControllerTest
```

## Готово, когда

- Все новые тесты добавлены.
- Существующие API тесты не сломаны.
- Проверены основные гонки и безопасные no-op сценарии.
