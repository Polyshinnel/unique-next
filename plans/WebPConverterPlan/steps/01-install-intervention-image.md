# Шаг 01. Установить Intervention Image

## Цель

Добавить Laravel-интеграцию Intervention Image, чтобы проект мог читать JPEG/PNG и кодировать результат в WebP без внешнего CLI-бинарника.

## Зависимости

- Рабочий Composer внутри проекта.
- Доступ к Packagist.
- PHP-расширения GD будут окончательно проверены на шагах 04 и 13.

## Файлы

- Изменить: `composer.json`.
- Изменить: `composer.lock`.

## Команды

```bash
composer require intervention/image-laravel
```

Если зависимости в проекте принято ставить внутри контейнера, выполнить команду в `app`:

```bash
docker-compose exec app composer require intervention/image-laravel
```

## Задачи

1. Установить пакет `intervention/image-laravel`.
2. Проверить, что в `composer.json` появился пакет Intervention Laravel integration.
3. Проверить, что `composer.lock` обновлен.
4. Не добавлять собственную обертку над пакетом на этом шаге.
5. Не подключать пакет к Filament напрямую: конвертация должна быть общей для observer/job/import-сценариев.

## Проверка

```bash
composer show intervention/image-laravel
```

Ожидаемо: Composer показывает установленный пакет и версию.

## Готово, когда

- Пакет установлен.
- Composer-файлы обновлены.
- Проект не содержит еще никакой бизнес-логики конвертации.
