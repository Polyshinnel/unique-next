<?php

namespace App\Domain\Catalog\Import\Services\Concerns;

use App\Domain\Catalog\Import\Data\AdvertisementData;
use App\Domain\Catalog\Import\Data\StatusWithCommentData;
use App\Domain\Catalog\Import\Resolvers\CategoryResolver;
use App\Domain\Catalog\Import\Resolvers\ManagerResolver;
use App\Domain\Catalog\Import\Resolvers\RegionResolver;
use App\Domain\Catalog\Import\Resolvers\StatusResolvers;
use App\Domain\Catalog\Import\Resolvers\TagResolver;
use App\Domain\Catalog\Import\Services\FeedParser;
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
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Shared logic for mapping an {@see AdvertisementData} onto a {@see Product} and
 * its related rows. Using classes must expose the resolver dependencies as
 * properties: $parser, $categories, $statuses, $managers, $regions, $tags.
 *
 * @property FeedParser $parser
 * @property CategoryResolver $categories
 * @property StatusResolvers $statuses
 * @property ManagerResolver $managers
 * @property RegionResolver $regions
 * @property TagResolver $tags
 */
trait WritesProductFromAdvertisement
{
    /**
     * @return array{categories: int, advertisement_statuses: int, check_statuses: int, install_statuses: int}
     */
    protected function importReferences(string $path): array
    {
        $categories = iterator_to_array($this->parser->categories($path), false);

        $this->categories->upsertMany($categories);
        $this->categories->linkParents($categories);

        $advertisementStatuses = 0;
        $checkStatuses = 0;
        $installStatuses = 0;

        foreach ($this->parser->advertisementStatuses($path) as $status) {
            $this->statuses->productStatus($status);
            $advertisementStatuses++;
        }

        foreach ($this->parser->checkStatuses($path) as $status) {
            $this->statuses->checkStatus($status);
            $checkStatuses++;
        }

        foreach ($this->parser->installStatuses($path) as $status) {
            $this->statuses->installStatus($status);
            $installStatuses++;
        }

        return [
            'categories' => count($categories),
            'advertisement_statuses' => $advertisementStatuses,
            'check_statuses' => $checkStatuses,
            'install_statuses' => $installStatuses,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function productAttributes(AdvertisementData $advertisement): array
    {
        $manager = $advertisement->manager !== null
            ? $this->managers->upsert($advertisement->manager)
            : null;
        $regionId = null;

        if ($advertisement->regions !== []) {
            $regionId = $this->regions->upsert($advertisement->regions[0])->getKey();
        }

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
            'region_id' => $regionId,
            'published_at' => $this->normalizePublishedAt($advertisement->publishedAt),
        ];
    }

    protected function resolveEquipmentStateId(AdvertisementData $advertisement): ?int
    {
        if ($advertisement->stateExternalId === null || $advertisement->stateName === null) {
            return null;
        }

        return $this->statuses
            ->equipmentState($advertisement->stateExternalId, $advertisement->stateName)
            ->getKey();
    }

    protected function resolveEquipmentAvailabilityId(AdvertisementData $advertisement): ?int
    {
        if ($advertisement->availabilityExternalId === null || $advertisement->availabilityName === null) {
            return null;
        }

        return $this->statuses
            ->equipmentAvailability($advertisement->availabilityExternalId, $advertisement->availabilityName)
            ->getKey();
    }

    protected function syncRelations(Product $product, AdvertisementData $advertisement): void
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

    protected function syncTextBlocks(Product $product, AdvertisementData $advertisement): void
    {
        $this->updateTextBlock(ProductMainCharacteristic::class, $product->id, $advertisement->mainCharacteristics);
        $this->updateTextBlock(ProductComplectation::class, $product->id, $advertisement->complectation);
        $this->updateTextBlock(ProductTechnicalCharacteristic::class, $product->id, $advertisement->technicalCharacteristics);
        $this->updateTextBlock(ProductMainInfo::class, $product->id, $advertisement->mainInfo);
        $this->updateTextBlock(ProductAdditionalInfo::class, $product->id, $advertisement->additionalInfo);
    }

    protected function syncStatusBlocks(Product $product, AdvertisementData $advertisement): void
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
    protected function updateTextBlock(string $modelClass, int $productId, ?string $content): void
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
    protected function updateStatusBlock(
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

    protected function normalizePrice(?string $price): ?string
    {
        if ($price === null || $price === '') {
            return null;
        }

        if (! is_numeric($price)) {
            throw new InvalidArgumentException(sprintf('Invalid price [%s].', $price));
        }

        return number_format((float) $price, 2, '.', '');
    }

    protected function normalizePublishedAt(?string $publishedAt): ?CarbonImmutable
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
