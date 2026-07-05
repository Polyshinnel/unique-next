<?php

namespace Tests\Unit\Catalog\Resources;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\EquipmentAvailability;
use App\Domain\Catalog\Models\EquipmentState;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductImage;
use App\Domain\Catalog\Models\ProductMainCharacteristic;
use App\Domain\Catalog\Models\ProductMainInfo;
use App\Domain\Catalog\Models\ProductStatus;
use App\Domain\Catalog\Models\ProductTechnicalCharacteristic;
use App\Domain\Catalog\Models\Region;
use App\Http\Resources\Catalog\CatalogCategoryResource;
use App\Http\Resources\Catalog\CatalogFilterOptionResource;
use App\Http\Resources\Catalog\CatalogPageResource;
use App\Http\Resources\Catalog\CatalogProductCardResource;
use App\Http\Resources\Catalog\CatalogProductDetailResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

final class CatalogResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_resource_returns_public_catalog_shape(): void
    {
        $root = Category::query()->create([
            'name' => 'Металлообработка',
            'slug' => 'metalloobrabotka',
        ]);
        $child = Category::query()->create([
            'name' => 'Токарные станки',
            'title' => 'Купить токарные станки',
            'description' => 'Описание',
            'slug' => 'tokarnye-stanki',
            'parent_id' => $root->id,
        ]);

        $resource = (new CatalogCategoryResource($child))->resolve();

        self::assertSame([
            'id' => $child->id,
            'name' => 'Токарные станки',
            'title' => 'Купить токарные станки',
            'description' => 'Описание',
            'slug' => 'tokarnye-stanki',
            'path' => ['metalloobrabotka', 'tokarnye-stanki'],
            'href' => '/catalog/metalloobrabotka/tokarnye-stanki',
            'breadcrumbs' => [
                [
                    'id' => $root->id,
                    'name' => 'Металлообработка',
                    'slug' => 'metalloobrabotka',
                    'path' => ['metalloobrabotka'],
                    'pathString' => 'metalloobrabotka',
                    'href' => '/catalog/metalloobrabotka',
                    'level' => 0,
                ],
            ],
        ], $resource);
    }

    public function test_product_card_resource_hides_amount_when_price_is_not_published(): void
    {
        $category = Category::query()->create(['name' => 'Токарные станки', 'slug' => 'tokarnye-stanki']);
        $region = Region::query()->create(['name' => 'Самарская область']);
        $availability = EquipmentAvailability::query()->create(['name' => 'В наличии', 'color' => '#42a']);
        $state = EquipmentState::query()->create(['name' => 'Б.У']);
        $status = ProductStatus::query()->create(['name' => 'В продаже']);
        $product = Product::query()->create([
            'name' => 'Станок',
            'title' => 'Токарный станок 16К20',
            'sku' => 'UNQ-1',
            'category_id' => $category->id,
            'equipment_availability_id' => $availability->id,
            'equipment_state_id' => $state->id,
            'product_status_id' => $status->id,
            'price' => 95000,
            'show_price' => false,
            'published_at' => now(),
        ]);
        $product->regions()->attach($region->id);
        ProductImage::query()->create([
            'product_id' => $product->id,
            'file_name' => 'main.jpg',
            'file_path' => 'products/1/main.jpg',
            'is_main' => true,
            'sort_order' => 0,
        ]);

        $resource = (new CatalogProductCardResource($product->load([
            'category',
            'mainImage',
            'equipmentAvailability',
            'equipmentState',
            'regions',
        ])))->resolve();

        self::assertSame('/catalog/tokarnye-stanki/unq-1', $resource['href']);
        self::assertSame('/storage/products/1/main.jpg', $resource['imageUrl']);
        self::assertSame([
            'id' => $category->id,
            'name' => 'Токарные станки',
            'slug' => 'tokarnye-stanki',
            'path' => ['tokarnye-stanki'],
            'pathString' => 'tokarnye-stanki',
            'href' => '/catalog/tokarnye-stanki',
            'level' => 0,
            'breadcrumbs' => [],
        ], $resource['category']);
        self::assertSame(['id' => $region->id, 'name' => 'Самарская область'], $resource['region']);
        self::assertSame(['amount' => null, 'isPublished' => false, 'isReserve' => false, 'isSold' => false, 'label' => 'По запросу', 'comment' => null], $resource['price']);
        self::assertSame(['id' => $availability->id, 'name' => 'В наличии', 'color' => '#42a'], $resource['availability']);
        self::assertSame(['id' => $state->id, 'name' => 'Б.У'], $resource['state']);
    }

    public function test_product_card_resource_marks_reserved_product_price(): void
    {
        $category = Category::query()->create(['name' => 'Токарные станки', 'slug' => 'tokarnye-stanki']);
        $status = ProductStatus::query()->create(['name' => 'Резерв']);
        $product = Product::query()->create([
            'name' => 'Станок',
            'title' => 'Токарный станок 16К20',
            'category_id' => $category->id,
            'product_status_id' => $status->id,
            'price' => 95000,
            'show_price' => true,
            'published_at' => now(),
        ]);

        $product->setRelation('category', $category);
        $product->setRelation('mainImage', null);
        $product->setRelation('equipmentAvailability', null);
        $product->setRelation('equipmentState', null);
        $product->setRelation('regions', collect());
        $product->setRelation('productStatus', $status);

        $resource = (new CatalogProductCardResource($product))->resolve();

        self::assertSame(['amount' => null, 'isPublished' => false, 'isReserve' => true, 'isSold' => false, 'label' => 'Резерв', 'comment' => null], $resource['price']);
    }

    public function test_product_card_resource_uses_og_image_when_main_image_is_missing(): void
    {
        $category = Category::query()->create(['name' => 'Токарные станки', 'slug' => 'tokarnye-stanki']);
        $status = ProductStatus::query()->create(['name' => 'В продаже']);
        $product = Product::query()->create([
            'name' => 'Станок',
            'title' => 'Токарный станок',
            'category_id' => $category->id,
            'product_status_id' => $status->id,
            'og_image' => 'products/654/6107_5278222912676633859.jpg',
            'show_price' => true,
            'published_at' => now(),
        ]);
        $product->setRelation('category', $category);
        $product->setRelation('mainImage', null);
        $product->setRelation('equipmentAvailability', null);
        $product->setRelation('equipmentState', null);
        $product->setRelation('regions', collect());

        $resource = (new CatalogProductCardResource($product))->resolve();

        self::assertSame('/storage/products/654/6107_5278222912676633859.jpg', $resource['imageUrl']);
    }

    public function test_product_card_resource_falls_back_to_id_when_slug_is_missing(): void
    {
        $category = Category::query()->create(['name' => 'Токарные станки', 'slug' => 'tokarnye-stanki']);
        $status = ProductStatus::query()->create(['name' => 'В продаже']);
        $product = Product::query()->create([
            'name' => 'Станок',
            'title' => 'Токарный станок',
            'category_id' => $category->id,
            'product_status_id' => $status->id,
            'show_price' => true,
            'published_at' => now(),
        ]);

        $product->forceFill(['slug' => null])->save();
        $product->setRelation('category', $category);
        $product->setRelation('mainImage', null);
        $product->setRelation('equipmentAvailability', null);
        $product->setRelation('equipmentState', null);
        $product->setRelation('regions', collect());

        $resource = (new CatalogProductCardResource($product))->resolve();

        self::assertSame('/catalog/tokarnye-stanki/'.$product->id, $resource['href']);
    }

    public function test_product_detail_resource_returns_characteristic_html(): void
    {
        $category = Category::query()->create(['name' => 'Токарные станки', 'slug' => 'tokarnye-stanki']);
        $status = ProductStatus::query()->create(['name' => 'В продаже']);
        $product = Product::query()->create([
            'name' => 'Станок',
            'title' => 'Токарный станок',
            'category_id' => $category->id,
            'product_status_id' => $status->id,
            'show_price' => true,
            'published_at' => now(),
        ]);
        $product->setRelation('category', $category);
        $product->setRelation('images', collect());
        $product->setRelation('mainImage', null);
        $product->setRelation('equipmentAvailability', null);
        $product->setRelation('equipmentState', null);
        $product->setRelation('regions', collect());
        $product->setRelation('manager', null);
        $product->setRelation('tags', collect());
        $product->setRelation('mainCharacteristics', null);
        $product->setRelation('complectation', null);
        $product->setRelation('technicalCharacteristics', new ProductTechnicalCharacteristic([
            'content' => '<p>Габариты ДхШхВ<strong>:</strong> 2812х1348х1424</p>',
        ]));
        $product->setRelation('mainInfo', null);
        $product->setRelation('additionalInfo', null);

        $resource = (new CatalogProductDetailResource($product))->resolve();

        self::assertSame('<p>Габариты ДхШхВ<strong>:</strong> 2812х1348х1424</p>', $resource['characteristicBlocks'][0]['contentHtml']);
    }

    public function test_product_detail_resource_shows_reserve_in_sale_conditions(): void
    {
        $category = Category::query()->create(['name' => 'Токарные станки', 'slug' => 'tokarnye-stanki']);
        $status = ProductStatus::query()->create(['name' => 'Резерв']);
        $product = Product::query()->create([
            'name' => 'Станок',
            'title' => 'Токарный станок',
            'category_id' => $category->id,
            'product_status_id' => $status->id,
            'price' => 150000,
            'show_price' => true,
            'published_at' => now(),
        ]);
        $product->setRelation('category', $category);
        $product->setRelation('images', collect());
        $product->setRelation('mainImage', null);
        $product->setRelation('equipmentAvailability', null);
        $product->setRelation('equipmentState', null);
        $product->setRelation('regions', collect());
        $product->setRelation('manager', null);
        $product->setRelation('tags', collect());
        $product->setRelation('mainCharacteristics', null);
        $product->setRelation('complectation', null);
        $product->setRelation('technicalCharacteristics', null);
        $product->setRelation('mainInfo', null);
        $product->setRelation('additionalInfo', null);
        $product->setRelation('productStatus', $status);

        $resource = (new CatalogProductDetailResource($product))->resolve();

        self::assertSame('<p class="product-sale-price"><strong>Цена:</strong> Резерв</p>', $resource['characteristicBlocks'][0]['contentHtml']);
    }

    public function test_product_detail_resource_shows_sold_and_hides_sale_only_blocks(): void
    {
        $category = Category::query()->create(['name' => 'Токарные станки', 'slug' => 'tokarnye-stanki']);
        $status = ProductStatus::query()->create(['name' => 'Продано']);
        $product = Product::query()->create([
            'name' => 'Станок',
            'title' => 'Проданный токарный станок',
            'category_id' => $category->id,
            'product_status_id' => $status->id,
            'price' => 150000,
            'show_price' => true,
            'published_at' => now(),
        ]);
        $product->setRelation('category', $category);
        $product->setRelation('images', collect());
        $product->setRelation('mainImage', null);
        $product->setRelation('equipmentAvailability', new EquipmentAvailability([
            'name' => 'В наличии',
        ]));
        $product->setRelation('equipmentState', null);
        $product->setRelation('regions', collect());
        $product->setRelation('manager', null);
        $product->setRelation('tags', collect());
        $product->setRelation('mainCharacteristics', new ProductMainCharacteristic([
            'content' => '<p>Основные характеристики</p>',
        ]));
        $product->setRelation('complectation', new ProductTechnicalCharacteristic([
            'content' => '<p>Комплектация</p>',
        ]));
        $product->setRelation('technicalCharacteristics', new ProductTechnicalCharacteristic([
            'content' => '<p>Технические характеристики</p>',
        ]));
        $product->setRelation('mainInfo', new ProductMainInfo([
            'content' => '<p>Основная информация</p>',
        ]));
        $product->setRelation('additionalInfo', new ProductTechnicalCharacteristic([
            'content' => '<p>Дополнительная информация</p>',
        ]));
        $product->setRelation('productStatus', $status);

        $resource = (new CatalogProductDetailResource($product))->resolve();

        self::assertSame(['amount' => null, 'isPublished' => false, 'isReserve' => false, 'isSold' => true, 'label' => 'Продано', 'comment' => null], $resource['price']);
        self::assertSame(['Основные характеристики', 'Основная информация'], array_column($resource['characteristicBlocks'], 'title'));
    }

    public function test_product_detail_resource_scrubs_invalid_utf8_characteristic_html(): void
    {
        $category = Category::query()->create(['name' => 'Токарные станки', 'slug' => 'tokarnye-stanki']);
        $status = ProductStatus::query()->create(['name' => 'В продаже']);
        $product = Product::query()->create([
            'name' => 'Станок',
            'title' => 'Токарный станок',
            'category_id' => $category->id,
            'product_status_id' => $status->id,
            'show_price' => true,
            'published_at' => now(),
        ]);
        $product->setRelation('category', $category);
        $product->setRelation('images', collect());
        $product->setRelation('mainImage', null);
        $product->setRelation('equipmentAvailability', null);
        $product->setRelation('equipmentState', null);
        $product->setRelation('regions', collect());
        $product->setRelation('manager', null);
        $product->setRelation('tags', collect());
        $product->setRelation('mainCharacteristics', null);
        $product->setRelation('complectation', null);
        $product->setRelation('technicalCharacteristics', new ProductTechnicalCharacteristic([
            'content' => "Габариты 1000\xC3\x28\nМощность 5 кВт",
        ]));
        $product->setRelation('mainInfo', null);
        $product->setRelation('additionalInfo', null);

        $resource = (new CatalogProductDetailResource($product))->resolve();

        self::assertNotFalse(json_encode($resource));
        self::assertSame("Габариты 1000?(\nМощность 5 кВт", $resource['characteristicBlocks'][0]['contentHtml']);
    }

    public function test_product_detail_resource_repairs_legacy_dimension_separators(): void
    {
        $category = Category::query()->create(['name' => 'Токарные станки', 'slug' => 'tokarnye-stanki']);
        $status = ProductStatus::query()->create(['name' => 'В продаже']);
        $product = Product::query()->create([
            'name' => 'Станок',
            'title' => 'Токарный станок',
            'category_id' => $category->id,
            'product_status_id' => $status->id,
            'show_price' => true,
            'published_at' => now(),
        ]);
        $product->setRelation('category', $category);
        $product->setRelation('images', collect());
        $product->setRelation('mainImage', null);
        $product->setRelation('equipmentAvailability', null);
        $product->setRelation('equipmentState', null);
        $product->setRelation('regions', collect());
        $product->setRelation('manager', null);
        $product->setRelation('tags', collect());
        $product->setRelation('mainCharacteristics', null);
        $product->setRelation('complectation', null);
        $product->setRelation('technicalCharacteristics', new ProductTechnicalCharacteristic([
            'content' => "<p>Габариты Д\xD7Ш\xD7В: 2812\xD71348\xD71424</p>",
        ]));
        $product->setRelation('mainInfo', null);
        $product->setRelation('additionalInfo', null);

        $resource = (new CatalogProductDetailResource($product))->resolve();

        self::assertSame('<p>Габариты Д×Ш×В: 2812×1348×1424</p>', $resource['characteristicBlocks'][0]['contentHtml']);
    }

    public function test_filter_option_resource_supports_nested_category_options(): void
    {
        $resource = (new CatalogFilterOptionResource([
            'id' => 1,
            'name' => 'Токарные станки',
            'slug' => 'tokarnye-stanki',
            'count' => 12,
            'href' => '/catalog/tokarnye-stanki',
            'level' => 0,
            'children' => [
                [
                    'id' => 2,
                    'name' => '16К20 и аналоги',
                    'slug' => '16k20-i-analogi',
                    'count' => 5,
                    'href' => '/catalog/tokarnye-stanki/16k20-i-analogi',
                    'level' => 1,
                    'children' => [],
                ],
            ],
        ]))->resolve();

        self::assertSame('tokarnye-stanki', $resource['slug']);
        self::assertSame(0, $resource['level']);
        self::assertSame('16К20 и аналоги', $resource['children'][0]['name']);
        self::assertSame(5, $resource['children'][0]['count']);
    }

    public function test_page_resource_returns_sorting_products_and_pagination_shape(): void
    {
        $category = Category::query()->create(['name' => 'Токарные станки', 'slug' => 'tokarnye-stanki']);
        $availability = EquipmentAvailability::query()->create(['name' => 'В наличии']);
        $state = EquipmentState::query()->create(['name' => 'Б.У']);
        $status = ProductStatus::query()->create(['name' => 'В продаже']);
        $product = Product::query()->create([
            'name' => 'Станок',
            'title' => 'Токарный станок',
            'sku' => 'UNQ-2',
            'category_id' => $category->id,
            'equipment_availability_id' => $availability->id,
            'equipment_state_id' => $state->id,
            'product_status_id' => $status->id,
            'show_price' => true,
            'published_at' => now(),
        ]);
        $product->load(['category', 'mainImage', 'equipmentAvailability', 'equipmentState', 'regions']);
        $paginator = new LengthAwarePaginator([$product], total: 25, perPage: 12, currentPage: 2);

        $resource = (new CatalogPageResource([
            'category' => $category,
            'filters' => [
                'regions' => [['id' => 1, 'name' => 'Самарская область', 'count' => 3, 'href' => '/catalog?region=1']],
                'categories' => [],
                'availabilities' => [],
                'states' => [],
            ],
            'sorting' => ['active' => 'price_desc'],
            'products' => $paginator,
        ]))->resolve();

        self::assertSame('price_desc', $resource['sorting']['active']);
        self::assertCount(3, $resource['sorting']['options']);
        self::assertSame($product->id, $resource['products'][0]['id']);
        self::assertSame([
            'currentPage' => 2,
            'perPage' => 12,
            'total' => 25,
            'totalPages' => 3,
        ], $resource['pagination']);
    }

    public function test_page_resource_returns_null_category_for_catalog_root(): void
    {
        $paginator = new LengthAwarePaginator([], total: 0, perPage: 12, currentPage: 1);

        $resource = (new CatalogPageResource([
            'category' => null,
            'filters' => [
                'regions' => [],
                'categories' => [],
                'availabilities' => [],
                'states' => [],
            ],
            'sorting' => ['active' => 'default'],
            'products' => $paginator,
        ]))->resolve();

        self::assertNull($resource['category']);
    }
}
