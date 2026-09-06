<?php

namespace App\Http\Resources\Catalog;

use App\Domain\Catalog\Actions\ResolveCatalogProductHrefAction;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductImage;
use App\Domain\Catalog\Models\Region;
use App\Domain\Catalog\Support\CategoryTreeBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CatalogProductCardResource extends JsonResource
{
    public static $wrap = null;

    private CategoryTreeBuilder $categories;

    private ResolveCatalogProductHrefAction $href;

    public function __construct(
        $resource,
        ?CategoryTreeBuilder $categories = null,
        ?ResolveCatalogProductHrefAction $href = null,
    ) {
        parent::__construct($resource);

        $this->categories = $categories ?? new CategoryTreeBuilder;
        $this->href = $href ?? new ResolveCatalogProductHrefAction($this->categories);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Product $product */
        $product = $this->resource;

        return [
            'id' => (int) $product->getKey(),
            'title' => $product->title ?? $product->name,
            'sku' => $product->sku,
            'category' => $this->category($product->category),
            'region' => $this->region($product),
            'price' => $this->price($product),
            'availability' => $this->availability($product),
            'state' => $this->state($product),
            'imageUrl' => $this->productImageUrl($product),
            'href' => $this->productHref($product),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function category(?Category $category): ?array
    {
        if ($category === null) {
            return null;
        }

        $categoryData = $this->categories->categoryData($category);

        if ($categoryData === null) {
            return null;
        }

        return [
            'id' => (int) $categoryData['id'],
            'name' => $categoryData['name'],
            'title' => $categoryData['title'] ?? $categoryData['name'],
            'slug' => $categoryData['slug'],
            'path' => $categoryData['path'],
            'pathString' => $categoryData['pathString'],
            'href' => $categoryData['href'],
            'level' => $categoryData['level'],
            'breadcrumbs' => $categoryData['breadcrumbs'] ?? [],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function region(Product $product): ?array
    {
        $region = $product->regions->first();

        if ($region === null && $product->relationLoaded('region')) {
            $region = $product->region;
        }

        if (! $region instanceof Region) {
            return null;
        }

        return [
            'id' => (int) $region->getKey(),
            'name' => $region->name,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function price(Product $product): array
    {
        $isSold = ! in_array($product->productStatus?->name, ['В продаже', 'Резерв'], true);
        $isReserve = $product->productStatus?->name === 'Резерв';
        $isPublished = (bool) $product->show_price && ! $isReserve && ! $isSold;

        return [
            'amount' => $isPublished && $product->price !== null ? (string) $product->price : null,
            'isPublished' => $isPublished,
            'isReserve' => $isReserve,
            'isSold' => $isSold,
            'label' => $this->priceLabel($product, $isSold, $isReserve, $isPublished),
            'comment' => $product->price_comment,
        ];
    }

    private function priceLabel(Product $product, bool $isSold, bool $isReserve, bool $isPublished): string
    {
        if ($isSold) {
            return 'Продано';
        }

        if ($isReserve) {
            return 'Резерв';
        }

        if ($isPublished && $product->price !== null) {
            return number_format((float) $product->price, 0, '.', ' ').' ₽';
        }

        return 'По запросу';
    }

    /**
     * @return array<string, mixed>|null
     */
    private function availability(Product $product): ?array
    {
        if ($product->equipmentAvailability === null) {
            return null;
        }

        return [
            'id' => (int) $product->equipmentAvailability->getKey(),
            'name' => $product->equipmentAvailability->name,
            'color' => $product->equipmentAvailability->color,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function state(Product $product): ?array
    {
        if ($product->equipmentState === null) {
            return null;
        }

        return [
            'id' => (int) $product->equipmentState->getKey(),
            'name' => $product->equipmentState->name,
        ];
    }

    private function imageUrl(?ProductImage $image): string
    {
        return $this->pathUrl($image?->file_path ?: $image?->file_url);
    }

    private function productImageUrl(Product $product): string
    {
        $imagePath = $product->mainImage?->file_path ?: $product->mainImage?->file_url;

        return $this->pathUrl($imagePath ?: $product->og_image);
    }

    private function pathUrl(?string $path): string
    {
        if ($path === null || $path === '') {
            return '/assets/img/catalog.jpeg';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return '/storage/'.ltrim($path, '/');
    }

    private function productHref(Product $product): string
    {
        return $this->href->execute($product);
    }
}
