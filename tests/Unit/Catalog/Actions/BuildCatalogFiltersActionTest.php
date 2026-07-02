<?php

namespace Tests\Unit\Catalog\Actions;

use App\Domain\Catalog\Actions\BuildCatalogFiltersAction;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\EquipmentAvailability;
use App\Domain\Catalog\Models\EquipmentState;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductStatus;
use App\Domain\Catalog\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BuildCatalogFiltersActionTest extends TestCase
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

    public function test_region_counters_use_distinct_products_and_ignore_active_region_only(): void
    {
        $product = $this->product([
            'category_id' => $this->childCategory->id,
            'equipment_state_id' => $this->usedState->id,
        ], [$this->samara->id, $this->tula->id]);
        $this->product([
            'category_id' => $this->childCategory->id,
            'equipment_state_id' => $this->newState->id,
        ], [$this->samara->id]);
        $this->product(['published_at' => null], [$this->samara->id]);
        $this->product(['product_status_id' => $this->hiddenStatus->id], [$this->samara->id]);

        $filters = (new BuildCatalogFiltersAction)->execute($this->rootCategory, [
            'region' => $this->samara->id,
            'state' => $this->usedState->id,
            'sort' => 'price_desc',
            'search' => 'Станок',
        ]);

        self::assertSame(1, $this->option($filters['regions'], $this->samara->id)['count']);
        self::assertSame(1, $this->option($filters['regions'], $this->tula->id)['count']);
        self::assertSame(
            '/catalog/tokarnye-stanki?region='.$this->tula->id.'&state='.$this->usedState->id.'&search=%D0%A1%D1%82%D0%B0%D0%BD%D0%BE%D0%BA&sort=price_desc',
            $this->option($filters['regions'], $this->tula->id)['href'],
        );
    }

    public function test_category_counts_roll_up_children_and_category_hrefs_keep_other_filters(): void
    {
        $this->product(['category_id' => $this->rootCategory->id], [$this->samara->id]);
        $this->product(['category_id' => $this->childCategory->id], [$this->samara->id]);
        $this->product(['category_id' => $this->childCategory->id], [$this->tula->id]);
        Category::query()->create(['name' => 'Пустая категория', 'slug' => 'empty-category']);
        Category::query()->create([
            'name' => 'Пустой подраздел',
            'slug' => 'empty-child',
            'parent_id' => $this->rootCategory->id,
        ]);

        $filters = (new BuildCatalogFiltersAction)->execute($this->childCategory, [
            'region' => $this->samara->id,
            'availability' => $this->available->id,
            'sort' => 'default',
        ]);

        $root = $filters['categories'][0];

        self::assertSame(2, $root['count']);
        self::assertCount(1, $filters['categories']);
        self::assertCount(1, $root['children']);
        self::assertSame(1, $root['children'][0]['count']);
        self::assertSame(
            '/catalog/tokarnye-stanki?region='.$this->samara->id.'&availability='.$this->available->id,
            $root['href'],
        );
    }

    public function test_reference_counters_ignore_only_their_own_active_filter(): void
    {
        $this->product(['equipment_availability_id' => $this->available->id, 'equipment_state_id' => $this->usedState->id]);
        $this->product(['equipment_availability_id' => $this->reserved->id, 'equipment_state_id' => $this->usedState->id]);
        $this->product(['equipment_availability_id' => $this->available->id, 'equipment_state_id' => $this->newState->id]);

        $filters = (new BuildCatalogFiltersAction)->execute(null, [
            'availability' => $this->available->id,
            'state' => $this->usedState->id,
        ]);

        self::assertSame(1, $this->option($filters['availabilities'], $this->available->id)['count']);
        self::assertSame(1, $this->option($filters['availabilities'], $this->reserved->id)['count']);
        self::assertSame(1, $this->option($filters['states'], $this->usedState->id)['count']);
        self::assertSame(1, $this->option($filters['states'], $this->newState->id)['count']);
        self::assertSame('/catalog?availability='.$this->reserved->id.'&state='.$this->usedState->id, $this->option($filters['availabilities'], $this->reserved->id)['href']);
    }

    /**
     * @param  list<array<string, mixed>>  $options
     * @return array<string, mixed>
     */
    private function option(array $options, int $id): array
    {
        foreach ($options as $option) {
            if ($option['id'] === $id) {
                return $option;
            }
        }

        self::fail("Option [{$id}] was not found.");
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
            'published_at' => array_key_exists('published_at', $attributes) ? $attributes['published_at'] : now(),
        ]);

        $product->regions()->sync($regionIds ?? [$this->samara->id]);

        return $product;
    }
}
