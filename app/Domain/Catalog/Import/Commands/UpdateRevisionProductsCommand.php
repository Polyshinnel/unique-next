<?php

namespace App\Domain\Catalog\Import\Commands;

use App\Domain\Catalog\Import\Jobs\UpdateRevisionProductsJob;
use App\Domain\Catalog\Import\Services\RevisionProductUpdater;
use App\Domain\Catalog\Import\Support\ExistingProductUpdateObserver;
use Illuminate\Console\Command;
use Throwable;

final class UpdateRevisionProductsCommand extends Command
{
    protected $signature = 'catalog:update-revision-products
        {--url= : Переопределить URL фида}
        {--sync : Выполнить обновление синхронно и вывести прогресс в консоль}';

    protected $description = 'Полное обновление существующих товаров из XML-фида (поля + фото: порядок, чистка, докачка). Новые товары не создаёт.';

    public function handle(RevisionProductUpdater $updater): int
    {
        $url = (string) ($this->option('url') ?: config('catalog_import.feed_url'));
        $queue = (string) config('catalog_import.queue');

        if ((bool) $this->option('sync')) {
            return $this->runSynchronously($updater, $url);
        }

        $observer = new ExistingProductUpdateObserver(
            ExistingProductUpdateObserver::createQueued($url, $queue),
        );

        UpdateRevisionProductsJob::dispatch(
            url: $url,
            productImportId: $observer->id(),
        );

        $this->info('UpdateRevisionProductsJob поставлен в очередь.');
        $this->line('Import ID: '.$observer->id());
        $this->line('Queue: '.$queue);
        $this->line('URL: '.$url);
        $this->line('Mode: queued');

        return self::SUCCESS;
    }

    private function runSynchronously(RevisionProductUpdater $updater, string $url): int
    {
        $productImport = ExistingProductUpdateObserver::createQueued(
            url: $url,
            queue: 'sync',
        );
        $observer = new ExistingProductUpdateObserver(
            $productImport,
            fn (string $message) => $this->line($message),
        );

        $this->info('UpdateRevisionProductsJob выполняется синхронно.');
        $this->line('Import ID: '.$productImport->id);
        $this->line('URL: '.$url);
        $this->line('Mode: sync');

        try {
            $result = $updater->update($url, $observer);
        } catch (Throwable $exception) {
            $observer->markFailed($exception);
            $this->error('Обновление завершилось с ошибкой: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Обновление завершено: updated=%d, skipped=%d, failed=%d.',
            $result->updated,
            $result->skipped,
            $result->failed,
        ));

        return self::SUCCESS;
    }
}
