<?php

namespace Tests\Feature\Catalog;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\CheckStatus;
use App\Domain\Catalog\Models\DismantleStatus;
use App\Domain\Catalog\Models\EquipmentAvailability;
use App\Domain\Catalog\Models\EquipmentState;
use App\Domain\Catalog\Models\Manager;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductAdditionalInfo;
use App\Domain\Catalog\Models\ProductCheck;
use App\Domain\Catalog\Models\ProductComplectation;
use App\Domain\Catalog\Models\ProductDismantling;
use App\Domain\Catalog\Models\ProductImage;
use App\Domain\Catalog\Models\ProductLoading;
use App\Domain\Catalog\Models\ProductMainCharacteristic;
use App\Domain\Catalog\Models\ProductMainInfo;
use App\Domain\Catalog\Models\ProductStatus;
use App\Domain\Catalog\Models\ProductTechnicalCharacteristic;
use App\Domain\Catalog\Models\Region;
use App\Domain\Catalog\Models\ShipmentStatus;
use App\Domain\Catalog\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CatalogPageControllerTest extends TestCase
{
    use RefreshDatabase;

    private ProductStatus $saleStatus;

    private ProductStatus $reserveStatus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleStatus = ProductStatus::query()->create(['name' => 'В продаже']);
        $this->reserveStatus = ProductStatus::query()->create(['name' => 'Резерв']);
    }

    public function test_catalog_page_returns_products_filters_and_pagination(): void
    {
        $category = Category::query()->create(['name' => 'Токарные станки', 'slug' => 'tokarnye-stanki']);
        $region = Region::query()->create(['name' => 'Самарская область']);
        $availability = EquipmentAvailability::query()->create(['name' => 'В наличии']);
        $state = EquipmentState::query()->create(['name' => 'Б.У']);
        $product = Product::query()->create([
            'name' => 'Станок',
            'title' => 'Токарный станок',
            'category_id' => $category->id,
            'equipment_availability_id' => $availability->id,
            'equipment_state_id' => $state->id,
            'product_status_id' => $this->saleStatus->id,
            'show_price' => true,
            'published_at' => now(),
        ]);
        $product->regions()->attach($region->id);

        $response = $this->getJson('/api/catalog/page?category_path=tokarnye-stanki&region='.$region->id);

        $response
            ->assertOk()
            ->assertJsonPath('category.id', $category->id)
            ->assertJsonPath('filters.regions.0.count', 1)
            ->assertJsonPath('filters.categories.0.count', 1)
            ->assertJsonPath('filters.availabilities.0.count', 1)
            ->assertJsonPath('filters.states.0.count', 1)
            ->assertJsonPath('products.0.id', $product->id)
            ->assertJsonPath('pagination.perPage', 12);
    }

    public function test_catalog_page_returns_404_for_unknown_category_path(): void
    {
        $this->getJson('/api/catalog/page?category_path=unknown')->assertNotFound();
    }

    public function test_category_by_path_returns_category_seo_data(): void
    {
        $root = Category::query()->create(['name' => 'Металлообработка', 'slug' => 'metalloobrabotka']);
        $category = Category::query()->create([
            'name' => 'Токарные станки',
            'title' => 'Купить токарные станки',
            'description' => 'Описание категории',
            'slug' => 'tokarnye-stanki',
            'parent_id' => $root->id,
        ]);

        $this->getJson('/api/catalog/categories/by-path?path='.urlencode('/metalloobrabotka/tokarnye-stanki/'))
            ->assertOk()
            ->assertJsonPath('id', $category->id)
            ->assertJsonPath('name', 'Токарные станки')
            ->assertJsonPath('title', 'Купить токарные станки')
            ->assertJsonPath('description', 'Описание категории')
            ->assertJsonPath('slug', 'tokarnye-stanki')
            ->assertJsonPath('path', ['metalloobrabotka', 'tokarnye-stanki'])
            ->assertJsonPath('href', '/catalog/metalloobrabotka/tokarnye-stanki')
            ->assertJsonPath('breadcrumbs.0.href', '/catalog/metalloobrabotka');
    }

    public function test_category_by_path_returns_404_for_unknown_path(): void
    {
        $this->getJson('/api/catalog/categories/by-path?path=unknown')->assertNotFound();
    }

    public function test_category_by_path_returns_404_for_empty_path(): void
    {
        $this->getJson('/api/catalog/categories/by-path?path=/%20/')->assertNotFound();
    }

    public function test_product_detail_returns_public_product_shape(): void
    {
        $root = Category::query()->create(['name' => 'Металлообработка', 'slug' => 'metalloobrabotka']);
        $category = Category::query()->create([
            'name' => 'Токарные станки',
            'slug' => 'tokarnye-stanki',
            'parent_id' => $root->id,
        ]);
        $region = Region::query()->create(['name' => 'Самарская область']);
        $availability = EquipmentAvailability::query()->create(['name' => 'В наличии', 'color' => '#22c55e']);
        $state = EquipmentState::query()->create(['name' => 'Б.У']);
        $manager = Manager::query()->create(['name' => 'Иван', 'phone' => '+79990000000']);
        $tag = Tag::query()->create(['name' => '16К20']);
        $product = Product::query()->create([
            'name' => 'Станок',
            'title' => 'Токарный станок 16К20',
            'sku' => 'UNQ-16K20',
            'description' => 'Готов к отгрузке.',
            'category_id' => $category->id,
            'manager_id' => $manager->id,
            'equipment_availability_id' => $availability->id,
            'equipment_state_id' => $state->id,
            'product_status_id' => $this->saleStatus->id,
            'price' => 95000,
            'show_price' => true,
            'published_at' => now(),
        ]);
        $product->regions()->attach($region->id);
        $product->tags()->attach($tag->id);
        ProductImage::query()->create([
            'product_id' => $product->id,
            'file_name' => 'main.jpg',
            'file_path' => 'products/16k20/main.jpg',
            'is_main' => true,
            'sort_order' => 0,
        ]);
        ProductMainCharacteristic::query()->create([
            'product_id' => $product->id,
            'content' => '<p>РМЦ: 1000 мм</p><p>Диаметр<strong>:</strong> 400 мм</p>',
        ]);
        ProductMainInfo::query()->create([
            'product_id' => $product->id,
            'content' => '<p>Основная информация</p>',
        ]);
        ProductComplectation::query()->create([
            'product_id' => $product->id,
            'content' => '<p>Комплектация</p>',
        ]);
        ProductTechnicalCharacteristic::query()->create([
            'product_id' => $product->id,
            'content' => '<p>Технические характеристики</p>',
        ]);
        ProductAdditionalInfo::query()->create([
            'product_id' => $product->id,
            'content' => '<p>Дополнительная информация</p>',
        ]);
        $checkStatus = CheckStatus::query()->create(['name' => 'Проверен']);
        $dismantleStatus = DismantleStatus::query()->create(['name' => 'Демонтирован']);
        $shipmentStatus = ShipmentStatus::query()->create(['name' => 'Готов к погрузке']);
        ProductCheck::query()->create([
            'product_id' => $product->id,
            'check_status_id' => $checkStatus->id,
            'comment' => '<p>Комментарий проверки</p>',
        ]);
        ProductDismantling::query()->create([
            'product_id' => $product->id,
            'dismantle_status_id' => $dismantleStatus->id,
            'comment' => '<p>Комментарий демонтажа</p>',
        ]);
        ProductLoading::query()->create([
            'product_id' => $product->id,
            'shipment_status_id' => $shipmentStatus->id,
            'comment' => '<p>Комментарий погрузки</p>',
        ]);

        $this->getJson('/api/catalog/products/'.$product->id)
            ->assertOk()
            ->assertJsonPath('id', $product->id)
            ->assertJsonPath('title', 'Токарный станок 16К20')
            ->assertJsonPath('canonicalHref', '/catalog/metalloobrabotka/tokarnye-stanki/unq-16k20')
            ->assertJsonPath('href', '/catalog/metalloobrabotka/tokarnye-stanki/unq-16k20')
            ->assertJsonPath('category.href', '/catalog/metalloobrabotka/tokarnye-stanki')
            ->assertJsonPath('category.breadcrumbs.0.href', '/catalog/metalloobrabotka')
            ->assertJsonPath('region.name', 'Самарская область')
            ->assertJsonPath('price.amount', '95000.00')
            ->assertJsonPath('price.isPublished', true)
            ->assertJsonPath('availability.name', 'В наличии')
            ->assertJsonPath('state.name', 'Б.У')
            ->assertJsonPath('manager.name', 'Иван')
            ->assertJsonPath('images.0', '/storage/products/16k20/main.jpg')
            ->assertJsonPath('tags.0', '16К20')
            ->assertJsonPath('characteristicBlocks.0.title', 'Основные характеристики')
            ->assertJsonPath('characteristicBlocks.0.contentHtml', '<p>РМЦ: 1000 мм</p><p>Диаметр<strong>:</strong> 400 мм</p>')
            ->assertJsonPath('characteristicBlocks.1.title', 'Основная информация')
            ->assertJsonPath('characteristicBlocks.2.title', 'Комплектация')
            ->assertJsonPath('characteristicBlocks.3.title', 'Технические характеристики')
            ->assertJsonPath('characteristicBlocks.4.title', 'Условия продажи')
            ->assertJsonPath('characteristicBlocks.4.contentHtml', '<p class="product-sale-price"><strong>Цена:</strong> 95 000 ₽</p>')
            ->assertJsonPath('characteristicBlocks.5.title', 'Проверка')
            ->assertJsonPath('characteristicBlocks.5.contentHtml', '<p><strong>Статус:</strong> Проверен</p><div><strong>Комментарий:</strong></div><p>Комментарий проверки</p>')
            ->assertJsonPath('characteristicBlocks.6.title', 'Демонтаж')
            ->assertJsonPath('characteristicBlocks.6.contentHtml', '<p><strong>Статус:</strong> Демонтирован</p><div><strong>Комментарий:</strong></div><p>Комментарий демонтажа</p>')
            ->assertJsonPath('characteristicBlocks.7.title', 'Погрузка')
            ->assertJsonPath('characteristicBlocks.7.contentHtml', '<p><strong>Статус:</strong> Готов к погрузке</p><div><strong>Комментарий:</strong></div><p>Комментарий погрузки</p>')
            ->assertJsonPath('characteristicBlocks.8.title', 'Дополнительная информация')
            ->assertJsonCount(9, 'characteristicBlocks');
    }

    public function test_product_detail_can_be_opened_by_slug(): void
    {
        $category = Category::query()->create(['name' => 'Токарные станки', 'slug' => 'tokarnye-stanki']);
        $product = $this->product([
            'title' => 'Токарный станок 16К20',
            'sku' => 'SKU-16K20',
            'category_id' => $category->id,
        ]);

        $this->getJson('/api/catalog/products/sku-16k20')
            ->assertOk()
            ->assertJsonPath('id', $product->id)
            ->assertJsonPath('href', '/catalog/tokarnye-stanki/sku-16k20')
            ->assertJsonPath('canonicalHref', '/catalog/tokarnye-stanki/sku-16k20');
    }

    public function test_product_detail_falls_back_to_id_when_slug_is_missing(): void
    {
        $category = Category::query()->create(['name' => 'Токарные станки', 'slug' => 'tokarnye-stanki']);
        $product = $this->product([
            'title' => 'Токарный станок 1А616',
            'category_id' => $category->id,
        ]);
        $product->forceFill(['slug' => null])->save();

        $this->getJson('/api/catalog/products/'.$product->id)
            ->assertOk()
            ->assertJsonPath('id', $product->id)
            ->assertJsonPath('href', '/catalog/tokarnye-stanki/'.$product->id)
            ->assertJsonPath('canonicalHref', '/catalog/tokarnye-stanki/'.$product->id);
    }

    public function test_product_detail_returns_404_for_unpublished_or_deleted_products(): void
    {
        $hidden = $this->product(['published_at' => null]);
        $deleted = $this->product();
        $deleted->delete();

        $this->getJson('/api/catalog/products/'.$hidden->id)->assertNotFound();
        $this->getJson('/api/catalog/products/'.$deleted->id)->assertNotFound();
    }

    public function test_product_detail_returns_sold_product_with_sold_labels_and_reduced_blocks(): void
    {
        $category = Category::query()->create(['name' => 'Токарные станки', 'slug' => 'tokarnye-stanki']);
        $availability = EquipmentAvailability::query()->create(['name' => 'В наличии']);
        $soldStatus = ProductStatus::query()->create(['name' => 'Продано']);
        $checkStatus = CheckStatus::query()->create(['name' => 'Проверен']);
        $dismantleStatus = DismantleStatus::query()->create(['name' => 'Демонтирован']);
        $shipmentStatus = ShipmentStatus::query()->create(['name' => 'Готов к погрузке']);
        $product = $this->product([
            'title' => 'Проданный станок',
            'category_id' => $category->id,
            'equipment_availability_id' => $availability->id,
            'product_status_id' => $soldStatus->id,
            'price' => 123456,
            'show_price' => true,
        ]);

        ProductMainCharacteristic::query()->create([
            'product_id' => $product->id,
            'content' => '<p>Основные характеристики</p>',
        ]);
        ProductMainInfo::query()->create([
            'product_id' => $product->id,
            'content' => '<p>Основная информация</p>',
        ]);
        ProductComplectation::query()->create([
            'product_id' => $product->id,
            'content' => '<p>Комплектация</p>',
        ]);
        ProductTechnicalCharacteristic::query()->create([
            'product_id' => $product->id,
            'content' => '<p>Технические характеристики</p>',
        ]);
        ProductAdditionalInfo::query()->create([
            'product_id' => $product->id,
            'content' => '<p>Дополнительная информация</p>',
        ]);
        ProductCheck::query()->create([
            'product_id' => $product->id,
            'check_status_id' => $checkStatus->id,
            'comment' => '<p>Комментарий проверки</p>',
        ]);
        ProductDismantling::query()->create([
            'product_id' => $product->id,
            'dismantle_status_id' => $dismantleStatus->id,
            'comment' => '<p>Комментарий демонтажа</p>',
        ]);
        ProductLoading::query()->create([
            'product_id' => $product->id,
            'shipment_status_id' => $shipmentStatus->id,
            'comment' => '<p>Комментарий погрузки</p>',
        ]);

        $this->getJson('/api/catalog/products/'.$product->id)
            ->assertOk()
            ->assertJsonPath('price.amount', null)
            ->assertJsonPath('price.isPublished', false)
            ->assertJsonPath('price.isReserve', false)
            ->assertJsonPath('price.isSold', true)
            ->assertJsonPath('price.label', 'Продано')
            ->assertJsonPath('availability.name', 'В наличии')
            ->assertJsonPath('characteristicBlocks.0.title', 'Основные характеристики')
            ->assertJsonPath('characteristicBlocks.1.title', 'Основная информация')
            ->assertJsonMissingPath('characteristicBlocks.2.title')
            ->assertJsonCount(2, 'characteristicBlocks');
    }

    public function test_product_detail_hides_unpublished_price_amount(): void
    {
        $product = $this->product(['price' => 123456, 'show_price' => false]);

        $this->getJson('/api/catalog/products/'.$product->id)
            ->assertOk()
            ->assertJsonPath('price.amount', null)
            ->assertJsonPath('price.isPublished', false)
            ->assertJsonPath('price.isReserve', false)
            ->assertJsonPath('price.label', 'По запросу');
    }

    public function test_product_detail_returns_reserved_product_with_reserve_price_label(): void
    {
        $product = $this->product([
            'title' => 'Станок в резерве',
            'product_status_id' => $this->reserveStatus->id,
            'price' => 123456,
            'show_price' => true,
        ]);

        $this->getJson('/api/catalog/products/'.$product->id)
            ->assertOk()
            ->assertJsonPath('price.amount', null)
            ->assertJsonPath('price.isPublished', false)
            ->assertJsonPath('price.isReserve', true)
            ->assertJsonPath('price.label', 'Резерв')
            ->assertJsonPath('characteristicBlocks.0.contentHtml', '<p class="product-sale-price"><strong>Цена:</strong> Резерв</p>');
    }

    public function test_catalog_page_uses_fixed_pagination_page_from_query(): void
    {
        $products = collect(range(1, 13))
            ->map(fn (int $number): Product => $this->product(['title' => 'Станок '.$number]));

        $this->getJson('/api/catalog/page')
            ->assertOk()
            ->assertJsonPath('products.0.id', $products->last()->id)
            ->assertJsonPath('pagination.currentPage', 1)
            ->assertJsonPath('pagination.perPage', 12)
            ->assertJsonPath('pagination.total', 13)
            ->assertJsonPath('pagination.totalPages', 2);

        $this->getJson('/api/catalog/page?page=2')
            ->assertOk()
            ->assertJsonPath('products.0.id', $products->first()->id)
            ->assertJsonPath('pagination.currentPage', 2)
            ->assertJsonCount(1, 'products');
    }

    public function test_catalog_page_lists_only_public_products_by_default_sort(): void
    {
        $oldest = $this->product(['title' => 'Старый публичный']);
        $newest = $this->product(['title' => 'Новый публичный']);
        $reserved = $this->product(['title' => 'Зарезервированный', 'product_status_id' => $this->reserveStatus->id]);
        $unpublished = $this->product(['title' => 'Черновик', 'published_at' => null]);
        $hiddenStatus = ProductStatus::query()->create(['name' => 'Снят с продажи']);
        $hidden = $this->product(['title' => 'Скрытый статус', 'product_status_id' => $hiddenStatus->id]);

        $response = $this->getJson('/api/catalog/page')
            ->assertOk()
            ->assertJsonPath('sorting.active', 'default')
            ->assertJsonPath('products.0.id', $reserved->id)
            ->assertJsonPath('products.0.price.isReserve', true)
            ->assertJsonPath('products.0.price.label', 'Резерв')
            ->assertJsonPath('products.1.id', $newest->id)
            ->assertJsonPath('products.2.id', $oldest->id)
            ->assertJsonPath('pagination.total', 3);

        self::assertNotContains($unpublished->id, $this->productIds($response->json('products')));
        self::assertNotContains($hidden->id, $this->productIds($response->json('products')));
    }

    public function test_catalog_page_sorts_by_price_desc_with_unpriced_products_last(): void
    {
        $reserved = $this->product(['title' => 'Резерв', 'price' => 500000, 'show_price' => true, 'product_status_id' => $this->reserveStatus->id]);
        $hiddenHighPrice = $this->product(['title' => 'Скрытая цена', 'price' => 999999, 'show_price' => false]);
        $withoutPrice = $this->product(['title' => 'Без цены', 'price' => null, 'show_price' => true]);
        $cheap = $this->product(['title' => 'Дешевый', 'price' => 100, 'show_price' => true]);
        $expensive = $this->product(['title' => 'Дорогой', 'price' => 300, 'show_price' => true]);

        $this->getJson('/api/catalog/page?sort=price_desc')
            ->assertOk()
            ->assertJsonPath('sorting.active', 'price_desc')
            ->assertJsonPath('products.0.id', $expensive->id)
            ->assertJsonPath('products.1.id', $cheap->id)
            ->assertJsonPath('products.2.id', $hiddenHighPrice->id)
            ->assertJsonPath('products.3.id', $reserved->id)
            ->assertJsonPath('products.4.id', $withoutPrice->id);
    }

    public function test_catalog_page_sorts_by_price_asc_with_unpriced_products_last(): void
    {
        $reserved = $this->product(['title' => 'Резерв', 'price' => 500000, 'show_price' => true, 'product_status_id' => $this->reserveStatus->id]);
        $hiddenHighPrice = $this->product(['title' => 'Скрытая цена', 'price' => 999999, 'show_price' => false]);
        $withoutPrice = $this->product(['title' => 'Без цены', 'price' => null, 'show_price' => true]);
        $cheap = $this->product(['title' => 'Дешевый', 'price' => 100, 'show_price' => true]);
        $expensive = $this->product(['title' => 'Дорогой', 'price' => 300, 'show_price' => true]);

        $response = $this->getJson('/api/catalog/page?sort=price_asc')
            ->assertOk()
            ->assertJsonPath('sorting.active', 'price_asc')
            ->assertJsonPath('products.0.id', $cheap->id)
            ->assertJsonPath('products.1.id', $expensive->id);

        $ids = $this->productIds($response->json('products'));

        self::assertContains($reserved->id, array_slice($ids, 2));
        self::assertContains($hiddenHighPrice->id, array_slice($ids, 2));
        self::assertContains($withoutPrice->id, array_slice($ids, 2));
    }

    public function test_catalog_page_applies_search_to_title_name_sku_and_tags(): void
    {
        $titleMatch = $this->product(['title' => 'Токарный станок 16К20', 'name' => 'Inventory A']);
        $nameMatch = $this->product(['title' => 'Inventory B', 'name' => 'Вертикальный пресс П6320']);
        $skuMatch = $this->product(['title' => 'Inventory C', 'name' => 'Inventory C', 'sku' => 'UNQ-SKU-77']);
        $tagMatch = $this->product(['title' => 'Inventory D', 'name' => 'Inventory D']);
        $ignored = $this->product(['title' => 'Inventory E', 'name' => 'Inventory E', 'sku' => 'NO-MATCH']);
        $tag = Tag::query()->create(['name' => 'Редкая метка']);
        $tagMatch->tags()->attach($tag->id);

        $this->getJson('/api/catalog/page?search='.urlencode('16К20'))
            ->assertOk()
            ->assertJsonPath('products.0.id', $titleMatch->id)
            ->assertJsonCount(1, 'products');

        $this->getJson('/api/catalog/page?search='.urlencode('П6320'))
            ->assertOk()
            ->assertJsonPath('products.0.id', $nameMatch->id)
            ->assertJsonCount(1, 'products');

        $this->getJson('/api/catalog/page?search=SKU-77')
            ->assertOk()
            ->assertJsonPath('products.0.id', $skuMatch->id)
            ->assertJsonCount(1, 'products');

        $this->getJson('/api/catalog/page?search='.urlencode('Редкая'))
            ->assertOk()
            ->assertJsonPath('products.0.id', $tagMatch->id)
            ->assertJsonCount(1, 'products');

        $this->getJson('/api/catalog/page?search=NOPE')
            ->assertOk()
            ->assertJsonCount(0, 'products');

        self::assertNotSame($ignored->id, $tagMatch->id);
    }

    public function test_catalog_page_applies_region_availability_state_and_descendant_category_filters(): void
    {
        $root = Category::query()->create(['name' => 'Металлообработка', 'slug' => 'metalloobrabotka']);
        $child = Category::query()->create(['name' => 'Токарные станки', 'slug' => 'tokarnye-stanki', 'parent_id' => $root->id]);
        $outside = Category::query()->create(['name' => 'Складская техника', 'slug' => 'skladskaya-tehnika']);
        $samara = Region::query()->create(['name' => 'Самарская область']);
        $tula = Region::query()->create(['name' => 'Тульская область']);
        $available = EquipmentAvailability::query()->create(['name' => 'В наличии']);
        $reserved = EquipmentAvailability::query()->create(['name' => 'В резерве']);
        $used = EquipmentState::query()->create(['name' => 'Б.У']);
        $new = EquipmentState::query()->create(['name' => 'Новое']);

        $matching = $this->product([
            'title' => 'Подходящий',
            'category_id' => $child->id,
            'equipment_availability_id' => $available->id,
            'equipment_state_id' => $used->id,
        ], [$samara->id]);
        $this->product([
            'title' => 'Другой регион',
            'category_id' => $child->id,
            'equipment_availability_id' => $available->id,
            'equipment_state_id' => $used->id,
        ], [$tula->id]);
        $this->product([
            'title' => 'Другая доступность',
            'category_id' => $child->id,
            'equipment_availability_id' => $reserved->id,
            'equipment_state_id' => $used->id,
        ], [$samara->id]);
        $this->product([
            'title' => 'Другое состояние',
            'category_id' => $child->id,
            'equipment_availability_id' => $available->id,
            'equipment_state_id' => $new->id,
        ], [$samara->id]);
        $this->product([
            'title' => 'Другая категория',
            'category_id' => $outside->id,
            'equipment_availability_id' => $available->id,
            'equipment_state_id' => $used->id,
        ], [$samara->id]);

        $this->getJson('/api/catalog/page?category_path=metalloobrabotka&region='.$samara->id.'&availability='.$available->id.'&state='.$used->id)
            ->assertOk()
            ->assertJsonPath('products.0.id', $matching->id)
            ->assertJsonCount(1, 'products')
            ->assertJsonPath('pagination.total', 1);
    }

    public function test_catalog_page_filter_counters_ignore_active_region_and_roll_up_category_descendants(): void
    {
        $root = Category::query()->create(['name' => 'Токарные станки', 'slug' => 'tokarnye-stanki']);
        $child = Category::query()->create(['name' => '16К20 и аналоги', 'slug' => '16k20-i-analogi', 'parent_id' => $root->id]);
        $samara = Region::query()->create(['name' => 'Самарская область']);
        $tula = Region::query()->create(['name' => 'Тульская область']);
        $available = EquipmentAvailability::query()->create(['name' => 'В наличии']);
        $used = EquipmentState::query()->create(['name' => 'Б.У']);
        $new = EquipmentState::query()->create(['name' => 'Новое']);

        $this->product([
            'title' => 'Станок основной',
            'category_id' => $child->id,
            'equipment_availability_id' => $available->id,
            'equipment_state_id' => $used->id,
        ], [$samara->id, $tula->id]);
        $this->product([
            'title' => 'Станок другой',
            'category_id' => $child->id,
            'equipment_availability_id' => $available->id,
            'equipment_state_id' => $new->id,
        ], [$samara->id]);

        $response = $this->getJson('/api/catalog/page?category_path=tokarnye-stanki&region='.$samara->id.'&state='.$used->id.'&search='.urlencode('Станок'))
            ->assertOk();

        self::assertSame(1, $this->option($response->json('filters.regions'), $samara->id)['count']);
        self::assertSame(1, $this->option($response->json('filters.regions'), $tula->id)['count']);
        self::assertSame(1, $response->json('filters.categories.0.count'));
        self::assertSame(1, $response->json('filters.categories.0.children.0.count'));
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
            'slug' => $attributes['slug'] ?? null,
            'category_id' => $attributes['category_id'] ?? null,
            'equipment_availability_id' => $attributes['equipment_availability_id'] ?? null,
            'equipment_state_id' => $attributes['equipment_state_id'] ?? null,
            'product_status_id' => $attributes['product_status_id'] ?? $this->saleStatus->id,
            'price' => $attributes['price'] ?? null,
            'show_price' => $attributes['show_price'] ?? true,
            'published_at' => array_key_exists('published_at', $attributes) ? $attributes['published_at'] : now(),
        ]);

        if ($regionIds !== null) {
            $product->regions()->sync($regionIds);
        }

        return $product;
    }

    /**
     * @param  list<array<string, mixed>>|null  $products
     * @return list<int>
     */
    private function productIds(?array $products): array
    {
        return array_map(
            fn (array $product): int => (int) $product['id'],
            $products ?? [],
        );
    }

    /**
     * @param  list<array<string, mixed>>|null  $options
     * @return array<string, mixed>
     */
    private function option(?array $options, int $id): array
    {
        foreach ($options ?? [] as $option) {
            if ($option['id'] === $id) {
                return $option;
            }
        }

        self::fail("Option [{$id}] was not found.");
    }
}
