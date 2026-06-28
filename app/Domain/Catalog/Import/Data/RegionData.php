<?php

namespace App\Domain\Catalog\Import\Data;

final readonly class RegionData
{
    public function __construct(
        public int $externalId,
        public string $name,
    ) {}
}
