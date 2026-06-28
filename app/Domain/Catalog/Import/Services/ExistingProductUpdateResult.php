<?php

namespace App\Domain\Catalog\Import\Services;

final readonly class ExistingProductUpdateResult
{
    public function __construct(
        public int $updated,
        public int $skipped,
        public int $failed,
        public string $message,
    ) {}
}
