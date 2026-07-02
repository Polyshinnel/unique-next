<?php

namespace Tests\Unit\Catalog\Support;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Support\CategoryTreeBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CategoryTreeBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_paths_hrefs_levels_breadcrumbs_and_tree(): void
    {
        $metal = Category::query()->create([
            'name' => 'Металлообработка',
            'slug' => 'metalloobrabotka',
        ]);
        $lathes = Category::query()->create([
            'name' => 'Токарные станки',
            'slug' => 'tokarnye-stanki',
            'parent_id' => $metal->id,
            'title' => 'Токарные станки',
            'description' => 'Описание токарных станков',
            'og_image' => 'catalog/lathes.jpg',
        ]);
        $analogues = Category::query()->create([
            'name' => '16К20 и аналоги',
            'slug' => '16k20-i-analogi',
            'parent_id' => $lathes->id,
        ]);

        $builder = new CategoryTreeBuilder;

        self::assertSame(['metalloobrabotka'], $builder->path($metal));
        self::assertSame(['metalloobrabotka', 'tokarnye-stanki'], $builder->path($lathes));
        self::assertSame('metalloobrabotka/tokarnye-stanki', $builder->pathString($lathes));
        self::assertSame('/catalog/metalloobrabotka/tokarnye-stanki', $builder->href($lathes));
        self::assertSame(2, $builder->level($analogues));

        self::assertSame([
            [
                'id' => $metal->id,
                'name' => 'Металлообработка',
                'slug' => 'metalloobrabotka',
                'path' => ['metalloobrabotka'],
                'pathString' => 'metalloobrabotka',
                'href' => '/catalog/metalloobrabotka',
                'level' => 0,
            ],
            [
                'id' => $lathes->id,
                'name' => 'Токарные станки',
                'slug' => 'tokarnye-stanki',
                'path' => ['metalloobrabotka', 'tokarnye-stanki'],
                'pathString' => 'metalloobrabotka/tokarnye-stanki',
                'href' => '/catalog/metalloobrabotka/tokarnye-stanki',
                'level' => 1,
            ],
        ], $builder->breadcrumbs($analogues));

        $tree = $builder->tree();

        self::assertCount(1, $tree);
        self::assertSame($metal->id, $tree[0]['id']);
        self::assertSame($lathes->id, $tree[0]['children'][0]['id']);
        self::assertSame($analogues->id, $tree[0]['children'][0]['children'][0]['id']);
        self::assertSame('/catalog/metalloobrabotka/tokarnye-stanki/16k20-i-analogi', $tree[0]['children'][0]['children'][0]['href']);
    }

    public function test_it_resolves_only_full_category_paths(): void
    {
        $root = Category::query()->create([
            'name' => 'Корневая',
            'slug' => 'root',
        ]);
        $child = Category::query()->create([
            'name' => 'Дочерняя',
            'slug' => 'child',
            'parent_id' => $root->id,
        ]);

        $builder = new CategoryTreeBuilder;

        self::assertNull($builder->resolvePath(null));
        self::assertNull($builder->resolvePath(''));
        self::assertNull($builder->resolvePath('/'));
        self::assertNull($builder->resolvePath('child'));
        self::assertNull($builder->resolvePath('missing/path'));
        self::assertTrue($root->is($builder->resolvePath('/root/')));
        self::assertTrue($child->is($builder->resolvePath('/root/child/')));
    }

    public function test_descendant_ids_include_current_category_and_all_nested_children(): void
    {
        $root = Category::query()->create([
            'name' => 'A',
            'slug' => 'a',
        ]);
        $middle = Category::query()->create([
            'name' => 'B',
            'slug' => 'b',
            'parent_id' => $root->id,
        ]);
        $leaf = Category::query()->create([
            'name' => 'C',
            'slug' => 'c',
            'parent_id' => $middle->id,
        ]);
        $sibling = Category::query()->create([
            'name' => 'D',
            'slug' => 'd',
        ]);

        $builder = new CategoryTreeBuilder;

        self::assertEqualsCanonicalizing([$root->id, $middle->id, $leaf->id], $builder->descendantIds($root));
        self::assertSame([$leaf->id], $builder->descendantIds($leaf));
        self::assertSame([$sibling->id], $builder->descendantIds($sibling->id));
        self::assertSame([], $builder->descendantIds(999999));
    }

    public function test_category_data_returns_api_ready_category_without_children(): void
    {
        $root = Category::query()->create([
            'name' => 'Фрезерные станки',
            'slug' => 'frezernye-stanki',
        ]);

        $builder = new CategoryTreeBuilder;

        self::assertNull($builder->categoryData(null));
        self::assertSame([
            'id' => $root->id,
            'name' => 'Фрезерные станки',
            'slug' => 'frezernye-stanki',
            'title' => null,
            'description' => null,
            'og_image' => null,
            'path' => ['frezernye-stanki'],
            'pathString' => 'frezernye-stanki',
            'href' => '/catalog/frezernye-stanki',
            'level' => 0,
            'breadcrumbs' => [],
        ], $builder->categoryData($root));
    }
}
