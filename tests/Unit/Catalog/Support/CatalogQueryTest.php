<?php

namespace Tests\Unit\Catalog\Support;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\EquipmentAvailability;
use App\Domain\Catalog\Models\EquipmentState;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductStatus;
use App\Domain\Catalog\Models\Region;
use App\Domain\Catalog\Models\Tag;
use App\Domain\Catalog\Support\CatalogQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CatalogQueryTest extends TestCase
{
    use RefreshDatabase;

    private ProductStatus $saleStatus;

    private ProductStatus $hiddenStatus;

    private Category $rootCategory;

    private Category $childCategory;

    private EquipmentAvailability $available;

    private EquipmentAvailability $reserved;

    private EquipmentState $usedState;

    private EquipmentState $newState;

    private Region $samara;

    private Region $tula;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleStatus = ProductStatus::query()->create(['name' => 'В продаже']);
        $this->hiddenStatus = ProductStatus::query()->create(['name' => 'Снят с продажи']);
        $this->rootCategory = Category::query()->create(['name' => 'Токарные станки', 'slug' => 'tokarnye-stanki']);
        $this->childCategory = Category::query()->create([
            'name' => '16К20 и аналоги',
            'slug' => '16k20-i-analogi',
            'parent_id' => $this->rootCategory->id,
        ]);
        $this->available = EquipmentAvailability::query()->create(['name' => 'В наличии']);
        $this->reserved = EquipmentAvailability::query()->create(['name' => 'В резерве']);
        $this->usedState = EquipmentState::query()->create(['name' => 'Б.У']);
        $this->newState = EquipmentState::query()->create(['name' => 'Новое']);
        $this->samara = Region::query()->create(['name' => 'Самарская область']);
        $this->tula = Region::query()->create(['name' => 'Тульская область']);
    }

    public function test_list_query_uses_public_rules_eager_loads_and_default_sort(): void
    {
        $first = $this->product(['title' => 'Первый']);
        $second = $this->product(['title' => 'Второй']);
        $this->product(['title' => 'Черновик', 'published_at' => null]);
        $this->product(['title' => 'Скрытый статус', 'product_status_id' => $this->hiddenStatus->id]);
        $this->product(['title' => 'Удаленный'])->delete();

        $query = (new CatalogQuery)->listQuery();

        self::assertSame([
            'category',
            'mainImage',
            'productStatus',
            'equipmentAvailability',
            'equipmentState',
            'regions',
        ], array_keys($query->getEagerLoads()));

        self::assertSame([$second->id, $first->id], $query->pluck('id')->all());
    }

    public function test_it_applies_category_region_availability_and_state_filters(): void
    {
        $matching = $this->product([
            'category_id' => $this->childCategory->id,
            'equipment_availability_id' => $this->available->id,
            'equipment_state_id' => $this->usedState->id,
        ], [$this->samara->id]);
        $this->product(['category_id' => $this->rootCategory->id], [$this->tula->id]);
        $this->product(['category_id' => null], [$this->samara->id]);
        $this->product(['equipment_availability_id' => $this->reserved->id], [$this->samara->id]);
        $this->product(['equipment_state_id' => $this->newState->id], [$this->samara->id]);

        $ids = (new CatalogQuery)->listQuery($this->rootCategory, [
            'region' => $this->samara->id,
            'availability' => $this->available->id,
            'state' => $this->usedState->id,
        ])->pluck('id')->all();

        self::assertSame([$matching->id], $ids);
    }

    public function test_region_filter_uses_regions_relation_without_product_region_fallback(): void
    {
        $byPivot = $this->product(['region_id' => $this->tula->id], [$this->samara->id]);
        $this->product(['region_id' => $this->samara->id], [$this->tula->id]);

        $ids = (new CatalogQuery)->listQuery(filters: [
            'region' => $this->samara->id,
        ])->pluck('id')->all();

        self::assertSame([$byPivot->id], $ids);
    }

    public function test_search_escapes_like_wildcards_and_searches_tags(): void
    {
        $literalPercent = $this->product(['title' => 'Станок 16%20']);
        $literalUnderscore = $this->product(['name' => 'ABC_123']);
        $byTag = $this->product(['title' => 'Обычный станок']);
        $this->product(['title' => 'Станок 16020']);
        $this->product(['name' => 'ABCX123']);

        $tag = Tag::query()->create(['name' => 'редкий%тег']);
        $byTag->tags()->attach($tag->id);

        $catalog = new CatalogQuery;

        self::assertSame([$literalPercent->id], $catalog->listQuery(filters: ['search' => '16%'])->pluck('id')->all());
        self::assertSame([$literalUnderscore->id], $catalog->listQuery(filters: ['search' => 'ABC_'])->pluck('id')->all());
        self::assertSame([$byTag->id], $catalog->listQuery(filters: ['search' => 'редкий%'])->pluck('id')->all());
    }

    public function test_price_sort_keeps_unpublished_prices_last(): void
    {
        $hiddenHighPrice = $this->product(['price' => 999999, 'show_price' => false]);
        $withoutPrice = $this->product(['price' => null, 'show_price' => true]);
        $cheap = $this->product(['price' => 100, 'show_price' => true]);
        $expensive = $this->product(['price' => 300, 'show_price' => true]);

        $catalog = new CatalogQuery;

        self::assertSame(
            [$expensive->id, $cheap->id, $hiddenHighPrice->id, $withoutPrice->id],
            $catalog->listQuery(filters: ['sort' => CatalogQuery::SORT_PRICE_DESC])->pluck('id')->all(),
        );
        self::assertSame(
            [$cheap->id, $expensive->id, $withoutPrice->id, $hiddenHighPrice->id],
            $catalog->listQuery(filters: ['sort' => CatalogQuery::SORT_PRICE_ASC])->pluck('id')->all(),
        );
    }

    public function test_filters_can_exclude_one_active_filter_for_counters(): void
    {
        $samaraProduct = $this->product([], [$this->samara->id]);
        $tulaProduct = $this->product([], [$this->tula->id]);
        $this->product(['equipment_state_id' => $this->newState->id], [$this->samara->id]);

        $catalog = new CatalogQuery;

        self::assertSame(
            [$samaraProduct->id],
            $catalog->filteredQuery(filters: [
                'region' => $this->samara->id,
                'state' => $this->usedState->id,
            ])->orderBy('products.id')->pluck('id')->all(),
        );
        self::assertSame(
            [$samaraProduct->id, $tulaProduct->id],
            $catalog->filteredQuery(filters: [
                'region' => $this->samara->id,
                'state' => $this->usedState->id,
            ], except: CatalogQuery::FILTER_REGION)->orderBy('products.id')->pluck('id')->all(),
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<int>|null  $regionIds
     */
    private function product(array $attributes = [], ?array $regionIds = null): Product
    {
        $product = Product::query()->create([
            'name' => $attributes['name'] ?? $attributes['title'] ?? 'Станок',
            'title' => $attributes['title'] ?? $attributes['name'] ?? 'Станок',
            'sku' => $attributes['sku'] ?? null,
            'category_id' => array_key_exists('category_id', $attributes) ? $attributes['category_id'] : $this->rootCategory->id,
            'equipment_availability_id' => array_key_exists('equipment_availability_id', $attributes) ? $attributes['equipment_availability_id'] : $this->available->id,
            'equipment_state_id' => array_key_exists('equipment_state_id', $attributes) ? $attributes['equipment_state_id'] : $this->usedState->id,
            'product_status_id' => array_key_exists('product_status_id', $attributes) ? $attributes['product_status_id'] : $this->saleStatus->id,
            'price' => $attributes['price'] ?? null,
            'show_price' => $attributes['show_price'] ?? true,
            'region_id' => array_key_exists('region_id', $attributes) ? $attributes['region_id'] : null,
            'published_at' => array_key_exists('published_at', $attributes) ? $attributes['published_at'] : now(),
        ]);

        $product->regions()->sync($regionIds ?? [$this->samara->id]);

        return $product;
    }
}
