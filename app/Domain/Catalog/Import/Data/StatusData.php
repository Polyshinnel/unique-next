<?php

namespace App\Domain\Catalog\Import\Data;

final readonly class StatusData
{
    public function __construct(
        public int $externalId,
        public string $name,
        public ?string $color,
    ) {}
}
