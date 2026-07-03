# Шаг 02. Опубликовать и настроить config/image.php

## Цель

Опубликовать конфигурацию Intervention Image и явно выбрать GD-драйвер с настройками, подходящими для пользовательских загрузок.

## Зависимости

- Завершен шаг 01.
- Пакет `intervention/image-laravel` доступен приложению.

## Файлы

- Создать или изменить: `config/image.php`.

## Команды

```bash
php artisan vendor:publish --provider="Intervention\\Image\\Laravel\\ServiceProvider"
```

Если artisan выполняется внутри контейнера:

```bash
docker-compose exec app php artisan vendor:publish --provider="Intervention\\Image\\Laravel\\ServiceProvider"
```

## Задачи

1. Опубликовать конфиг пакета.
2. Настроить драйвер на GD:

```php
'driver' => Intervention\Image\Drivers\Gd\Driver::class,
```

3. В `options` выставить:

```php
'options' => [
    'autoOrientation' => true,
    'decodeAnimation' => true,
    'strip' => true,
],
```

4. Убедиться, что `strip => true` включен: публичным баннерам не нужны EXIF и прочие метаданные.
5. Не включать Imagick, если в проекте нет отдельного решения перейти на него.

## Проверка

```bash
php artisan config:clear
php artisan tinker
```

В tinker можно проверить, что фасад доступен:

```php
Intervention\Image\Laravel\Facades\Image::class
```

## Готово, когда

- `config/image.php` существует.
- Драйвер - GD.
- Включены `autoOrientation`, `decodeAnimation`, `strip`.
