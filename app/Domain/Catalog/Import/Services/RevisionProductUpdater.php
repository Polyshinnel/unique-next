<?php

namespace App\Domain\Catalog\Import\Services;

use App\Domain\Catalog\Import\Data\AdvertisementData;
use App\Domain\Catalog\Import\Resolvers\CategoryResolver;
use App\Domain\Catalog\Import\Resolvers\ManagerResolver;
use App\Domain\Catalog\Import\Resolvers\RegionResolver;
use App\Domain\Catalog\Import\Resolvers\StatusResolvers;
use App\Domain\Catalog\Import\Resolvers\TagResolver;
use App\Domain\Catalog\Import\Services\Concerns\WritesProductFromAdvertisement;
use App\Domain\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Full revision update of EXISTING products only.
 *
 * For each advertisement in the feed it fully updates the matching product
 * (all scalar fields, relations, text/status blocks) and synchronises the whole
 * image set (reorder + cleanup + download). Advertisements that have no matching
 * product are skipped — this command never creates new products.
 */
final class RevisionProductUpdater
{
    use WritesProductFromAdvertisement;

    private const PROGRESS_LOG_INTERVAL = 50;

    public function __construct(
        private FeedDownloader $downloader,
        private FeedParser $parser,
        private CategoryResolver $categories,
        private StatusResolvers $statuses,
        private ManagerResolver $managers,
        private RegionResolver $regions,
        private TagResolver $tags,
        private ProductImageSynchronizer $images,
    ) {}

    /**
     * @param null|callable(string, array<string, mixed>): void $progress
     */
    public function update(string $url, ?callable $progress = null): ExistingProductUpdateResult
    {
        Log::info('Revision product update started.', ['url' => $url]);
        $this->reportProgress($progress, 'started', ['url' => $url]);

        $path = $this->downloader->download($url);
        $exportDate = $this->parser->exportDate($path);
        $updated = 0;
        $skipped = 0;
        $failed = 0;
        $processed = 0;

        Log::info('Revision product feed downloaded.', [
            'url' => $url,
            'export_date' => $exportDate,
        ]);
        $this->reportProgress($progress, 'feed_downloaded', [
            'url' => $url,
            'export_date' => $exportDate,
        ]);

        try {
            $referenceCounts = $this->importReferences($path);

            Log::info('Revision product references imported.', [
                'url' => $url,
                ...$referenceCounts,
            ]);
            $this->reportProgress($progress, 'references_imported', $referenceCounts);

            foreach ($this->parser->advertisements($path) as $advertisement) {
                $processed++;

                try {
                    $product = $this->findExistingProduct($advertisement);

                    if ($product === null) {
                        $skipped++;
                        $this->maybeLogProgress($progress, $processed, $updated, $skipped, $failed, $advertisement);

                        continue;
                    }

                    DB::transaction(function () use ($product, $advertisement): void {
                        $this->updateProduct($product, $advertisement);
                    });

                    $updated++;
                    $this->maybeLogProgress($progress, $processed, $updated, $skipped, $failed, $advertisement);
                } catch (Throwable $exception) {
                    $failed++;

                    Log::error('Revision product update failed for advertisement.', [
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

        Log::info('Revision product feed update finished.', [
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

    private function updateProduct(Product $product, AdvertisementData $advertisement): void
    {
        $product->fill($this->productAttributes($advertisement));
        $product->name = $advertisement->name;
        $product->title = $advertisement->name;

        if ($advertisement->sku !== null) {
            $product->sku = $advertisement->sku;
        }

        $product->save();

        $this->syncRelations($product, $advertisement);
        $this->syncTextBlocks($product, $advertisement);
        $this->syncStatusBlocks($product, $advertisement);
        $this->images->sync($product, $advertisement->media);
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

    /**
     * @param null|callable(string, array<string, mixed>): void $progress
     */
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

        Log::info('Revision product feed update progress.', $context);
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
}
