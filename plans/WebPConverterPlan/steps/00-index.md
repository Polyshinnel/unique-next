# WebPConverterPlan - шаги исполнения

Разбивка плана [`webp-converter.md`](../webp-converter.md) на последовательные исполнимые шаги.
Каждый шаг оформлен отдельным `.md` файлом и рассчитан на выполнение по порядку.

## Цель

Добавить общий серверный механизм, который принимает изображения через Filament, сохраняет исходный файл сразу, а затем в фоне конвертирует `jpg`/`jpeg`/`png` в `webp`, обновляет путь в базе и аккуратно удаляет исходник.

## Основные решения

- Конвертация выполняется асинхронно через observer + queue job.
- Для кодирования используется `intervention/image-laravel`.
- Первый подключаемый сценарий - баннеры.
- GIF/BMP не конвертируются автоматически.
- Уже загруженные `webp` сохраняются как есть и не ставятся в очередь.

## Порядок выполнения

| # | Файл | Назначение |
|---|---|---|
| 01 | [`01-install-intervention-image.md`](01-install-intervention-image.md) | Установить `intervention/image-laravel` |
| 02 | [`02-publish-image-config.md`](02-publish-image-config.md) | Опубликовать и настроить `config/image.php` |
| 03 | [`03-images-config-and-env.md`](03-images-config-and-env.md) | Добавить `config/images.php` и env-переменные |
| 04 | [`04-docker-gd-webp.md`](04-docker-gd-webp.md) | Включить WebP в GD внутри Docker-образа |
| 05 | [`05-image-conversion-exception.md`](05-image-conversion-exception.md) | Создать доменное исключение конвертации |
| 06 | [`06-image-to-webp-converter.md`](06-image-to-webp-converter.md) | Создать общий сервис `ImageToWebpConverter` |
| 07 | [`07-convert-model-image-job.md`](07-convert-model-image-job.md) | Создать универсальную queue job для модели/атрибута |
| 08 | [`08-valid-image-upload-extensions.md`](08-valid-image-upload-extensions.md) | Расширить `ValidImageUpload` списком разрешенных расширений |
| 09 | [`09-image-upload-webp-helper.md`](09-image-upload-webp-helper.md) | Добавить helper в `ImageUpload` для WebP-конвертируемых полей |
| 10 | [`10-banner-form-upload.md`](10-banner-form-upload.md) | Подключить helper в форме баннеров |
| 11 | [`11-banner-observer.md`](11-banner-observer.md) | Создать и зарегистрировать `BannerObserver` |
| 12 | [`12-tests.md`](12-tests.md) | Добавить unit/feature/observer/API тесты |
| 13 | [`13-docker-rebuild-and-gd-check.md`](13-docker-rebuild-and-gd-check.md) | Пересобрать контейнеры и проверить поддержку WebP |
| 14 | [`14-horizon-and-manual-check.md`](14-horizon-and-manual-check.md) | Проверить очередь, Filament, API и фронт вручную |
| 15 | [`15-rollout-other-fields.md`](15-rollout-other-fields.md) | После баннеров распространить механизм на остальные поля |

## Общие правила для всех шагов

- Не добавлять синхронную конвертацию в `saveUploadedFileUsing()`.
- Не смешивать первую реализацию с backfill-командой для старых файлов.
- Не удалять файл при обычном soft delete.
- Не перетирать путь в модели, если пользователь уже заменил изображение.
- Хранить код конвертации в `App\Domain\Media`, а привязку к баннерам - в `App\Domain\Banner`.
- Проверять WebP-поддержку GD внутри Docker-контейнера `app`, а не в локальном CLI PHP.

## Общий Definition of Done

- [ ] Пакет `intervention/image-laravel` установлен.
- [ ] `config/image.php` опубликован и настроен на GD.
- [ ] `config/images.php` и env-переменные добавлены.
- [ ] Docker-образ собирает GD с `--with-webp`.
- [ ] Общий сервис конвертирует JPEG/PNG в WebP на storage disk.
- [ ] Универсальная job обновляет модель только при совпадении исходного пути.
- [ ] `ValidImageUpload` поддерживает кастомный список расширений без поломки старых форм.
- [ ] Поле баннера принимает `jpg`, `jpeg`, `png`, `webp`.
- [ ] `BannerObserver` dispatch'ит job после commit.
- [ ] Тесты покрывают сервис, job, observer и API.
- [ ] В Docker подтверждено `gd_info()['WebP Support'] === true`.
- [ ] Ручная проверка через Filament и `/api/banners` пройдена.
