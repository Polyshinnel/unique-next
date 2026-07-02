<?php

namespace App\Domain\Seo\Services;

final readonly class SitemapGenerationResult
{
    public function __construct(
        public int $urlCount,
        public int $categoryCount,
        public int $productCount,
        public int $shipmentCount,
        public string $filePath,
    ) {}
}
