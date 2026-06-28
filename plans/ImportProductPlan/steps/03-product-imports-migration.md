# Шаг 03 — (Опц.) Лог-таблица `product_imports`

## Цель

Завести таблицу для отслеживания прогонов импорта планировщиком: статус, счётчики, дата
экспорта фида. Полезна для дебага и для дедупа по `export_date` (не обрабатывать один и тот же
экспорт дважды). Шаг **опциональный** — можно отложить, но рекомендуется.

## Миграция `create_product_imports_table`

Файл: `database/migrations/<ts>_create_product_imports_table.php`

| Поле | Тип | Null | Описание |
|---|---|---|---|
| `id` | `id()` | нет | PK |
| `feed_export_date` | `timestamp` | да | атрибут `export_date` фида |
| `status` | `string(20)` | нет | `running` / `success` / `failed` |
| `created_count` | `unsignedInteger` default 0 | нет | создано товаров |
| `skipped_count` | `unsignedInteger` default 0 | нет | пропущено |
| `failed_count` | `unsignedInteger` default 0 | нет | ошибок |
| `message` | `text` | да | текст ошибки/итог |
| `started_at` | `timestamp` | да | начало прогона |
| `finished_at` | `timestamp` | да | конец прогона |
| `timestamps` | — | да | метки |

```php
$table->id();
$table->timestamp('feed_export_date')->nullable();
$table->string('status', 20);
$table->unsignedInteger('created_count')->default(0);
$table->unsignedInteger('skipped_count')->default(0);
$table->unsignedInteger('failed_count')->default(0);
$table->text('message')->nullable();
$table->timestamp('started_at')->nullable();
$table->timestamp('finished_at')->nullable();
$table->timestamps();
$table->index('feed_export_date');
$table->index('status');
```

## Модель `ProductImport`

Файл: `app/Domain/Catalog/Models/ProductImport.php`, неймспейс `App\Domain\Catalog\Models`.

- `$fillable`: все поля выше.
- `$casts`: `feed_export_date`/`started_at`/`finished_at` → `datetime`,
  счётчики → `integer`.

## Использование

- В начале прогона `ProductFeedImporter` создаёт запись `status=running`, `started_at=now()`.
- По завершении — `update` со счётчиками и `status=success|failed`, `finished_at=now()`.
- Перед обработкой: если `feed_export_date` уже импортировался успешно — можно пропустить прогон
  (согласовать; off по умолчанию).

## Definition of Done

- [ ] Миграция `product_imports` создана и применяется.
- [ ] Модель `ProductImport` с `$fillable`/`$casts`.
- [ ] (Если включён дедуп по экспорту) логика проверки `feed_export_date`.
