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

final class ProductFeedImporter
{
    use WritesProductFromAdvertisement;

    private const PROGRESS_LOG_INTERVAL = 25;

    public function __construct(
        private FeedDownloader $downloader,
        private FeedParser $parser,
        private CategoryResolver $categories,
        private StatusResolvers $statuses,
        private ManagerResolver $managers,
        private RegionResolver $regions,
        private TagResolver $tags,
        private ImageDownloader $images,
        private ProductOgImageSynchronizer $ogImages,
    ) {}

    /**
     * @param null|callable(string, array<string, mixed>): void $progress
     */
    public function import(string $url, bool $updateExisting = false, ?callable $progress = null): ImportResult
    {
        Log::info('Catalog product feed import started.', [
            'url' => $url,
            'update_existing' => $updateExisting,
        ]);
        $this->reportProgress($progress, 'started', [
            'url' => $url,
            'update_existing' => $updateExisting,
        ]);

        $path = $this->downloader->download($url);
        $exportDate = $this->parser->exportDate($path);
        $created = 0;
        $skipped = 0;
        $failed = 0;
        $processed = 0;

        Log::info('Catalog product feed downloaded.', [
            'url' => $url,
            'export_date' => $exportDate,
        ]);
        $this->reportProgress($progress, 'feed_downloaded', [
            'url' => $url,
            'export_date' => $exportDate,
        ]);

        try {
            $referenceCounts = $this->importReferences($path);

            Log::info('Catalog import references imported.', [
                'url' => $url,
                'export_date' => $exportDate,
                ...$referenceCounts,
            ]);
            $this->reportProgress($progress, 'references_imported', $referenceCounts);

            foreach ($this->parser->advertisements($path) as $advertisement) {
                $processed++;

                try {
                    $wasCreated = false;

                    DB::transaction(function () use ($advertisement, $updateExisting, &$skipped, &$wasCreated): void {
                        $product = $this->findExistingProduct($advertisement);

                        if ($product !== null && ! $updateExisting) {
                            $skipped++;

                            return;
                        }

                        [$product, $wasCreated] = $this->upsertProduct($advertisement, $product);
                        $this->syncRelations($product, $advertisement);
                        $this->syncTextBlocks($product, $advertisement);
                        $this->syncStatusBlocks($product, $advertisement);

                        foreach ($advertisement->media as $mediaItem) {
                            $this->images->download($product, $mediaItem);
                        }

                        $this->ogImages->sync($product);
                    });

                    if ($wasCreated) {
                        $created++;
                    }

                    if ($processed === 1 || $processed % self::PROGRESS_LOG_INTERVAL === 0) {
                        $this->logAdvertisementProgress(
                            progress: $progress,
                            processed: $processed,
                            created: $created,
                            skipped: $skipped,
                            failed: $failed,
                            advertisementExternalId: $advertisement->externalId,
                            sku: $advertisement->sku,
                        );
                    }
                } catch (Throwable $exception) {
                    $failed++;

                    Log::error('Catalog product import failed for advertisement.', [
                        'advertisement_external_id' => $advertisement->externalId,
                        'sku' => $advertisement->sku,
                        'error' => $exception->getMessage(),
                    ]);
                    $this->reportProgress($progress, 'failed_item', [
                        'processed' => $processed,
                        'created' => $created,
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

        Log::info('Catalog product feed import finished.', [
            'url' => $url,
            'export_date' => $exportDate,
            'processed' => $processed,
            'created' => $created,
            'skipped' => $skipped,
            'failed' => $failed,
        ]);
        $this->reportProgress($progress, 'finished', [
            'url' => $url,
            'export_date' => $exportDate,
            'processed' => $processed,
            'created' => $created,
            'skipped' => $skipped,
            'failed' => $failed,
        ]);

        return new ImportResult(
            created: $created,
            skipped: $skipped,
            failed: $failed,
            message: sprintf(
                'Processed %d advertisements: created=%d, skipped=%d, failed=%d.',
                $processed,
                $created,
                $skipped,
                $failed,
            ),
        );
    }

    /**
     * @param null|callable(string, array<string, mixed>): void $progress
     */
    private function logAdvertisementProgress(
        ?callable $progress,
        int $processed,
        int $created,
        int $skipped,
        int $failed,
        int $advertisementExternalId,
        ?string $sku,
    ): void {
        $context = [
            'processed' => $processed,
            'created' => $created,
            'skipped' => $skipped,
            'failed' => $failed,
            'advertisement_external_id' => $advertisementExternalId,
            'sku' => $sku,
        ];

        Log::info('Catalog product feed import progress.', $context);
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
     * @return array{0: Product, 1: bool}
     */
    private function upsertProduct(AdvertisementData $advertisement, ?Product $product): array
    {
        $attributes = $this->productAttributes($advertisement);

        if ($product === null) {
            /** @var Product $product */
            $product = Product::query()->create(array_merge($attributes, [
                'name' => $advertisement->name,
                'sku' => $advertisement->sku,
                'title' => $advertisement->name,
            ]));

            return [$product, true];
        }

        $product->fill(array_merge($attributes, [
            'external_id' => $product->external_id ?? $advertisement->externalId,
            'title' => $advertisement->name,
        ]));
        $product->save();

        return [$product->fresh(), false];
    }
}
