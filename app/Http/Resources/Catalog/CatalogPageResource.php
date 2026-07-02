<?php

namespace App\Http\Resources\Catalog;

use App\Domain\Catalog\Support\CategoryTreeBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CatalogPageResource extends JsonResource
{
    public static $wrap = null;

    public function __construct($resource, private readonly CategoryTreeBuilder $categories = new CategoryTreeBuilder)
    {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $products = $this->resource['products'];
        $category = $this->resource['category'] ?? null;

        return [
            'category' => $category === null ? null : (new CatalogCategoryResource($category, $this->categories))->resolve($request),
            'filters' => [
                'regions' => $this->filterOptions($this->resource['filters']['regions'] ?? [], $request),
                'categories' => $this->filterOptions($this->resource['filters']['categories'] ?? [], $request),
                'availabilities' => $this->filterOptions($this->resource['filters']['availabilities'] ?? [], $request),
                'states' => $this->filterOptions($this->resource['filters']['states'] ?? [], $request),
            ],
            'sorting' => [
                'active' => $this->resource['sorting']['active'] ?? 'default',
                'options' => $this->resource['sorting']['options'] ?? [
                    ['value' => 'default', 'label' => 'По умолчанию'],
                    ['value' => 'price_desc', 'label' => 'По убыванию цены'],
                    ['value' => 'price_asc', 'label' => 'По возрастанию цены'],
                ],
            ],
            'products' => $this->products($products, $request),
            'pagination' => $this->pagination($products),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function filterOptions(iterable $options, Request $request): array
    {
        $items = is_array($options) ? $options : iterator_to_array($options);

        return array_map(
            fn (mixed $option): array => (new CatalogFilterOptionResource($option))->resolve($request),
            $items,
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function products(mixed $products, Request $request): array
    {
        $items = $products instanceof LengthAwarePaginator ? $products->items() : $products;

        return array_map(
            fn (mixed $product): array => (new CatalogProductCardResource($product, $this->categories))->resolve($request),
            is_array($items) ? $items : iterator_to_array($items),
        );
    }

    /**
     * @return array{currentPage: int, perPage: int, total: int, totalPages: int}
     */
    private function pagination(mixed $products): array
    {
        if ($products instanceof LengthAwarePaginator) {
            return [
                'currentPage' => $products->currentPage(),
                'perPage' => $products->perPage(),
                'total' => $products->total(),
                'totalPages' => $products->lastPage(),
            ];
        }

        $total = is_countable($products) ? count($products) : 0;

        return [
            'currentPage' => 1,
            'perPage' => $total,
            'total' => $total,
            'totalPages' => 1,
        ];
    }
}
