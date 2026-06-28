<?php

namespace App\Domain\Catalog\Import\Services;

final readonly class ImportResult
{
    public function __construct(
        public int $created = 0,
        public int $skipped = 0,
        public int $failed = 0,
        public ?string $message = null,
    ) {}
}
