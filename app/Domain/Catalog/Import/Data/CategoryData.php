<?php

namespace App\Domain\Catalog\Import\Data;

final readonly class CategoryData
{
    public function __construct(
        public int $externalId,
        public string $name,
        public ?int $parentExternalId,
    ) {}
}
