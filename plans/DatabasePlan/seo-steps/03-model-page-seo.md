# Шаг 03 — Модель `PageSeo`

## Цель

Eloquent-модель для таблицы `page_seo`.

## Действия

Файл: `app/Domain/Seo/Models/PageSeo.php`, неймспейс `App\Domain\Seo\Models`.

```php
<?php

namespace App\Domain\Seo\Models;

use Illuminate\Database\Eloquent\Model;

class PageSeo extends Model
{
    protected $table = 'page_seo';

    protected $fillable = [
        'key',
        'path',
        'name',
        'title',
        'description',
        'og_image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
    ];
}
```

## Замечания

- `$table = 'page_seo'` задаётся явно, т.к. имя таблицы — в ед. числе и не выводится из
  имени модели по умолчанию.
- `$fillable` нужен для `updateOrCreate` в сидере (шаг 04).
- Модель располагается в Domain-неймспейсе, автозагрузка — через существующий PSR-4
  `App\ => app/`.

## Definition of Done

- [ ] `app/Domain/Seo/Models/PageSeo.php` создан с правильным неймспейсом.
- [ ] Заданы `$table`, `$fillable`, `$casts`.
