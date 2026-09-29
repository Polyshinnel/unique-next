<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Support\CatalogQuery;
use App\Domain\Catalog\Support\CategoryTreeBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class BuildCatalogPageAction
{
    private const PER_PAGE = 15;

    public function __construct(
        private readonly CatalogQuery $catalog = new CatalogQuery,
        private readonly CategoryTreeBuilder $categories = new CategoryTreeBuilder,
        private readonly BuildCatalogFiltersAction $filters = new BuildCatalogFiltersAction,
    ) {}

    /**
     * @param  array{page: int, region: int|null, availability: int|null, state: int|null, sort: string, search: string|null, price_from?: float|null, price_to?: float|null, category_path: string|null, exact_category?: bool, exclude_product_id?: int|null}  $input
     * @return array<string, mixed>
     */
    public function execute(array $input): array
    {
        $category = $this->resolveCategory($input['category_path'] ?? null);
        $filters = [
            'region' => $input['region'] ?? null,
            'availability' => $input['availability'] ?? null,
            'state' => $input['state'] ?? null,
            'search' => $input['search'] ?? null,
            'price_from' => $input['price_from'] ?? null,
            'price_to' => $input['price_to'] ?? null,
            'sort' => $input['sort'] ?? CatalogQuery::SORT_DEFAULT,
            'exact_category' => $input['exact_category'] ?? false,
            'exclude_product_id' => $input['exclude_product_id'] ?? null,
        ];
        $priceRangeQuery = $this->catalog->filteredQuery($category)
            ->where('products.show_price', true)
            ->whereNotNull('products.price');
        $priceRange = [
            'min' => $this->wholePrice($priceRangeQuery->clone()->min('products.price')),
            'max' => $this->wholePrice($priceRangeQuery->clone()->max('products.price')),
        ];

        return [
            'category' => $category,
            'price_range' => $priceRange,
            'filters' => $this->filters->execute($category, $filters),
            'sorting' => ['active' => $filters['sort']],
            'products' => $this->catalog
                ->listQuery($category, $filters)
                ->paginate(self::PER_PAGE, ['*'], 'page', $input['page']),
        ];
    }

    private function resolveCategory(?string $path): ?Category
    {
        if ($path === null || $path === '') {
            return null;
        }

        $category = $this->categories->resolvePath($path);

        if ($category === null) {
            throw new NotFoundHttpException('Catalog category not found.');
        }

        return $category;
    }

    private function wholePrice(mixed $price): ?int
    {
        return $price === null ? null : (int) $price;
    }
}
