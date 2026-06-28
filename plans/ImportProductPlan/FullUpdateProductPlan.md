# FullUpdateProductPlan — Полное обновление товара из XML-фида

## 1. Цель

Реализовать **полное обновление существующего товара** при импорте из фида
`advertisements.xml`, а именно:

1. **Фото — синхронизация полного набора:**
   - обновить **порядок** (`sort_order`) и флаг главного (`is_main`) у уже существующих фото;
   - **удалить** фото, которых больше нет в фиде (строка в `product_images` + файл на диске);
   - **докачать** новые фото, появившиеся в фиде.
2. **Поля товара — обновить все поля** объявления (скалярные поля `products`, связи,
   текстовые блоки, статус-блоки), а не только цену/статус.

> **Команда ТОЛЬКО для обновления существующих товаров.** Новые товары она **не создаёт**
> (создание — задача `catalog:import-products`). Запускается по расписанию **раз в несколько
> часов**, чтобы дорогие операции (реордер/чистка/докачка фото) не выполнялись постоянно.
> Лёгкий путь `ExistingProductUpdater` (только цена/статус) остаётся как было.

---

## 2. Текущее состояние (что уже работает и где пробелы)

### 2.1 Оркестратор — `ProductFeedImporter::import($url, $updateExisting)`

`@/home/andrey/projects/uniqset2.com/app/Domain/Catalog/Import/Services/ProductFeedImporter.php:90-107`

При `--update-existing` для найденного товара уже вызываются:
- `upsertProduct()` — все скалярные поля `products` (`productAttributes()` — полный набор);
- `syncRelations()` — регионы, теги, менеджер;
- `syncTextBlocks()` — 5 one-to-one текстовых блоков;
- `syncStatusBlocks()` — check / loading / removal;
- цикл `foreach ($advertisement->media as $mediaItem) { $this->images->download(...) }`.

> **Вывод:** обновление **полей** товара уже реализовано корректно (п.2 цели почти закрыт).
> Основная недоработка — **фото**.

### 2.2 Фото — `ImageDownloader::download()`

`@/home/andrey/projects/uniqset2.com/app/Domain/Catalog/Import/Services/ImageDownloader.php:17-87`

Проблемы:

| # | Проблема | Где | Следствие |
|---|---|---|---|
| 1 | `firstOrNew` по (`product_id`,`external_id`) + ранний `return`, если файл уже на диске | строки 21-28 | `sort_order` / `is_main` существующих фото **не обновляются** |
| 2 | Нет удаления фото, отсутствующих в фиде | весь сервис | **осиротевшие** строки + файлы остаются |
| 3 | Скачивание идёт по одному `media_item` без знания полного набора товара | вызов из importer | невозможно вычислить «что удалить» |

### 2.3 Модель и связи

- `ProductImage` fillable содержит `sort_order`, `is_main`, `file_path` и т.д. —
  `@/home/andrey/projects/uniqset2.com/app/Domain/Catalog/Models/ProductImage.php:15-34`.
- `Product::images()` = `hasMany(ProductImage)->orderBy('sort_order')` —
  `@/home/andrey/projects/uniqset2.com/app/Domain/Catalog/Models/Product.php:113-116`.
- `Product::mainImage()` = `hasOne(...)->where('is_main', true)` — строки 118-121.
- DTO `MediaItemData` несёт `externalId, fileName, fileUrl, mimeType, fileSize, sortOrder, isMain` —
  `@/home/andrey/projects/uniqset2.com/app/Domain/Catalog/Import/Data/MediaItemData.php:5-15`.

---

## 3. Целевое решение

Ввести отдельный сервис **`ProductImageSynchronizer`**, который принимает товар и
**весь список** `MediaItemData` объявления и приводит `product_images` к этому списку.
`ImageDownloader` остаётся низкоуровневым «скачать один файл», синхронизатор управляет набором.

### 3.1 Алгоритм синхронизации фото (полный)

```text
sync(Product $product, list<MediaItemData> $media):
  1. $feedIds = external_id всех media из фида.
  2. Удаление осиротевших:
     - выбрать product_images товара, где external_id NOT IN $feedIds;
     - для каждого: удалить файл с диска (если file_path есть и существует), затем delete() строки.
  3. Для каждого MediaItemData (в порядке из фида):
     - $image = ProductImage::firstOrNew(['product_id','external_id']);
     - если файла на диске нет / file_path пуст / external файл новый →
         скачать через ImageDownloader (см. 3.2);
     - ВСЕГДА обновить метаданные порядка: sort_order, is_main,
         file_name, file_url, mime_type, file_size (если поменялись);
     - $image->save().
  4. (опц.) Гарантировать единственный is_main: если в наборе нет ни одного is_main —
     назначить главным фото с минимальным sort_order; если несколько — оставить первый.
```

> Ключевое отличие от текущего кода: шаги **2** (удаление) и обновление `sort_order`/`is_main`
> **даже когда файл уже скачан** (сейчас этого нет из-за раннего `return`).

### 3.2 Рефактор `ImageDownloader`

Вынести скачивание файла в метод, не делающий ранний выход по «файл существует»:

- `download(Product, MediaItemData): ?ProductImage` — оставить для обратной совместимости,
  но внутри использовать общую логику.
- Добавить (или выделить) метод, который:
  - решает, нужно ли качать (нет `file_path`, или файла нет на диске, или сменился `file_url`);
  - качает и кладёт на диск, заполняет `file_*`, `mime_type`, `file_size`;
  - **не** трогает `sort_order`/`is_main` сам — это делает синхронизатор (или передаёт их явно).
- Метод удаления файла: `deleteFile(?string $filePath): void` (через тот же диск
  `config('catalog_import.image_disk')`).

> Вариант проще без двух классов: всю логику набора добавить прямо в `ImageDownloader`
> новым методом `syncProductImages(Product, array $media)`. Выбор — см. п.6.

### 3.3 Интеграция в `ProductFeedImporter`

Заменить цикл `@/home/andrey/projects/uniqset2.com/app/Domain/Catalog/Import/Services/ProductFeedImporter.php:104-106`:

```php
// было:
foreach ($advertisement->media as $mediaItem) {
    $this->images->download($product, $mediaItem);
}

// станет (полная синхронизация набора):
$this->imageSync->sync($product, $advertisement->media);
```

Вызов остаётся **внутри** `DB::transaction` на объявление (строки 90-107), но
**удаление файлов с диска делать после успешного коммита** (диск — не транзакционный),
чтобы откат БД не оставил «дыру». Варианты:
- собрать пути на удаление, удалить файлы в `DB::afterCommit(...)`/после транзакции;
- либо удалять файлы сразу, приняв риск (фид — источник истины, повторный прогон восстановит).
  Рекомендуется **`afterCommit`**.

---

## 4. Запуск: отдельная команда (решение — вариант B)

Полное обновление выносится в **отдельную команду + job + сервис-оркестратор**, чтобы
дорогой фото-синк (реордер + чистка + докачка) не выполнялся на каждом лёгком прогоне
`--update-existing` / `ExistingProductUpdater` (только цена/статус).

### 4.1 Команда

`UpdateRevisionProductsCommand` (черновик уже открыт в IDE) — по образцу
`@/home/andrey/projects/uniqset2.com/app/Domain/Catalog/Import/Commands/ImportProductsCommand.php:11-48`:

```php
protected $signature = 'catalog:update-revision-products
    {--url= : Переопределить URL фида}
    {--sync : Выполнить синхронно с прогрессом в консоль}';
```

- **Только обновление существующих товаров.** Команда **НЕ создаёт** новые товары:
  если по `external_id` (fallback `sku`) товар не найден — **пропустить** (`skipped++`),
  не вставлять. Создание новых товаров — задача отдельного импорта
  (`catalog:import-products`).
- Команда **всегда** делает полное обновление найденного товара (поля + связи +
  текст/статус-блоки + полная фото-синхронизация). Флаг `--update-existing` не нужен.
- Очередь — `config('catalog_import.queue')`; режим `--sync` — синхронный прогон с выводом
  (как в `ImportProductsCommand::runSynchronously`).

### 4.1.1 Расписание

Запускается **раз в несколько часов** (дорогие операции — реордер/чистка/докачка фото),
а не на каждый прогон. В `routes/console.php` (по образцу `ImportProductPlan.md`, 7.3):

```php
Schedule::command('catalog:update-revision-products')
    ->everyFourHours()          // частоту согласовать
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground();
```

`ShouldBeUnique` на job + `withoutOverlapping` исключают наложение долгих прогонов.

### 4.2 Job

`UpdateRevisionProductsJob` (`ShouldQueue`, `ShouldBeUnique`) — по образцу
`@/home/andrey/projects/uniqset2.com/app/Domain/Catalog/Import/Jobs/UpdateExistingProductsJob.php:14-66`:
- `timeout = 1800`, `tries = 3`, `backoff = [60,300,900]`;
- `uniqueId() = 'catalog-update-revision-products'`;
- вызывает сервис-оркестратор (см. 4.3), пишет прогресс через observer (см. 4.4).

### 4.3 Оркестратор

`RevisionProductUpdater` (новый сервис) — для каждого `<advertisement>`:
1. найти товар (`findExistingProduct` по `external_id`, fallback `sku`) —
   по образцу `@/home/andrey/projects/uniqset2.com/app/Domain/Catalog/Import/Services/ExistingProductUpdater.php:128-137`;
2. **если не найден → `skipped++`, continue** (НЕ создавать);
3. если найден → полное обновление: все скалярные поля, связи, текст-блоки,
   статус-блоки **и** полная фото-синхронизация через `ProductImageSynchronizer` (раздел 3).

> **Важно:** напрямую вызывать `ProductFeedImporter::import($url, updateExisting: true)`
> **нельзя** — `upsertProduct()` создаёт новый товар, если не найден
> (`@/home/andrey/projects/uniqset2.com/app/Domain/Catalog/Import/Services/ProductFeedImporter.php:269-277`),
> а по ТЗ создание запрещено. Поэтому переиспользовать нужно **приватную логику**
> полного апдейта (`syncRelations`, `syncTextBlocks`, `syncStatusBlocks`, маппинг
> `productAttributes`), выделив её в общий трейт/сервис, и применять **только** к
> найденному товару. Справочники верхнего уровня (`importReferences`) можно
> обновлять, как в `ProductFeedImporter`, либо ограничиться нужными статусами
> (как в `ExistingProductUpdater::update`, строки 48-50).

### 4.4 Observer / лог

По образцу `ExistingProductUpdateObserver` —
`@/home/andrey/projects/uniqset2.com/app/Domain/Catalog/Import/Support/ExistingProductUpdateObserver.php:10-51`:
писать статус/прогресс в `product_imports` + счётчики фото (см. 6.5).

---

## 5. Крайние случаи и риски

- **Транзакция vs. диск:** удаление файлов — вне транзакции (`afterCommit`), см. 3.3.
- **Сменился `file_url` при том же `external_id`:** считать как обновление — перекачать файл,
  старый файл по прежнему `file_path` удалить/перезаписать.
- **Битые/недоступные фото:** ошибка скачивания одного фото **не должна** ронять товар —
  логировать (как сейчас в `ImageDownloader::logWarning`) и продолжать; строку при этом
  можно не создавать/не удалять существующую (чтобы не терять рабочее фото из-за временного 5xx).
- **`is_main` целостность:** гарантировать ровно один главный (п. 3.1 шаг 4).
- **Удаление файла:** удалять только в пределах `products/{product_id}/...`; не трогать,
  если `file_path` пуст.
- **Идемпотентность:** повторный прогон без изменений в фиде → 0 удалений, 0 скачиваний,
  возможные no-op обновления `sort_order` (через `isDirty()` не сохранять, если не менялось).
- **Производительность:** фото-синк дороже простого апдейта — учитывать при выборе частоты/режима (п.4).

---

## 6. Изменения в коде (чек-лист реализации)

1. **`ImageDownloader`** (`Services/ImageDownloader.php`):
   - выделить «решение о скачивании» + сам download без раннего возврата по метаданным порядка;
   - добавить `deleteFile(?string $filePath): void`;
   - не управлять `sort_order`/`is_main` единолично (передавать из синхронизатора).
2. **Новый `ProductImageSynchronizer`** (`Services/ProductImageSynchronizer.php`) — алгоритм п.3.1
   (или метод `syncProductImages()` в `ImageDownloader`, если решено без нового класса).
3. **`ProductFeedImporter`**:
   - внедрить синхронизатор в конструктор (строки 33-42);
   - заменить цикл media на `sync()` (строки 104-106);
   - удаление файлов через `DB::afterCommit`.
4. **Новая команда + job + оркестратор (вариант B):**
   - `Commands/UpdateRevisionProductsCommand.php` (`catalog:update-revision-products`, см. 4.1);
   - `Jobs/UpdateRevisionProductsJob.php` (`ShouldQueue`+`ShouldBeUnique`, см. 4.2);
   - `Services/RevisionProductUpdater.php` (оркестратор, см. 4.3) — переиспользовать логику
     полного апдейта из `ProductFeedImporter` (выделить общий код, не дублировать);
   - `Support/RevisionProductUpdateObserver.php` (или переиспользовать существующий, см. 4.4).
5. **Логирование/прогресс:** добавить счётчики `images_downloaded` / `images_deleted` /
   `images_reordered` в прогресс-события (по образцу `reportProgress`).

---

## 7. Тестирование

- **Unit `ProductImageSynchronizer`:**
  - реордер: меняется `sort_order`/`is_main` у существующих без повторного скачивания;
  - удаление: фото, которого нет в фиде, удаляется из БД и с диска (`Storage::fake`);
  - докачка: новое фото скачивается (`Http::fake`);
  - целостность `is_main` (ровно один главный);
  - идемпотентность: повторный прогон без изменений — без скачиваний/удалений.
- **Feature (полный апдейт товара):**
  - фикстура-XML: товар существует → меняется набор фото (часть удалена, часть добавлена,
    порядок изменён) + меняются поля → проверить итоговое состояние `products`/связей/`product_images`;
  - `Http::fake()` для фида и изображений, `Storage::fake(image_disk)`.
- **Регресс:** ошибка скачивания одного фото не ломает обновление товара и не удаляет рабочее фото.

---

## 8. Итоговое поведение (Definition of Done)

- [ ] Порядок фото (`sort_order`) и главное фото (`is_main`) обновляются у существующих изображений.
- [ ] Фото, исчезнувшие из фида, удаляются из `product_images` и с диска.
- [ ] Новые фото из фида докачиваются.
- [ ] Все поля товара (скалярные, связи, текст-блоки, статус-блоки) обновляются.
- [ ] Новые товары НЕ создаются: отсутствующий в БД `<advertisement>` пропускается.
- [ ] Команда запускается по расписанию раз в несколько часов (`withoutOverlapping` + `ShouldBeUnique`).
- [ ] Удаление файлов выполняется после коммита транзакции (`afterCommit`).
- [ ] Ошибка одного фото не роняет обновление товара.
- [ ] Покрыто unit- и feature-тестами (`Http::fake` / `Storage::fake`).
