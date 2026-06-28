<?php

namespace App\Domain\Catalog\Import\Jobs;

use App\Domain\Catalog\Import\Services\ProductFeedImporter;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

final class ImportProductsJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $timeout = 1800;

    public int $tries = 3;

    public function __construct(
        public string $url,
        public bool $updateExisting = false,
    ) {
        $this->onQueue(config('catalog_import.queue'));
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(ProductFeedImporter $importer): void
    {
        $result = $importer->import($this->url, $this->updateExisting);

        Log::info('catalog import finished', [
            'created' => $result->created,
            'skipped' => $result->skipped,
            'failed' => $result->failed,
        ]);
    }

    public function uniqueId(): string
    {
        return 'catalog-import-products';
    }

    public function uniqueFor(): int
    {
        return 1800;
    }
}
