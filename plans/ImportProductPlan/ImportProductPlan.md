# ImportProductPlan — Импорт товаров из XML-фида (Laravel 13 + MySQL 8.4, DDD)

## 1. Обзор

Документ описывает план реализации **импорта товаров (объявлений)** из внешнего XML-фида
панели `panel.uniqset.com` в базу данных сайта `uniqset2.com`.

- **Источник:** `https://panel.uniqset.com/storage/exports/advertisements.xml`
- **Единица импорта:** элемент `<advertisement>` → строка в таблице `products`.
- **Запуск:** artisan-команда, которая ставит job в очередь; job запускается по расписанию.
- **Идемпотентность:** повторный прогон не создаёт дубликатов и обновляет изменившиеся справочники.

> **Ключевой вывод по схеме БД:** таблицы из `DatabaseTextPlan.md` уже спроектированы под импорт —
> почти все справочники имеют уникальный столбец `external_id`. Структурных изменений требуется минимум
> (см. раздел 8). Основная работа — код импортёра.

---

## 2. Соответствие полей XML ↔ База данных (mapping)

### 2.1 Справочники верхнего уровня (блоки в корне фида)

| XML-узел | Таблица | Поля XML → колонки | Ключ синхронизации |
|---|---|---|---|
| `categories/category` | `categories` | `@id`→`external_id`, `name`→`name`, `parent_id`→`parent_id` (через external_id родителя), `slug` — генерируется | `external_id` |
| `advertisement_statuses/status` | `product_statuses` | `@id`→`external_id`, `name`→`name`, `color`→`color` | `external_id` |
| `check_statuses/status` | `check_statuses` | `@id`→`external_id`, `name`→`name`, `color`→`color` | `external_id` |
| `install_statuses/status` | `shipment_statuses` (погрузка) | `@id`→`external_id`, `name`→`name` | `external_id` |
| `install_statuses/status` | `dismantle_statuses` (демонтаж) | `@id`→`external_id`, `name`→`name` | `external_id` |

> `install_statuses` — единый список в фиде, но в проекте погрузка (`shipment_statuses`) и демонтаж
> (`dismantle_statuses`) — разные таблицы. Обе наполняются из одного блока; `external_id` не конфликтует,
> т.к. таблицы разные.

### 2.2 Объявление `<advertisement>` → `products`

| XML | Колонка `products` | Примечание |
|---|---|---|
| `@id` | `external_id` | **главный ключ дедупликации** |
| `title` | `name` | |
| `sku` | `sku` | вторичный уникальный ключ |
| `category/@id` | `category_id` | резолв по `external_id` категории |
| `status/@id` | `product_status_id` | резолв по `external_id` |
| `product_state/@id` + текст | `equipment_state_id` | upsert по `external_id` (см. 2.4) |
| `product_available/@id` + текст | `equipment_availability_id` | upsert по `external_id` (см. 2.4) |
| `price/adv_price` | `price` | |
| `price/show_price` | `show_price` | `1/0`→bool |
| `price/adv_price_comment` | `price_comment` | CDATA |
| `location/product_address` | `product_address` | |
| `dates/published_at` | `published_at` | |
| `manager/product_owner` | `manager_id` | + запись в pivot `product_manager` (см. 2.5) |
| — | `description` | **null** (в фиде нет прямого аналога) |
| — | `og_image` | **null** (заполняется отдельно/из главного изображения позже) |

### 2.3 Текстовые блоки объявления → one-to-one таблицы (longText `content`)

| XML (CDATA) | Таблица |
|---|---|
| `main_characteristics` | `product_main_characteristics` |
| `complectation` | `product_complectations` |
| `technical_characteristics` | `product_technical_characteristics` |
| `main_info` | `product_main_infos` |
| `additional_info` | `product_additional_infos` |

### 2.4 Статусы, встроенные в объявление

| XML | Таблица | Как резолвить |
|---|---|---|
| `check/status` (текст) + `check/comment` | `check_statuses` + `product_checks` | в объявлении **только name** → match по `name`; id берётся из блока `check_statuses` (раздел 2.1). Если по имени не найдено — создать `check_statuses` без `external_id`. Связь и `comment` → `product_checks` |
| `loading/status` (текст) + `loading/comment` | `shipment_statuses` + `product_loadings` | match по `name` среди `install_statuses`; иначе создать. Связь+comment → `product_loadings` |
| `removal/status` (текст) + `removal/comment` | `dismantle_statuses` + `product_dismantlings` | аналогично, → `product_dismantlings` |
| `product_state/@id` + текст | `equipment_states` | upsert по `external_id` (id и name есть прямо в объявлении) |
| `product_available/@id` + текст | `equipment_availabilities` | upsert по `external_id` |

> **Важно:** `check`, `loading`, `removal` внутри объявления НЕ содержат `@id` — только текст имени.
> Поэтому связь устанавливается по `name`. Блоки верхнего уровня (`check_statuses`, `install_statuses`)
> импортируются ПЕРВЫМИ и проставляют `external_id`, что делает поиск по имени надёжным.

### 2.5 Менеджер (`manager`)

Интересует **только `product_owner`** (по ТЗ, п. 6).

| XML (`manager/product_owner`) | Таблица `managers` |
|---|---|
| `@id` | `external_id` |
| `name` | `name` |
| `email` | `email` |
| `phone` | `phone` |
| `role` | `role` |
| `has_telegram` | — (нет колонки; см. примечание) |
| `has_whatsapp` | — (нет колонки) |

- upsert `managers` по `external_id`; изменилось имя/контакты — обновляем.
- `products.manager_id` = id владельца.
- Дополнительно строка в pivot `product_manager` с `role` = `product_owner` (или роль из фида).
- `creator` и `regional_representative` — **игнорируются** (по ТЗ).

> Колонки `has_telegram`/`has_whatsapp` в `managers` нет. По умолчанию не импортируем.
> Если потребуется — добавить boolean-колонки (опционально, раздел 8).

### 2.6 Локация (`location`)

Интересует **только название региона** `regions/region/name` (по ТЗ, п. 7). `warehouse`,
`company_addresses` и **город** (`city_name`) — игнорируются: достаточно региона уровня области
(условно «Калужская область»), конкретный город не важен.

| XML (`location/regions/region`) | Таблица `regions` |
|---|---|
| `@id` | `external_id` |
| `name` | `name` |
| `city_name` | **не импортируется** (колонка удаляется, см. 8.1) |

- upsert `regions` по `external_id`; связь — pivot `product_region` (many-to-many).
- `location/product_address` (текст) → `products.product_address` (см. 2.2) — остаётся как было
  (это адрес товара в карточке, не связан с регионом).

### 2.7 Медиа (`media/media_item`) → `product_images`

| XML | Колонка | Примечание |
|---|---|---|
| `@id` | `external_id` | дедуп по `external_id` в рамках товара |
| `file_name` | `file_name` | |
| `file_url` | `file_url` | оригинальный URL источника |
| `mime_type` | `mime_type` | |
| `file_size` | `file_size` | |
| `sort_order` | `sort_order` | |
| `is_main_image` | `is_main` | `1/0`→bool |
| (скачанный файл) | `file_path` | **локальный** путь после скачивания (диск `public`) |

- Файл скачивается по `file_url` и сохраняется на диск `public` (раздел 6).
- `@type="image"` — другие типы (video и т.п.) можно пропускать или обрабатывать позже.

### 2.8 Теги (`tags/tag`) → `tags` + `product_tag`

| XML | Таблица `tags` |
|---|---|
| `tag` (текст) | `name` (unique) |

- `tags` **без** `external_id` → синхронизация по `name` (`firstOrCreate`).
- Связь — pivot `product_tag`.

---

## 3. Алгоритм импорта (по шагам)

Порядок важен: сначала справочники верхнего уровня, потом объявления.

```text
1. Скачать XML-фид (HTTP GET, потоково во временный файл).
2. Импортировать справочники верхнего уровня (идемпотентно, updateOrCreate по external_id):
   2.1 categories      — два прохода: сначала все, затем проставить parent_id.
   2.2 advertisement_statuses → product_statuses
   2.3 check_statuses
   2.4 install_statuses → shipment_statuses И dismantle_statuses
3. Для каждого <advertisement>:
   3.1 Дедуп: ищем products по external_id (fallback sku).
       — если найден → ПРОПУСКАЕМ (по ТЗ п.4). [см. примечание про update]
       — если нет → создаём.
   3.2 Резолв/создание связанных сущностей:
       - category (по external_id; create/rename при расхождении имени)
       - product_status (по external_id; rename при расхождении)
       - equipment_state / equipment_availability (upsert по external_id)
       - manager(product_owner) (upsert по external_id)
       - regions (upsert по external_id)
       - check/loading/removal статусы (match по name, create если нет)
       - tags (firstOrCreate по name)
   3.3 Создать products + one-to-one текстовые блоки.
   3.4 Привязать pivot: product_region, product_manager, product_tag.
   3.5 Создать product_checks / product_loadings / product_dismantlings (+comment).
   3.6 Скачать и сохранить media → product_images (см. раздел 6).
4. Залогировать итог (создано/пропущено/ошибки).
```

> **Категории — почему два прохода:** `parent_id` ссылается на другую категорию по `external_id`.
> Сначала создаём все категории, затем во втором проходе проставляем `parent_id`, чтобы порядок в
> фиде не имел значения.

> **Примечание по дедупликации (ТЗ п.4):** буквальная трактовка — «если есть, пропускаем».
> Рекомендуется компромисс: создавать новые, а для существующих опционально обновлять изменившиеся
> поля (цена, статус, published_at). Поведение вынести в опцию команды `--update-existing`
> (по умолчанию off = строго пропускать).

---

## 4. Логика синхронизации справочников

Универсальное правило (ТЗ п.2, 3, 8–12): **upsert по `external_id`**, при расхождении имени — переименовать.

```php
// Псевдокод единого резолвера справочника по external_id
$row = Model::firstOrNew(['external_id' => $extId]);
if (! $row->exists) {
    $row->fill([...])->save();           // создать
} elseif ($row->name !== $name /* или др. поля */) {
    $row->update(['name' => $name, ...]); // переименовать/обновить
}
return $row;
```

- **categories** — create, если нет; rename, если имя в фиде отличается; проставить `parent_id`.
- **product_statuses / check_statuses / shipment_statuses / dismantle_statuses /
  equipment_states / equipment_availabilities** — то же по `external_id`.
- **check/loading/removal внутри объявления** — нет `external_id`, поэтому match по `name`,
  create если отсутствует.
- **tags** — match/create по `name`.

---

## 5. DDD-структура кода

```text
app/
└── Domain/
    └── Catalog/
        └── Import/
            ├── Commands/
            │   └── ImportProductsCommand.php      # artisan: catalog:import-products
            ├── Jobs/
            │   └── ImportProductsJob.php           # ставится в очередь
            ├── Services/
            │   ├── ProductFeedImporter.php         # оркестратор всего процесса
            │   ├── FeedDownloader.php               # скачивание XML во временный файл
            │   ├── FeedParser.php                   # потоковый разбор (XMLReader)
            │   └── ImageDownloader.php              # скачивание media → public disk
            ├── Resolvers/                            # идемпотентные upsert'ы справочников
            │   ├── CategoryResolver.php
            │   ├── StatusResolvers.php               # product/check/loading/removal/state/avail
            │   ├── ManagerResolver.php
            │   ├── RegionResolver.php
            │   └── TagResolver.php
            └── Data/                                 # DTO (spatie/laravel-data или простые классы)
                ├── AdvertisementData.php
                ├── CategoryData.php
                ├── MediaItemData.php
                └── ...
```

PSR-4 (`App\ => app/`) уже настроен — правок `composer.json` не нужно.

---

## 6. Скачивание изображений

- Диск: **`public`** (`config/filesystems.php` → `storage/app/public`, ссылка `public/storage`).
- Путь хранения: `products/{product_external_id}/{media_external_id}_{file_name}`
  (или хеш имени, чтобы избежать коллизий и кириллицы в путях).
- Скачивание: `Http::timeout(...)->get($fileUrl)` (или поток в файл при больших размерах),
  проверка `2xx` и `content-type`.
- Сохранение: `Storage::disk('public')->put($path, $contents)`.
- `product_images.file_path` = относительный путь на диске `public`;
  `file_url` = оригинальный URL источника.
- Идемпотентность: дедуп `product_images` по (`product_id`, `external_id`); повторно не качать,
  если файл уже есть.
- Ошибки скачивания отдельного файла **не должны** ронять импорт всего товара — логировать и продолжать.
- Перед запуском убедиться, что выполнен `php artisan storage:link`.

---

## 7. Запуск: команда, очередь, расписание

### 7.1 Artisan-команда

```php
// catalog:import-products [--update-existing] [--url=...]
final class ImportProductsCommand extends Command
{
    protected $signature = 'catalog:import-products {--update-existing} {--url=}';

    public function handle(): int
    {
        ImportProductsJob::dispatch(
            url: $this->option('url') ?: config('catalog_import.feed_url'),
            updateExisting: (bool) $this->option('update-existing'),
        );
        return self::SUCCESS;
    }
}
```

### 7.2 Job (очередь)

```php
final class ImportProductsJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 1800;   // фид + картинки — долго
    public int $tries = 3;
    public function backoff(): array { return [60, 300, 900]; }

    public function __construct(
        public string $url,
        public bool $updateExisting = false,
    ) {}

    public function handle(ProductFeedImporter $importer): void
    {
        $importer->import($this->url, $this->updateExisting);
    }

    public function uniqueId(): string { return 'catalog-import-products'; }
}
```

- Реализовать `ShouldBeUnique`, чтобы исключить параллельные прогоны.
- Очередь: `QUEUE_CONNECTION=database` (по умолчанию) или Redis через **Laravel Horizon**
  (уже в зависимостях). Рекомендуется отдельная очередь `imports`.

### 7.3 Расписание (`routes/console.php`)

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('catalog:import-products')
    ->hourly()                 // частоту согласовать (фид обновляется ~раз в сутки)
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground();
```

- В проде планировщик гонится воркером/cron (`php artisan schedule:work` или системный cron
  `* * * * * php artisan schedule:run`). Проверить `docker/supervisor/supervisord.conf`.

### 7.4 Конфиг

Создать `config/catalog_import.php`:

```php
return [
    'feed_url'      => env('CATALOG_IMPORT_FEED_URL', 'https://panel.uniqset.com/storage/exports/advertisements.xml'),
    'image_disk'    => env('CATALOG_IMPORT_IMAGE_DISK', 'public'),
    'http_timeout'  => (int) env('CATALOG_IMPORT_HTTP_TIMEOUT', 60),
];
```

---

## 8. Изменения в схеме БД

Текущая схема (`DatabaseTextPlan.md`) **уже готова** к импорту: `external_id` присутствует в
`categories`, `product_statuses`, `check_statuses`, `shipment_statuses`, `dismantle_statuses`,
`equipment_states`, `equipment_availabilities`, `regions`, `managers`, `products`, `product_images`.

### 8.1 Обязательное

- **Удалить `city_name` из `regions`** — город не нужен, регион хранится на уровне области (п. 2.6).
  - Если схема ещё не в продакшене — проще убрать колонку прямо в исходной миграции
    `2026_06_26_000007_create_regions_table.php` (убрать строку `$table->string('city_name')...`)
    и из `$fillable` модели `App\Domain\Catalog\Models\Region`.
  - Если БД уже мигрирована — новая миграция `drop_city_name_from_regions_table`:

    ```php
    Schema::table('regions', function (Blueprint $table) {
        $table->dropColumn('city_name');
    });
    // down(): $table->string('city_name')->nullable();
    ```
  - Также убрать `city_name` из модели `Region` (`$fillable`) и из `RegionResolver`.

### 8.2 Рекомендуемое (новая миграция `create_product_imports_table`)

Лог-таблица для отслеживания прогонов планировщика (полезно для дебага и для дедупликации
по `export_date` фида — чтобы не обрабатывать один и тот же экспорт дважды):

| Поле | Тип | Описание |
|---|---|---|
| `id` | bigint PK | |
| `feed_export_date` | timestamp null | значение атрибута `export_date` фида |
| `status` | varchar(20) | `running` / `success` / `failed` |
| `created_count` | int | создано товаров |
| `skipped_count` | int | пропущено |
| `failed_count` | int | ошибки |
| `message` | text null | текст ошибки/итог |
| `started_at` / `finished_at` | timestamp null | |
| `timestamps` | | |

### 8.3 Опциональное (по необходимости)

- `managers`: `has_telegram` / `has_whatsapp` (boolean) — если понадобятся флаги мессенджеров.
- `products`: рассмотреть индекс на `product_status_id`, `category_id` для выборок каталога
  (если ещё не созданы FK-индексами).

---

## 9. Схема взаимодействия таблиц (что и где создаётся)

```text
                         advertisements.xml
                                 │
        ┌────────────────────────┼─────────────────────────────┐
        │ (справочники верхнего уровня — импортируются первыми)  │
        ▼                        ▼                               ▼
   categories            product_statuses              check_statuses
 (external_id, slug)   (advertisement_statuses)      (check_statuses block)
        │                                                  
        │                  install_statuses ──┬──► shipment_statuses (loading)
        │                                     └──► dismantle_statuses (removal)
        │
        │                   <advertisement> (единица импорта)
        ▼                            │
   ┌─────────────────────────────────┼─────────────────────────────────────┐
   │                              products                                   │
   │  external_id(@id), name(title), sku, price, show_price, price_comment,  │
   │  product_address, published_at,                                         │
   │  FK: category_id, product_status_id, equipment_state_id,                │
   │      equipment_availability_id, manager_id                              │
   └─────────────────────────────────┬─────────────────────────────────────┘
        │                │                 │                │            │
   one-to-one       one-to-one        pivots          one-to-one     hasMany
        ▼                ▼                 ▼                ▼            ▼
 product_main_*   product_checks   product_region   (status FKs    product_images
 product_complect product_loadings product_manager   на products)   (download media)
 product_technical product_dismant product_tag
 product_main_infos
 product_additional_infos

   Upsert по external_id при обработке объявления:
     equipment_states  ◄── product_state(@id, текст)
     equipment_availabilities ◄── product_available(@id, текст)
     managers          ◄── manager/product_owner(@id, ...)   → products.manager_id + product_manager
     regions           ◄── location/regions/region(@id,...)  → product_region
     tags              ◄── tags/tag (по name)                → product_tag
```

### Где что создаётся при импорте

| Сущность фида | Куда пишется | Создаётся при |
|---|---|---|
| Категория | `categories` | отсутствует `external_id` (rename при смене имени) |
| Статус объявления | `product_statuses` | отсутствует `external_id` (rename) |
| Статус проверки | `check_statuses` | блок + по `name` из объявления |
| Статус погрузки | `shipment_statuses` | `install_statuses` / по `name` |
| Статус демонтажа | `dismantle_statuses` | `install_statuses` / по `name` |
| Состояние | `equipment_states` | `product_state@id` в объявлении |
| Доступность | `equipment_availabilities` | `product_available@id` |
| Менеджер (owner) | `managers` (+`product_manager`) | `product_owner@id` |
| Регион | `regions` (+`product_region`) | `region@id` |
| Теги | `tags` (+`product_tag`) | по `name` |
| Объявление | `products` (+ текстовые one-to-one, checks/loadings/dismantlings) | нет `external_id`/`sku` |
| Картинки | `product_images` (+ файл на диск `public`) | при создании товара |

---

## 10. Технические детали и крайние случаи

- **Парсинг:** использовать **`XMLReader`** (потоковый), а не `simplexml_load_string`, т.к. фид со
  всеми объявлениями и медиа может быть большим (риск превышения памяти).
- **CDATA:** текстовые блоки (`*_characteristics`, `*_info`, комментарии) содержат HTML в CDATA —
  сохранять как есть в `longText`. При необходимости — санитайз HTML на выводе.
- **Транзакции:** обрабатывать каждое объявление в отдельной транзакции (`DB::transaction`),
  чтобы сбой одного товара не откатывал весь импорт.
- **Slug категорий:** в фиде нет `slug`, а в БД он `unique` и обязателен. Генерировать
  транслитерацией кириллицы (`Str::slug` недостаточно для кириллицы — нужен транслит-хелпер),
  при коллизии — суффикс `-2`, `-3`. Slug фиксировать при создании, при rename имени slug **не менять**
  (чтобы не ломать URL) — согласовать.
- **Числа/булевы:** `show_price`, `is_main_image` `1/0` → bool; `adv_price` → decimal(10,2).
- **Даты:** парсить `published_at` из формата `Y-m-d H:i:s`.
- **Идемпотентность медиа:** не перекачивать существующие файлы (dedup по `external_id`).
- **Память/время:** job c `timeout`/`tries`/`backoff`; картинки можно вынести в отдельные
  под-jobs (по товару), чтобы дробить нагрузку.
- **Логирование:** счётчики created/skipped/failed → лог и (опц.) `product_imports`.

---

## 11. Порядок реализации

1. `config/catalog_import.php` + переменные `.env.example`.
2. Удалить `city_name` из `regions` (миграция/модель, раздел 8.1).
3. (Опц.) Миграция `product_imports` (раздел 8.2).
4. DTO в `Domain/Catalog/Import/Data` (раздел 5).
5. `FeedDownloader` + `FeedParser` (XMLReader, потоковый).
6. Резолверы справочников (`CategoryResolver`, статусы, `ManagerResolver`, `RegionResolver`, `TagResolver`) — идемпотентные upsert по `external_id`/`name`.
7. `ImageDownloader` (диск `public`, dedup).
8. `ProductFeedImporter` — оркестратор (раздел 3), транзакция на объявление.
9. `ImportProductsJob` (`ShouldQueue`, `ShouldBeUnique`).
10. `ImportProductsCommand` (`catalog:import-products`).
11. Планировщик в `routes/console.php` (`Schedule::command(...)`).
12. Тесты (раздел 12).

---

## 12. Тестирование

- **Unit:** резолверы (create / rename / no-op по `external_id`); генерация slug; маппинг DTO.
- **Feature:** прогон импорта на фикстуре-XML (из примера ТЗ):
  - создаются категории с `parent_id`;
  - создаётся товар со всеми связями и текстовыми блоками;
  - повторный прогон не создаёт дубликатов (дедуп по `external_id`);
  - переименование категории/статуса в фиде → обновление в БД;
  - `Http::fake()` для скачивания фида и изображений;
  - `Storage::fake('public')` для проверки сохранённых файлов.
- **Очередь:** `Queue::fake()` — команда диспатчит `ImportProductsJob`; job уникален.

---

## 13. Чеклист

- [ ] `config/catalog_import.php` + `.env.example` (URL фида, диск, таймаут).
- [ ] `city_name` удалён из `regions` (миграция + модель + резолвер).
- [ ] (Опц.) миграция `product_imports`.
- [ ] DTO для advertisement/category/media и др.
- [ ] `FeedDownloader` + `FeedParser` (XMLReader).
- [ ] Резолверы справочников (idempotent upsert по `external_id`, rename по имени).
- [ ] `ImageDownloader` (public disk, dedup, отказоустойчивость).
- [ ] `ProductFeedImporter` (порядок из раздела 3, транзакция на объявление).
- [ ] `ImportProductsJob` (`ShouldQueue` + `ShouldBeUnique`, timeout/tries/backoff).
- [ ] `ImportProductsCommand` (`catalog:import-products`, опции).
- [ ] Расписание в `routes/console.php` (`withoutOverlapping`, `onOneServer`).
- [ ] `php artisan storage:link` выполнен.
- [ ] Тесты Unit + Feature (с `Http::fake` / `Storage::fake`).
