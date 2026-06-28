<?php

namespace App\Domain\Catalog\Import\Data;

final readonly class MediaItemData
{
    public function __construct(
        public int $externalId,
        public string $fileName,
        public string $fileUrl,
        public ?string $mimeType,
        public ?int $fileSize,
        public int $sortOrder,
        public bool $isMain,
    ) {}
}
