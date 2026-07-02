<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\EquipmentAvailability;
use App\Domain\Catalog\Models\EquipmentState;
use App\Domain\Catalog\Models\Region;
use App\Domain\Catalog\Support\CatalogQuery;
use App\Domain\Catalog\Support\CategoryTreeBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class BuildCatalogFiltersAction
{
    public function __construct(
        private readonly CatalogQuery $catalog = new CatalogQuery,
        private readonly CategoryTreeBuilder $categories = new CategoryTreeBuilder,
    ) {}

    /**
     * @param  array{region?: int|null, availability?: int|null, state?: int|null, search?: string|null, sort?: string|null}  $filters
     * @return array{regions: list<array<string, mixed>>, categories: list<array<string, mixed>>, availabilities: list<array<string, mixed>>, states: list<array<string, mixed>>}
     */
    public function execute(?Category $category, array $filters): array
    {
        return [
            'regions' => $this->regions($category, $filters),
            'categories' => $this->categoryTree($filters),
            'availabilities' => $this->availabilities($category, $filters),
            'states' => $this->states($category, $filters),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    private function regions(?Category $category, array $filters): array
    {
        $counts = $this->catalog
            ->filteredQuery($category, $filters, CatalogQuery::FILTER_REGION)
            ->join('product_region', 'product_region.product_id', '=', 'products.id')
            ->select('product_region.region_id', DB::raw('count(distinct products.id) as aggregate'))
            ->groupBy('product_region.region_id')
            ->pluck('aggregate', 'product_region.region_id');

        return Region::query()
            ->orderBy('name')
            ->orderBy('id')
            ->get()
            ->map(fn (Region $region): array => [
                'id' => (int) $region->getKey(),
                'name' => $region->name,
                'count' => (int) ($counts[(int) $region->getKey()] ?? 0),
                'href' => $this->href($category, $filters, ['region' => (int) $region->getKey()]),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    private function categoryTree(array $filters): array
    {
        $directCounts = $this->catalog
            ->filteredQuery(filters: $filters, except: CatalogQuery::FILTER_CATEGORY)
            ->whereNotNull('products.category_id')
            ->select('products.category_id', DB::raw('count(distinct products.id) as aggregate'))
            ->groupBy('products.category_id')
            ->pluck('aggregate', 'products.category_id');

        return array_values(array_filter(
            array_map(
                fn (array $node): ?array => $this->categoryNode($node, $filters, $directCounts->all()),
                $this->categories->tree(),
            ),
            static fn (?array $node): bool => $node !== null,
        ));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    private function availabilities(?Category $category, array $filters): array
    {
        $counts = $this->referenceCounts(
            $this->catalog->filteredQuery($category, $filters, CatalogQuery::FILTER_AVAILABILITY),
            'products.equipment_availability_id',
        );

        return EquipmentAvailability::query()
            ->orderBy('name')
            ->orderBy('id')
            ->get()
            ->map(fn (EquipmentAvailability $availability): array => [
                'id' => (int) $availability->getKey(),
                'name' => $availability->name,
                'count' => (int) ($counts[(int) $availability->getKey()] ?? 0),
                'href' => $this->href($category, $filters, ['availability' => (int) $availability->getKey()]),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    private function states(?Category $category, array $filters): array
    {
        $counts = $this->referenceCounts(
            $this->catalog->filteredQuery($category, $filters, CatalogQuery::FILTER_STATE),
            'products.equipment_state_id',
        );

        return EquipmentState::query()
            ->orderBy('name')
            ->orderBy('id')
            ->get()
            ->map(fn (EquipmentState $state): array => [
                'id' => (int) $state->getKey(),
                'name' => $state->name,
                'count' => (int) ($counts[(int) $state->getKey()] ?? 0),
                'href' => $this->href($category, $filters, ['state' => (int) $state->getKey()]),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Builder<\App\Domain\Catalog\Models\Product>  $query
     * @return array<int, int>
     */
    private function referenceCounts(Builder $query, string $column): array
    {
        return $query
            ->whereNotNull($column)
            ->select($column, DB::raw('count(distinct products.id) as aggregate'))
            ->groupBy($column)
            ->pluck('aggregate', $column)
            ->map(fn (mixed $count): int => (int) $count)
            ->all();
    }

    /**
     * @param  array<string, mixed>  $node
     * @param  array<string, mixed>  $filters
     * @param  array<int|string, mixed>  $directCounts
     * @return array<string, mixed>|null
     */
    private function categoryNode(array $node, array $filters, array $directCounts): ?array
    {
        $children = array_values(array_filter(
            array_map(
                fn (array $child): ?array => $this->categoryNode($child, $filters, $directCounts),
                $node['children'] ?? [],
            ),
            static fn (?array $child): bool => $child !== null,
        ));
        $count = (int) ($directCounts[(int) $node['id']] ?? 0);

        foreach ($children as $child) {
            $count += (int) $child['count'];
        }

        if ($count === 0) {
            return null;
        }

        return [
            'id' => (int) $node['id'],
            'name' => $node['name'],
            'slug' => $node['slug'],
            'count' => $count,
            'href' => $this->hrefForPath($node['href'], $filters),
            'level' => (int) $node['level'],
            'children' => $children,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  array<string, mixed>  $overrides
     */
    private function href(?Category $category, array $filters, array $overrides = []): string
    {
        return $this->hrefForPath($category === null ? '/catalog' : $this->categories->href($category), [
            ...$filters,
            ...$overrides,
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function hrefForPath(string $path, array $filters): string
    {
        $query = [];

        foreach (['region', 'availability', 'state'] as $key) {
            if (($filters[$key] ?? null) !== null) {
                $query[$key] = (int) $filters[$key];
            }
        }

        if (($filters['search'] ?? null) !== null && trim((string) $filters['search']) !== '') {
            $query['search'] = trim((string) $filters['search']);
        }

        if (($filters['sort'] ?? CatalogQuery::SORT_DEFAULT) !== CatalogQuery::SORT_DEFAULT) {
            $query['sort'] = (string) $filters['sort'];
        }

        return $query === [] ? $path : $path.'?'.http_build_query($query);
    }
}
