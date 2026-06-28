# Шаг 02 — Удалить `city_name` из `regions`

## Цель

Регион хранится на уровне области (например, «Калужская область»); конкретный город не нужен
(раздел 2.6 плана). Колонка `city_name` удаляется из схемы, модели и резолвера.

## Вариант A — БД ещё не в продакшене (предпочтительно)

Убрать колонку прямо в исходной миграции
`database/migrations/2026_06_26_000007_create_regions_table.php`:

- удалить строку вида `$table->string('city_name')->nullable();`

Затем пересоздать схему локально: `php artisan migrate:fresh` (или `migrate:fresh --seed`).

## Вариант B — БД уже мигрирована

Создать новую миграцию `drop_city_name_from_regions_table`:

```php
public function up(): void
{
    Schema::table('regions', function (Blueprint $table) {
        $table->dropColumn('city_name');
    });
}

public function down(): void
{
    Schema::table('regions', function (Blueprint $table) {
        $table->string('city_name')->nullable();
    });
}
```

## Правки модели

Файл `app/Domain/Catalog/Models/Region.php`:

- убрать `city_name` из `$fillable`;
- убрать `city_name` из `$casts` (если присутствует).

## Правки резолвера

В `RegionResolver` (создаётся в шаге 06) **не** обращаться к `city_name`:
маппинг региона — только `external_id` ← `@id`, `name` ← `name`.
Узел `location/regions/region/city_name` из фида игнорируется.

## Definition of Done

- [ ] Колонка `city_name` отсутствует в схеме `regions` (вариант A или B).
- [ ] `city_name` удалён из `$fillable`/`$casts` модели `Region`.
- [ ] Резолвер региона не использует `city_name`.
- [ ] Миграции применяются без ошибок.
