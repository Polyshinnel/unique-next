<?php

namespace App\Domain\Catalog\Import\Services;

use App\Domain\Catalog\Import\Data\AdvertisementData;
use App\Domain\Catalog\Import\Resolvers\StatusResolvers;
use App\Domain\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

final class ExistingProductUpdater
{
    private const PROGRESS_LOG_INTERVAL = 100;

    public function __construct(
        private FeedDownloader $downloader,
        private FeedParser $parser,
        private StatusResolvers $statuses,
    ) {}

    /**
     * @param null|callable(string, array<string, mixed>): void $progress
     */
    public function update(string $url, ?callable $progress = null): ExistingProductUpdateResult
    {
        Log::info('Existing product feed update started.', ['url' => $url]);
        $this->reportProgress($progress, 'started', ['url' => $url]);

        $path = $this->downloader->download($url);
        $exportDate = $this->parser->exportDate($path);
        $updated = 0;
        $skipped = 0;
        $failed = 0;
        $processed = 0;

        Log::info('Existing product feed downloaded.', [
            'url' => $url,
            'export_date' => $exportDate,
        ]);
        $this->reportProgress($progress, 'feed_downloaded', [
            'url' => $url,
            'export_date' => $exportDate,
        ]);

        try {
            foreach ($this->parser->advertisementStatuses($path) as $status) {
                $this->statuses->productStatus($status);
            }

            foreach ($this->parser->advertisements($path) as $advertisement) {
                $processed++;

                try {
                    $product = $this->findExistingProduct($advertisement);

                    if ($product === null) {
                        $skipped++;
                        $this->maybeLogProgress($progress, $processed, $updated, $skipped, $failed, $advertisement);

                        continue;
                    }

                    if (! $this->updateProduct($product, $advertisement)) {
                        $skipped++;
                        $this->maybeLogProgress($progress, $processed, $updated, $skipped, $failed, $advertisement);

                        continue;
                    }

                    $updated++;
                    $this->maybeLogProgress($progress, $processed, $updated, $skipped, $failed, $advertisement);
                } catch (Throwable $exception) {
                    $failed++;

                    Log::error('Existing product update failed for advertisement.', [
                        'advertisement_external_id' => $advertisement->externalId,
                        'sku' => $advertisement->sku,
                        'error' => $exception->getMessage(),
                    ]);
                    $this->reportProgress($progress, 'failed_item', [
                        'processed' => $processed,
                        'updated' => $updated,
                        'skipped' => $skipped,
                        'failed' => $failed,
                        'advertisement_external_id' => $advertisement->externalId,
                        'sku' => $advertisement->sku,
                        'error' => $exception->getMessage(),
                    ]);
                }
            }
        } finally {
            @unlink($path);
        }

        Log::info('Existing product feed update finished.', [
            'url' => $url,
            'export_date' => $exportDate,
            'processed' => $processed,
            'updated' => $updated,
            'skipped' => $skipped,
            'failed' => $failed,
        ]);
        $this->reportProgress($progress, 'finished', [
            'url' => $url,
            'export_date' => $exportDate,
            'processed' => $processed,
            'updated' => $updated,
            'skipped' => $skipped,
            'failed' => $failed,
        ]);

        return new ExistingProductUpdateResult(
            updated: $updated,
            skipped: $skipped,
            failed: $failed,
            message: sprintf(
                'Processed %d advertisements: updated=%d, skipped=%d, failed=%d.',
                $processed,
                $updated,
                $skipped,
                $failed,
            ),
        );
    }

    private function findExistingProduct(AdvertisementData $advertisement): ?Product
    {
        return Product::query()
            ->where('external_id', $advertisement->externalId)
            ->when(
                $advertisement->sku !== null,
                fn (Builder $query): Builder => $query->orWhere('sku', $advertisement->sku),
            )
            ->first();
    }

    private function updateProduct(Product $product, AdvertisementData $advertisement): bool
    {
        $product->fill([
            'external_id' => $product->external_id ?? $advertisement->externalId,
            'product_status_id' => $advertisement->statusExternalId !== null
                ? $this->statuses->productStatusByExternalId($advertisement->statusExternalId)?->getKey()
                : null,
            'price' => $this->normalizePrice($advertisement->price),
            'show_price' => $advertisement->showPrice,
            'price_comment' => $advertisement->priceComment,
        ]);

        if (! $product->isDirty()) {
            return false;
        }

        $product->save();

        return true;
    }

    private function maybeLogProgress(
        ?callable $progress,
        int $processed,
        int $updated,
        int $skipped,
        int $failed,
        AdvertisementData $advertisement,
    ): void {
        if ($processed !== 1 && $processed % self::PROGRESS_LOG_INTERVAL !== 0) {
            return;
        }

        $context = [
            'processed' => $processed,
            'updated' => $updated,
            'skipped' => $skipped,
            'failed' => $failed,
            'advertisement_external_id' => $advertisement->externalId,
            'sku' => $advertisement->sku,
        ];

        Log::info('Existing product feed update progress.', $context);
        $this->reportProgress($progress, 'progress', $context);
    }

    /**
     * @param null|callable(string, array<string, mixed>): void $progress
     * @param array<string, mixed> $context
     */
    private function reportProgress(?callable $progress, string $event, array $context): void
    {
        if ($progress !== null) {
            $progress($event, $context);
        }
    }

    private function normalizePrice(?string $price): ?string
    {
        if ($price === null || $price === '') {
            return null;
        }

        if (! is_numeric($price)) {
            throw new InvalidArgumentException(sprintf('Invalid price [%s].', $price));
        }

        return number_format((float) $price, 2, '.', '');
    }
}
