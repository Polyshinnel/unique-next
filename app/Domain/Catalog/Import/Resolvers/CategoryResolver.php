<?php

namespace App\Domain\Catalog\Import\Resolvers;

use App\Domain\Catalog\Import\Data\CategoryData;
use App\Domain\Catalog\Models\Category;
use Illuminate\Support\Str;

final class CategoryResolver
{
    /**
     * @param iterable<CategoryData> $categories
     */
    public function upsertMany(iterable $categories): void
    {
        foreach ($categories as $categoryData) {
            $category = Category::firstOrNew([
                'external_id' => $categoryData->externalId,
            ]);

            if (! $category->exists) {
                $category->fill([
                    'name' => $categoryData->name,
                    'slug' => $this->makeUniqueSlug($categoryData->name),
                ])->save();

                continue;
            }

            if ($category->name !== $categoryData->name) {
                $category->update([
                    'name' => $categoryData->name,
                ]);
            }
        }
    }

    /**
     * @param iterable<CategoryData> $categories
     */
    public function linkParents(iterable $categories): void
    {
        foreach ($categories as $categoryData) {
            $category = $this->resolveByExternalId($categoryData->externalId);

            if ($category === null) {
                continue;
            }

            $parentId = $categoryData->parentExternalId === null
                ? null
                : $this->resolveByExternalId($categoryData->parentExternalId)?->getKey();

            if ($category->parent_id !== $parentId) {
                $category->update([
                    'parent_id' => $parentId,
                ]);
            }
        }
    }

    public function resolveByExternalId(int $externalId): ?Category
    {
        return Category::query()
            ->where('external_id', $externalId)
            ->first();
    }

    private function makeUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name, '-', 'ru');
        $rootSlug = $baseSlug !== '' ? $baseSlug : 'category';
        $slug = $rootSlug;
        $suffix = 2;

        while (Category::query()->where('slug', $slug)->exists()) {
            $slug = "{$rootSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
