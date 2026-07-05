<?php

namespace App\Domain\Catalog\Support;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Builder;

final class CatalogQuery
{
    /**
     * @var list<string>
     */
    private const PUBLIC_PRODUCT_STATUSES = [
        'В продаже',
        'Резерв',
    ];

    public const FILTER_CATEGORY = 'category';

    public const FILTER_REGION = 'region';

    public const FILTER_AVAILABILITY = 'availability';

    public const FILTER_STATE = 'state';

    public const FILTER_SEARCH = 'search';

    public const SORT_DEFAULT = 'default';

    public const SORT_PRICE_DESC = 'price_desc';

    public const SORT_PRICE_ASC = 'price_asc';

    /**
     * @var list<string>
     */
    private const LIST_RELATIONS = [
        'category',
        'mainImage',
        'productStatus',
        'equipmentAvailability',
        'equipmentState',
        'regions',
    ];

    public function __construct(
        private readonly CategoryTreeBuilder $categories = new CategoryTreeBuilder,
    ) {}

    /**
     * @return Builder<Product>
     */
    public function baseQuery(): Builder
    {
        return $this->detailQuery()
            ->whereHas(
                'productStatus',
                fn (Builder $query): Builder => $query->whereIn('name', self::PUBLIC_PRODUCT_STATUSES),
            );
    }

    /**
     * @return Builder<Product>
     */
    public function detailQuery(): Builder
    {
        return Product::query()
            ->whereNotNull('products.published_at');
    }

    /**
     * @param  array{region?: int|null, availability?: int|null, state?: int|null, search?: string|null, sort?: string|null}  $filters
     * @param  string|list<string>|null  $except
     * @return Builder<Product>
     */
    public function listQuery(?Category $category = null, array $filters = [], string|array|null $except = null): Builder
    {
        $query = $this->filteredQuery($category, $filters, $except)
            ->with(self::LIST_RELATIONS);

        return $this->applySort($query, $filters['sort'] ?? self::SORT_DEFAULT);
    }

    /**
     * @param  array{region?: int|null, availability?: int|null, state?: int|null, search?: string|null}  $filters
     * @param  string|list<string>|null  $except
     * @return Builder<Product>
     */
    public function filteredQuery(?Category $category = null, array $filters = [], string|array|null $except = null): Builder
    {
        return $this->applyFilters($this->baseQuery(), $category, $filters, $except);
    }

    /**
     * @param  Builder<Product>  $query
     * @param  array{region?: int|null, availability?: int|null, state?: int|null, search?: string|null}  $filters
     * @param  string|list<string>|null  $except
     * @return Builder<Product>
     */
    public function applyFilters(Builder $query, ?Category $category = null, array $filters = [], string|array|null $except = null): Builder
    {
        $except = $this->normalizeExcept($except);

        if ($category !== null && ! in_array(self::FILTER_CATEGORY, $except, true)) {
            $query->whereIn('products.category_id', $this->categories->descendantIds($category));
        }

        if (($filters['region'] ?? null) !== null && ! in_array(self::FILTER_REGION, $except, true)) {
            $regionId = (int) $filters['region'];

            $query->whereHas(
                'regions',
                fn (Builder $query): Builder => $query->whereKey($regionId),
            );
        }

        if (($filters['availability'] ?? null) !== null && ! in_array(self::FILTER_AVAILABILITY, $except, true)) {
            $query->where('products.equipment_availability_id', (int) $filters['availability']);
        }

        if (($filters['state'] ?? null) !== null && ! in_array(self::FILTER_STATE, $except, true)) {
            $query->where('products.equipment_state_id', (int) $filters['state']);
        }

        if (($filters['search'] ?? null) !== null && ! in_array(self::FILTER_SEARCH, $except, true)) {
            $this->applySearch($query, (string) $filters['search']);
        }

        return $query;
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function applySort(Builder $query, ?string $sort = self::SORT_DEFAULT): Builder
    {
        return match ($sort) {
            self::SORT_PRICE_DESC => $this->applyPriceSort($query, 'desc'),
            self::SORT_PRICE_ASC => $this->applyPriceSort($query, 'asc'),
            default => $query->orderByDesc('products.id'),
        };
    }

    /**
     * @param  Builder<Product>  $query
     */
    private function applySearch(Builder $query, string $search): void
    {
        $like = '%'.$this->escapeLike($search).'%';

        $query->where(function (Builder $query) use ($like): void {
            $query
                ->whereRaw("products.title LIKE ? ESCAPE '\\\\'", [$like])
                ->orWhereRaw("products.name LIKE ? ESCAPE '\\\\'", [$like])
                ->orWhereRaw("products.sku LIKE ? ESCAPE '\\\\'", [$like])
                ->orWhereHas(
                    'tags',
                    fn (Builder $query): Builder => $query->whereRaw("tags.name LIKE ? ESCAPE '\\\\'", [$like]),
                );
        });
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    private function applyPriceSort(Builder $query, string $direction): Builder
    {
        return $query
            ->orderByRaw(
                'case when products.show_price = 1 and products.price is not null and exists (select 1 from product_statuses where product_statuses.id = products.product_status_id and product_statuses.name <> ?) then 0 else 1 end asc',
                ['Резерв'],
            )
            ->orderBy('products.price', $direction)
            ->orderByDesc('products.id');
    }

    private function escapeLike(string $value): string
    {
        return str_replace(
            ['\\', '%', '_'],
            ['\\\\', '\\%', '\\_'],
            $value,
        );
    }

    /**
     * @param  string|list<string>|null  $except
     * @return list<string>
     */
    private function normalizeExcept(string|array|null $except): array
    {
        if ($except === null) {
            return [];
        }

        return is_string($except) ? [$except] : array_values($except);
    }
}
