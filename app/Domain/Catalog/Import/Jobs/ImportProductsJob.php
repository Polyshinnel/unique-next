<?php

namespace App\Domain\Catalog\Import\Jobs;

use App\Domain\Catalog\Import\Services\ProductFeedImporter;
use App\Domain\Catalog\Import\Support\ProductImportObserver;
use App\Domain\Catalog\Models\ProductImport;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ImportProductsJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $timeout = 1800;

    public int $tries = 3;

    public function __construct(
        public string $url,
        public bool $updateExisting = false,
        public ?int $productImportId = null,
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
        $observer = new ProductImportObserver($this->resolveProductImport());

        try {
            $result = $importer->import($this->url, $this->updateExisting, $observer);

            Log::info('catalog import finished', [
                'product_import_id' => $this->productImportId,
                'created' => $result->created,
                'skipped' => $result->skipped,
                'failed' => $result->failed,
                'message' => $result->message,
            ]);
        } catch (Throwable $exception) {
            $observer->markFailed($exception);

            Log::error('catalog import failed', [
                'product_import_id' => $this->productImportId,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function uniqueId(): string
    {
        return 'catalog-import-products';
    }

    public function uniqueFor(): int
    {
        return 1800;
    }

    public function failed(Throwable $exception): void
    {
        (new ProductImportObserver($this->resolveProductImport()))->markFailed($exception);
    }

    private function resolveProductImport(): ?ProductImport
    {
        if ($this->productImportId === null) {
            return null;
        }

        return ProductImport::query()->find($this->productImportId);
    }
}
