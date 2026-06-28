<?php

namespace App\Domain\Catalog\Import\Services;

use App\Domain\Catalog\Import\Data\AdvertisementData;
use App\Domain\Catalog\Import\Data\StatusWithCommentData;
use App\Domain\Catalog\Import\Resolvers\CategoryResolver;
use App\Domain\Catalog\Import\Resolvers\ManagerResolver;
use App\Domain\Catalog\Import\Resolvers\RegionResolver;
use App\Domain\Catalog\Import\Resolvers\StatusResolvers;
use App\Domain\Catalog\Import\Resolvers\TagResolver;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductAdditionalInfo;
use App\Domain\Catalog\Models\ProductCheck;
use App\Domain\Catalog\Models\ProductComplectation;
use App\Domain\Catalog\Models\ProductDismantling;
use App\Domain\Catalog\Models\ProductLoading;
use App\Domain\Catalog\Models\ProductMainCharacteristic;
use App\Domain\Catalog\Models\ProductMainInfo;
use App\Domain\Catalog\Models\ProductTechnicalCharacteristic;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

final class ProductFeedImporter
{
    public function __construct(
        private FeedDownloader $downloader,
        private FeedParser $parser,
        private CategoryResolver $categories,
        private StatusResolvers $statuses,
        private ManagerResolver $managers,
        private RegionResolver $regions,
        private TagResolver $tags,
        private ImageDownloader $images,
    ) {}

    public function import(string $url, bool $updateExisting = false): ImportResult
    {
        $path = $this->downloader->download($url);
        $created = 0;
        $skipped = 0;
        $failed = 0;

        try {
            $this->importReferences($path);

            foreach ($this->parser->advertisements($path) as $advertisement) {
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
                    });

                    if ($wasCreated) {
                        $created++;
                    }
                } catch (Throwable $exception) {
                    $failed++;

                    Log::error('Catalog product import failed for advertisement.', [
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
            'created' => $created,
            'skipped' => $skipped,
            'failed' => $failed,
        ]);

        return new ImportResult(
            created: $created,
            skipped: $skipped,
            failed: $failed,
        );
    }

    private function importReferences(string $path): void
    {
        $this->categories->upsertMany($this->parser->categories($path));
        $this->categories->linkParents($this->parser->categories($path));

        foreach ($this->parser->advertisementStatuses($path) as $status) {
            $this->statuses->productStatus($status);
        }

        foreach ($this->parser->checkStatuses($path) as $status) {
            $this->statuses->checkStatus($status);
        }

        foreach ($this->parser->installStatuses($path) as $status) {
            $this->statuses->installStatus($status);
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
            ]));

            return [$product, true];
        }

        $product->fill(array_merge($attributes, [
            'external_id' => $product->external_id ?? $advertisement->externalId,
        ]));
        $product->save();

        return [$product->fresh(), false];
    }

    private function productAttributes(AdvertisementData $advertisement): array
    {
        $manager = $advertisement->manager !== null
            ? $this->managers->upsert($advertisement->manager)
            : null;

        return [
            'external_id' => $advertisement->externalId,
            'description' => null,
            'og_image' => null,
            'category_id' => $advertisement->categoryExternalId !== null
                ? $this->categories->resolveByExternalId($advertisement->categoryExternalId)?->getKey()
                : null,
            'manager_id' => $manager?->getKey(),
            'equipment_state_id' => $this->resolveEquipmentStateId($advertisement),
            'equipment_availability_id' => $this->resolveEquipmentAvailabilityId($advertisement),
            'product_status_id' => $advertisement->statusExternalId !== null
                ? $this->statuses->productStatusByExternalId($advertisement->statusExternalId)?->getKey()
                : null,
            'price' => $this->normalizePrice($advertisement->price),
            'show_price' => $advertisement->showPrice,
            'price_comment' => $advertisement->priceComment,
            'product_address' => $advertisement->productAddress,
            'published_at' => $this->normalizePublishedAt($advertisement->publishedAt),
        ];
    }

    private function resolveEquipmentStateId(AdvertisementData $advertisement): ?int
    {
        if ($advertisement->stateExternalId === null || $advertisement->stateName === null) {
            return null;
        }

        return $this->statuses
            ->equipmentState($advertisement->stateExternalId, $advertisement->stateName)
            ->getKey();
    }

    private function resolveEquipmentAvailabilityId(AdvertisementData $advertisement): ?int
    {
        if ($advertisement->availabilityExternalId === null || $advertisement->availabilityName === null) {
            return null;
        }

        return $this->statuses
            ->equipmentAvailability($advertisement->availabilityExternalId, $advertisement->availabilityName)
            ->getKey();
    }

    private function syncRelations(Product $product, AdvertisementData $advertisement): void
    {
        $regionIds = [];

        foreach ($advertisement->regions as $region) {
            $regionIds[] = $this->regions->upsert($region)->getKey();
        }

        $product->regions()->sync($regionIds);

        $tagIds = [];

        foreach ($advertisement->tags as $tagName) {
            $tagIds[] = $this->tags->resolve($tagName)->getKey();
        }

        $product->tags()->sync($tagIds);

        if ($advertisement->manager !== null) {
            $manager = $this->managers->upsert($advertisement->manager);

            $product->managers()->syncWithoutDetaching([
                $manager->getKey() => ['role' => 'product_owner'],
            ]);

            if ($product->manager_id !== $manager->getKey()) {
                $product->forceFill(['manager_id' => $manager->getKey()])->save();
            }
        }
    }

    private function syncTextBlocks(Product $product, AdvertisementData $advertisement): void
    {
        $this->updateTextBlock(ProductMainCharacteristic::class, $product->id, $advertisement->mainCharacteristics);
        $this->updateTextBlock(ProductComplectation::class, $product->id, $advertisement->complectation);
        $this->updateTextBlock(ProductTechnicalCharacteristic::class, $product->id, $advertisement->technicalCharacteristics);
        $this->updateTextBlock(ProductMainInfo::class, $product->id, $advertisement->mainInfo);
        $this->updateTextBlock(ProductAdditionalInfo::class, $product->id, $advertisement->additionalInfo);
    }

    private function syncStatusBlocks(Product $product, AdvertisementData $advertisement): void
    {
        $this->updateStatusBlock(
            ProductCheck::class,
            'check_status_id',
            $product->id,
            $advertisement->check,
            fn (string $name): int => $this->statuses->checkStatusByName($name)->getKey(),
        );

        $this->updateStatusBlock(
            ProductLoading::class,
            'shipment_status_id',
            $product->id,
            $advertisement->loading,
            fn (string $name): int => $this->statuses->shipmentStatusByName($name)->getKey(),
        );

        $this->updateStatusBlock(
            ProductDismantling::class,
            'dismantle_status_id',
            $product->id,
            $advertisement->removal,
            fn (string $name): int => $this->statuses->dismantleStatusByName($name)->getKey(),
        );
    }

    /**
     * @param class-string<Model> $modelClass
     */
    private function updateTextBlock(string $modelClass, int $productId, ?string $content): void
    {
        $modelClass::query()->updateOrCreate(
            ['product_id' => $productId],
            ['content' => $content],
        );
    }

    /**
     * @param class-string<Model> $modelClass
     * @param callable(string): int $statusResolver
     */
    private function updateStatusBlock(
        string $modelClass,
        string $statusColumn,
        int $productId,
        ?StatusWithCommentData $statusWithComment,
        callable $statusResolver,
    ): void {
        if ($statusWithComment === null) {
            $modelClass::query()->where('product_id', $productId)->delete();

            return;
        }

        $statusId = $statusWithComment->name !== ''
            ? $statusResolver($statusWithComment->name)
            : null;

        $modelClass::query()->updateOrCreate(
            ['product_id' => $productId],
            [
                $statusColumn => $statusId,
                'comment' => $statusWithComment?->comment,
            ],
        );
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

    private function normalizePublishedAt(?string $publishedAt): ?CarbonImmutable
    {
        if ($publishedAt === null || $publishedAt === '') {
            return null;
        }

        $date = CarbonImmutable::createFromFormat('Y-m-d H:i:s', $publishedAt);

        if ($date === false) {
            throw new InvalidArgumentException(sprintf('Invalid published_at [%s].', $publishedAt));
        }

        return $date;
    }
}
