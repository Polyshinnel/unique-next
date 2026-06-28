<?php

namespace App\Domain\Catalog\Import\Data;

final readonly class AdvertisementData
{
    /**
     * @param list<RegionData> $regions
     * @param list<string> $tags
     * @param list<MediaItemData> $media
     */
    public function __construct(
        public int $externalId,
        public string $name,
        public ?string $sku,
        public ?int $categoryExternalId,
        public ?int $statusExternalId,
        public ?int $stateExternalId,
        public ?string $stateName,
        public ?int $availabilityExternalId,
        public ?string $availabilityName,
        public ?string $price,
        public bool $showPrice,
        public ?string $priceComment,
        public ?string $productAddress,
        public ?string $publishedAt,
        public ?ManagerData $manager,
        public array $regions,
        public array $tags,
        public array $media,
        public ?StatusWithCommentData $check,
        public ?StatusWithCommentData $loading,
        public ?StatusWithCommentData $removal,
        public ?string $mainCharacteristics,
        public ?string $complectation,
        public ?string $technicalCharacteristics,
        public ?string $mainInfo,
        public ?string $additionalInfo,
    ) {}
}
