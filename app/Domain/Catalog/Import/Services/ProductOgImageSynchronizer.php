<?php

namespace App\Domain\Catalog\Import\Services;

use App\Domain\Catalog\Models\Product;

final class ProductOgImageSynchronizer
{
    public function sync(Product $product): void
    {
        $ogImagePath = $product->images()
            ->where('sort_order', 0)
            ->orderBy('id')
            ->value('file_path');

        if ($product->og_image === $ogImagePath) {
            return;
        }

        $product->forceFill([
            'og_image' => $ogImagePath,
        ])->save();
    }
}
