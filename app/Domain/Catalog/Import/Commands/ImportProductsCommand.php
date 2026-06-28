<?php

namespace App\Domain\Catalog\Import\Commands;

use App\Domain\Catalog\Import\Jobs\ImportProductsJob;
use App\Domain\Catalog\Import\Services\ProductFeedImporter;
use App\Domain\Catalog\Import\Support\ProductImportObserver;
use Illuminate\Console\Command;
use Throwable;

final class ImportProductsCommand extends Command
{
    protected $signature = 'catalog:import-products
        {--update-existing : Обновлять изменившиеся поля существующих товаров}
        {--url= : Переопределить URL фида}
        {--sync : Выполнить импорт синхронно и вывести прогресс в консоль}';

    protected $description = 'Импорт товаров из XML-фида панели в очередь или синхронно с прогрессом';

    public function handle(ProductFeedImporter $importer): int
    {
        $url = (string) ($this->option('url') ?: config('catalog_import.feed_url'));
        $updateExisting = (bool) $this->option('update-existing');
        $queue = (string) config('catalog_import.queue');

        if ((bool) $this->option('sync')) {
            return $this->runSynchronously($importer, $url, $updateExisting);
        }

        $observer = new ProductImportObserver(
            ProductImportObserver::createQueued($url, $updateExisting, $queue),
        );

        ImportProductsJob::dispatch(
            url: $url,
            updateExisting: $updateExisting,
            productImportId: $observer->id(),
        );

        $this->info('ImportProductsJob поставлен в очередь.');
        $this->line('Import ID: '.$observer->id());
        $this->line('Queue: '.$queue);
        $this->line('URL: '.$url);
        $this->line('Update existing: '.($updateExisting ? 'yes' : 'no'));
        $this->line('Mode: queued');

        return self::SUCCESS;
    }

    private function runSynchronously(ProductFeedImporter $importer, string $url, bool $updateExisting): int
    {
        $productImport = ProductImportObserver::createQueued(
            url: $url,
            updateExisting: $updateExisting,
            queue: 'sync',
        );
        $observer = new ProductImportObserver(
            $productImport,
            fn (string $message) => $this->line($message),
        );

        $this->info('ImportProductsJob выполняется синхронно.');
        $this->line('Import ID: '.$productImport->id);
        $this->line('URL: '.$url);
        $this->line('Update existing: '.($updateExisting ? 'yes' : 'no'));
        $this->line('Mode: sync');

        try {
            $result = $importer->import($url, $updateExisting, $observer);
        } catch (Throwable $exception) {
            $observer->markFailed($exception);
            $this->error('Импорт завершился с ошибкой: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Импорт завершён: created=%d, skipped=%d, failed=%d.',
            $result->created,
            $result->skipped,
            $result->failed,
        ));

        return self::SUCCESS;
    }
}
