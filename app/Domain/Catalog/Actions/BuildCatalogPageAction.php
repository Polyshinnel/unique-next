<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Support\CatalogQuery;
use App\Domain\Catalog\Support\CategoryTreeBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class BuildCatalogPageAction
{
    private const PER_PAGE = 12;

    public function __construct(
        private readonly CatalogQuery $catalog = new CatalogQuery,
        private readonly CategoryTreeBuilder $categories = new CategoryTreeBuilder,
        private readonly BuildCatalogFiltersAction $filters = new BuildCatalogFiltersAction,
    ) {}

    /**
     * @param  array{page: int, region: int|null, availability: int|null, state: int|null, sort: string, search: string|null, category_path: string|null}  $input
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
            'sort' => $input['sort'] ?? CatalogQuery::SORT_DEFAULT,
        ];

        return [
            'category' => $category,
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
}
