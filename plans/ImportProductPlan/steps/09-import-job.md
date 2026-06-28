# Шаг 09 — `ImportProductsJob`

## Цель

Обернуть запуск `ProductFeedImporter` в очередь: длительный процесс (фид + картинки) не должен
блокировать веб/CLI. Гарантировать отсутствие параллельных прогонов.

## Расположение

`app/Domain/Catalog/Import/Jobs/ImportProductsJob.php`,
неймспейс `App\Domain\Catalog\Import\Jobs`.

## Реализация

```php
final class ImportProductsJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $timeout = 1800;   // фид + картинки — долго
    public int $tries = 3;

    public function backoff(): array { return [60, 300, 900]; }

    public function __construct(
        public string $url,
        public bool $updateExisting = false,
    ) {
        $this->onQueue(config('catalog_import.queue'));
    }

    public function handle(ProductFeedImporter $importer): void
    {
        $result = $importer->import($this->url, $this->updateExisting);

        Log::info('catalog import finished', [
            'created' => $result->created,
            'skipped' => $result->skipped,
            'failed'  => $result->failed,
        ]);
    }

    public function uniqueId(): string { return 'catalog-import-products'; }

    public function uniqueFor(): int { return 1800; }
}
```

## Замечания

- **`ShouldBeUnique`** + `uniqueId()` исключает параллельные прогоны (важно для планировщика).
  Требует рабочего кэша (`CACHE_STORE`) как lock-драйвера.
- **Очередь** — `config('catalog_import.queue')` (рекомендуется отдельная `imports`).
- **`timeout`/`tries`/`backoff`** — фид большой; ретраи с возрастающей паузой.
- (Опц.) запись в `product_imports` (шаг 03) делать в `handle()` вокруг `import()`,
  плюс обработка падения в методе `failed(Throwable $e)`.
- (Опц.) скачивание картинок можно дробить на под-jobs по товару — тогда основной job только
  парсит и создаёт товары, а медиа диспатчит отдельно.

## Definition of Done

- [ ] `ImportProductsJob` реализует `ShouldQueue` и `ShouldBeUnique`.
- [ ] `timeout`/`tries`/`backoff` заданы; job уходит в очередь `imports`.
- [ ] `handle()` вызывает `ProductFeedImporter::import()` и логирует итог.
- [ ] `uniqueId()`/`uniqueFor()` исключают параллельные прогоны.
