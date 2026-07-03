# Шаг 13. Пересобрать Docker и проверить GD WebP

## Цель

Подтвердить, что контейнеры используют PHP GD с поддержкой WebP после изменений в `Dockerfile`.

## Зависимости

- Завершен шаг 04.
- Docker доступен на машине.

## Команды

Пересобрать сервисы, использующие образ приложения:

```bash
docker-compose build app scheduler
docker-compose up -d app scheduler
```

Проверить поддержку WebP внутри контейнера:

```bash
docker-compose exec app php -r "var_export(gd_info()['WebP Support'] ?? null);"
```

Ожидаемый результат:

```text
true
```

## Задачи

1. Пересобрать `app`.
2. Пересобрать `scheduler`, если он использует тот же образ.
3. Перезапустить сервисы.
4. Выполнить проверку `gd_info()` внутри `app`.
5. Если результат `false` или `null`, вернуться к шагу 04 и проверить:
   - установлен ли `libwebp-dev`;
   - есть ли `--with-webp`;
   - действительно ли контейнер пересобран без старого cache.
6. После успешной проверки запустить тесты из шага 12 внутри контейнера.

## Важные правила

- Локальный CLI PHP не является надежной проверкой.
- Конвертация через Intervention зависит от реальной поддержки WebP в GD.
- Без `true` в `gd_info()['WebP Support']` механизм нельзя считать готовым.

## Проверка

```bash
docker-compose exec app php artisan test --filter=ImageToWebpConverterTest
docker-compose exec app php artisan test --filter=ConvertModelImageToWebpJobTest
docker-compose exec app php artisan test --filter=BannerObserverTest
docker-compose exec app php artisan test --filter=BannerControllerTest
```

## Готово, когда

- Контейнер `app` показывает `true` для WebP Support.
- Тесты конвертации проходят внутри Docker.
