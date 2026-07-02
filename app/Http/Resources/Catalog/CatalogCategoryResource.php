<?php

namespace App\Http\Resources\Catalog;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Support\CategoryTreeBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CatalogCategoryResource extends JsonResource
{
    public static $wrap = null;

    public function __construct($resource, private readonly CategoryTreeBuilder $categories = new CategoryTreeBuilder)
    {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function toArray(Request $request): ?array
    {
        if ($this->resource === null) {
            return null;
        }

        $category = $this->categoryData();

        return [
            'id' => (int) $category['id'],
            'name' => $category['name'],
            'title' => $category['title'] ?? $category['name'],
            'description' => $category['description'] ?? null,
            'slug' => $category['slug'],
            'path' => $category['path'],
            'href' => $category['href'],
            'breadcrumbs' => $category['breadcrumbs'] ?? [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function categoryData(): array
    {
        if ($this->resource instanceof Category) {
            return $this->categories->categoryData($this->resource) ?? [];
        }

        return $this->resource;
    }
}
