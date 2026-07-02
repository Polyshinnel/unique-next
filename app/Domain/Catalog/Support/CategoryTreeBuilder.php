<?php

namespace App\Domain\Catalog\Support;

use App\Domain\Catalog\Models\Category;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

final class CategoryTreeBuilder
{
    /** @var EloquentCollection<int, Category>|null */
    private ?EloquentCollection $categories = null;

    /** @var array<int, Category> */
    private array $categoriesById = [];

    /** @var array<int|null, list<Category>> */
    private array $childrenByParentId = [];

    /** @var array<int, array{path: list<string>, pathString: string, href: string, level: int, breadcrumbs: list<array<string, mixed>>}> */
    private array $metadataById = [];

    /** @var array<string, int> */
    private array $categoryIdByPath = [];

    /** @var list<array<string, mixed>>|null */
    private ?array $tree = null;

    /**
     * @return list<array<string, mixed>>
     */
    public function tree(): array
    {
        $this->ensureBuilt();

        return $this->tree ?? [];
    }

    public function resolvePath(?string $path): ?Category
    {
        $this->ensureBuilt();

        $path = $this->normalizePath($path);

        if ($path === null) {
            return null;
        }

        $categoryId = $this->categoryIdByPath[$path] ?? null;

        return $categoryId === null ? null : $this->categoriesById[$categoryId];
    }

    /**
     * @return list<int>
     */
    public function descendantIds(Category|int $category): array
    {
        $this->ensureBuilt();

        $categoryId = $category instanceof Category ? (int) $category->getKey() : $category;

        if (! isset($this->categoriesById[$categoryId])) {
            return [];
        }

        $ids = [];
        $stack = [$categoryId];

        while ($stack !== []) {
            $currentId = array_pop($stack);
            $ids[] = $currentId;

            foreach ($this->childrenByParentId[$currentId] ?? [] as $child) {
                $stack[] = (int) $child->getKey();
            }
        }

        return $ids;
    }

    /**
     * @return list<string>
     */
    public function path(Category|int $category): array
    {
        return $this->metadata($category)['path'];
    }

    public function pathString(Category|int $category): string
    {
        return $this->metadata($category)['pathString'];
    }

    public function href(Category|int $category): string
    {
        return $this->metadata($category)['href'];
    }

    public function level(Category|int $category): int
    {
        return $this->metadata($category)['level'];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function breadcrumbs(Category|int $category): array
    {
        return $this->metadata($category)['breadcrumbs'];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function categoryData(?Category $category): ?array
    {
        if ($category === null) {
            return null;
        }

        $this->ensureBuilt();

        return $this->categoryNode($category, includeChildren: false);
    }

    /**
     * @return array{path: list<string>, pathString: string, href: string, level: int, breadcrumbs: list<array<string, mixed>>}
     */
    private function metadata(Category|int $category): array
    {
        $categoryId = $category instanceof Category ? (int) $category->getKey() : $category;

        if ($this->metadataById === [] || ! isset($this->categoriesById[$categoryId])) {
            $this->ensureBuilt();
        }

        if (! isset($this->metadataById[$categoryId])) {
            throw new \InvalidArgumentException("Category [{$categoryId}] is not loaded in the category tree.");
        }

        return $this->metadataById[$categoryId];
    }

    private function ensureBuilt(): void
    {
        if ($this->tree !== null) {
            return;
        }

        $this->loadCategories();
        $this->buildMetadata();

        $this->tree = array_map(
            fn (Category $category): array => $this->categoryNode($category),
            $this->rootCategories(),
        );
    }

    private function loadCategories(): void
    {
        if ($this->categories !== null) {
            return;
        }

        $this->categories = Category::query()
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        foreach ($this->categories as $category) {
            $categoryId = (int) $category->getKey();
            $parentId = $category->parent_id === null ? null : (int) $category->parent_id;

            $this->categoriesById[$categoryId] = $category;
            $this->childrenByParentId[$parentId][] = $category;
        }
    }

    private function buildMetadata(): void
    {
        foreach ($this->rootCategories() as $category) {
            $this->buildCategoryMetadata($category, [], []);
        }
    }

    /**
     * @param  list<string>  $parentPath
     * @param  list<array<string, mixed>>  $parentBreadcrumbs
     */
    private function buildCategoryMetadata(Category $category, array $parentPath, array $parentBreadcrumbs): void
    {
        $path = [...$parentPath, $category->slug];
        $pathString = implode('/', $path);
        $categoryId = (int) $category->getKey();
        $level = count($parentPath);

        $this->metadataById[$categoryId] = [
            'path' => $path,
            'pathString' => $pathString,
            'href' => "/catalog/{$pathString}",
            'level' => $level,
            'breadcrumbs' => $parentBreadcrumbs,
        ];
        $this->categoryIdByPath[$pathString] = $categoryId;

        $breadcrumbs = [...$parentBreadcrumbs, $this->categorySummary($category)];

        foreach ($this->childrenByParentId[$categoryId] ?? [] as $child) {
            $this->buildCategoryMetadata($child, $path, $breadcrumbs);
        }
    }

    /**
     * @return list<Category>
     */
    private function rootCategories(): array
    {
        $this->loadCategories();

        return $this->categories
            ?->filter(fn (Category $category): bool => $category->parent_id === null || ! isset($this->categoriesById[(int) $category->parent_id]))
            ->values()
            ->all() ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    private function categoryNode(Category $category, bool $includeChildren = true): array
    {
        $metadata = $this->metadata($category);

        $node = [
            'id' => (int) $category->getKey(),
            'name' => $category->name,
            'slug' => $category->slug,
            'title' => $category->title,
            'description' => $category->description,
            'og_image' => $category->og_image,
            'path' => $metadata['path'],
            'pathString' => $metadata['pathString'],
            'href' => $metadata['href'],
            'level' => $metadata['level'],
            'breadcrumbs' => $metadata['breadcrumbs'],
        ];

        if ($includeChildren) {
            $node['children'] = array_map(
                fn (Category $child): array => $this->categoryNode($child),
                $this->childrenByParentId[(int) $category->getKey()] ?? [],
            );
        }

        return $node;
    }

    /**
     * @return array<string, mixed>
     */
    private function categorySummary(Category $category): array
    {
        $metadata = $this->metadata($category);

        return [
            'id' => (int) $category->getKey(),
            'name' => $category->name,
            'slug' => $category->slug,
            'path' => $metadata['path'],
            'pathString' => $metadata['pathString'],
            'href' => $metadata['href'],
            'level' => $metadata['level'],
        ];
    }

    private function normalizePath(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $path = trim($path);
        $path = trim($path, '/');

        if ($path === '') {
            return null;
        }

        return implode('/', array_values(array_filter(explode('/', $path), fn (string $segment): bool => $segment !== '')));
    }
}
