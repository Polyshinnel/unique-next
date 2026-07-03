# Шаг 05. Создать ImageConversionException

## Цель

Добавить доменное исключение для ошибок конвертации, чтобы сервис, job и тесты не зависели от текста системных исключений Intervention/GD/Storage.

## Зависимости

- Нет жестких зависимостей.
- Используется сервисом из шага 06 и job из шага 07.

## Файлы

- Создать: `app/Domain/Media/Exceptions/ImageConversionException.php`.

## Задачи

1. Создать директорию `app/Domain/Media/Exceptions`, если ее нет.
2. Создать класс:

```php
<?php

namespace App\Domain\Media\Exceptions;

use RuntimeException;

final class ImageConversionException extends RuntimeException
{
}
```

3. Использовать это исключение для предсказуемых доменных ошибок:
   - исходный файл отсутствует;
   - Storage не смог сохранить WebP;
   - изображение не удалось прочитать или закодировать.
4. Не прятать все ошибки молча в сервисе: job должна иметь возможность залогировать понятную причину.

## Проверка

```bash
php artisan test --filter=ImageToWebpConverterTest
```

На момент создания исключения тестов может еще не быть. Минимальная проверка - отсутствие autoload/syntax ошибок:

```bash
composer dump-autoload
```

## Готово, когда

- Класс создан в правильном namespace.
- Сервис конвертации сможет импортировать `ImageConversionException`.
