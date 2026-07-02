<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Support\CategoryTreeBuilder;

final class ResolveCatalogProductHrefAction
{
    public function __construct(
        private readonly CategoryTreeBuilder $categories = new CategoryTreeBuilder,
    ) {}

    public function execute(Product $product): string
    {
        $productRouteKey = filled($product->slug) ? $product->slug : (string) $product->getKey();

        if ($product->category instanceof Category) {
            return $this->categories->href($product->category).'/'.$productRouteKey;
        }

        return '/catalog/'.$productRouteKey;
    }
}
