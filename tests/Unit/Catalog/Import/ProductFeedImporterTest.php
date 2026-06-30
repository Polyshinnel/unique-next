<?php

namespace Tests\Unit\Catalog\Import;

use App\Domain\Catalog\Import\Resolvers\CategoryResolver;
use App\Domain\Catalog\Import\Resolvers\ManagerResolver;
use App\Domain\Catalog\Import\Resolvers\RegionResolver;
use App\Domain\Catalog\Import\Resolvers\StatusResolvers;
use App\Domain\Catalog\Import\Resolvers\TagResolver;
use App\Domain\Catalog\Import\Services\FeedDownloader;
use App\Domain\Catalog\Import\Services\FeedParser;
use App\Domain\Catalog\Import\Services\ImageDownloader;
use App\Domain\Catalog\Import\Services\ProductFeedImporter;
use App\Domain\Catalog\Import\Services\ProductOgImageSynchronizer;
use App\Domain\Catalog\Models\CheckStatus;
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
use App\Domain\Catalog\Models\ProductTechnicalCharacteristic;
use App\Domain\Catalog\Models\Region;
use App\Domain\Catalog\Models\ShipmentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ProductFeedImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_references_products_relations_and_images(): void
    {
        Storage::fake('public');
        Http::fake([
            'https://example.com/feed.xml' => Http::response($this->fixtureXml(), 200, [
                'Content-Type' => 'application/xml',
            ]),
            'https://example.com/main.jpg' => Http::response('main-image', 200, [
                'Content-Type' => 'image/jpeg',
                'Content-Length' => '10',
            ]),
            'https://example.com/extra.jpg' => Http::response('extra-image', 200, [
                'Content-Type' => 'image/jpeg',
                'Content-Length' => '11',
            ]),
        ]);

        $result = $this->makeImporter()->import('https://example.com/feed.xml');

        self::assertSame(1, $result->created);
        self::assertSame(0, $result->skipped);
        self::assertSame(0, $result->failed);

        /** @var Product $product */
        $product = Product::query()->firstOrFail();
        $product->load(['region', 'regions', 'tags', 'managers', 'images']);

        self::assertSame(101, $product->external_id);
        self::assertSame('CASE CX210', $product->name);
        self::assertSame('CASE CX210', $product->title);
        self::assertSame('cx210-001', $product->sku);
        self::assertSame('12500000.00', $product->price);
        self::assertTrue($product->show_price);
        self::assertSame('С НДС', $product->price_comment);
        self::assertNotNull($product->region);
        self::assertSame('Москва', $product->region->name);
        self::assertSame('2026-06-20 09:00:00', $product->published_at?->format('Y-m-d H:i:s'));
        self::assertNull($product->description);
        self::assertSame("products/{$product->id}/9002_extra.jpg", $product->og_image);

        self::assertSame(['Москва'], $product->regions->pluck('name')->all());
        self::assertSame(['лизинг', 'в наличии'], $product->tags->pluck('name')->all());
        self::assertSame(1, $product->managers->count());
        self::assertSame('product_owner', $product->managers->first()->pivot->role);
        self::assertSame($product->manager_id, $product->managers->first()->id);

        self::assertDatabaseHas('categories', [
            'external_id' => 10,
            'name' => 'Экскаваторы',
        ]);
        self::assertDatabaseHas('product_statuses', [
            'external_id' => 4,
            'name' => 'Опубликовано',
        ]);
        self::assertDatabaseHas('equipment_states', [
            'external_id' => 15,
            'name' => 'Б/у',
        ]);
        self::assertDatabaseHas('equipment_availabilities', [
            'external_id' => 21,
            'name' => 'В наличии',
        ]);
        self::assertDatabaseHas('products', [
            'external_id' => 101,
            'region_id' => $product->region->id,
        ]);
        self::assertDatabaseHas('managers', [
            'external_id' => 501,
            'name' => 'Иван Иванов',
            'role' => 'owner',
        ]);
        self::assertDatabaseHas('shipment_statuses', [
            'external_id' => 9,
            'name' => 'Готово к погрузке',
        ]);
        self::assertDatabaseHas('dismantle_statuses', [
            'name' => 'Демонтаж не требуется',
            'external_id' => null,
        ]);

        self::assertSame('Основные <b>характеристики</b>', ProductMainCharacteristic::query()->value('content'));
        self::assertSame('Полная комплектация', ProductComplectation::query()->value('content'));
        self::assertSame('Технические характеристики', ProductTechnicalCharacteristic::query()->value('content'));
        self::assertSame('Главная информация', ProductMainInfo::query()->value('content'));
        self::assertSame('Дополнительная информация', ProductAdditionalInfo::query()->value('content'));

        self::assertSame('Осмотр завершен', ProductCheck::query()->value('comment'));
        self::assertSame(
            CheckStatus::query()->where('name', 'Проверено')->value('id'),
            ProductCheck::query()->value('check_status_id'),
        );
        self::assertSame('Погрузка завтра', ProductLoading::query()->value('comment'));
        self::assertSame(
            ShipmentStatus::query()->where('name', 'Готово к погрузке')->value('id'),
            ProductLoading::query()->value('shipment_status_id'),
        );
        self::assertSame('Установлено на складе', ProductDismantling::query()->value('comment'));

        self::assertSame(2, ProductImage::query()->count());
        Storage::disk('public')->assertExists("products/{$product->id}/9001_main.jpg");
        Storage::disk('public')->assertExists("products/{$product->id}/9002_extra.jpg");
    }

    public function test_it_skips_existing_products_and_updates_them_when_requested(): void
    {
        Storage::fake('public');
        Http::fake([
            'https://example.com/feed.xml' => Http::sequence()
                ->push($this->singleAdvertisementXml(
                    price: '12500000',
                    showPrice: true,
                    priceComment: 'С НДС',
                    publishedAt: '2026-06-20 09:00:00',
                    tags: ['лизинг', 'в наличии'],
                ), 200)
                ->push($this->singleAdvertisementXml(
                    price: '13000000',
                    showPrice: false,
                    priceComment: 'Без НДС',
                    publishedAt: '2026-06-25 18:30:00',
                    tags: ['обновлено'],
                ), 200)
                ->push($this->singleAdvertisementXml(
                    price: '13000000',
                    showPrice: false,
                    priceComment: 'Без НДС',
                    publishedAt: '2026-06-25 18:30:00',
                    tags: ['обновлено'],
                ), 200),
            'https://example.com/main.jpg' => Http::response('main-image', 200, [
                'Content-Type' => 'image/jpeg',
                'Content-Length' => '10',
            ]),
        ]);

        $importer = $this->makeImporter();
        $first = $importer->import('https://example.com/feed.xml');

        self::assertSame(1, $first->created);

        Http::fake([
            'https://example.com/feed.xml' => Http::response($this->singleAdvertisementXml(
                price: '13000000',
                showPrice: false,
                priceComment: 'Без НДС',
                publishedAt: '2026-06-25 18:30:00',
                tags: ['обновлено'],
            ), 200),
            'https://example.com/main.jpg' => Http::response('main-image-updated', 200, [
                'Content-Type' => 'image/jpeg',
                'Content-Length' => '18',
            ]),
        ]);

        $skipped = $importer->import('https://example.com/feed.xml', false);

        self::assertSame(0, $skipped->created);
        self::assertSame(1, $skipped->skipped);
        self::assertSame(0, $skipped->failed);

        /** @var Product $product */
        $product = Product::query()->firstOrFail();
        $product->load('tags');

        self::assertSame('12500000.00', $product->price);
        self::assertTrue($product->show_price);
        self::assertSame(['лизинг', 'в наличии'], $product->tags->pluck('name')->all());

        $updated = $importer->import('https://example.com/feed.xml', true);
        $product->refresh()->load('tags');

        self::assertSame(0, $updated->created);
        self::assertSame(0, $updated->skipped);
        self::assertSame(0, $updated->failed);
        self::assertSame('13000000.00', $product->price);
        self::assertFalse($product->show_price);
        self::assertSame('Без НДС', $product->price_comment);
        self::assertSame('CASE CX210 updated name', $product->title);
        self::assertSame('2026-06-25 18:30:00', $product->published_at?->format('Y-m-d H:i:s'));
        self::assertSame(['обновлено'], $product->tags->pluck('name')->all());
    }

    public function test_it_continues_after_a_product_level_failure(): void
    {
        Storage::fake('public');
        Http::fake([
            'https://example.com/feed.xml' => Http::response($this->twoAdvertisementXmlWithBrokenFirstItem(), 200),
            'https://example.com/second.jpg' => Http::response('second-image', 200, [
                'Content-Type' => 'image/jpeg',
                'Content-Length' => '12',
            ]),
        ]);

        $result = $this->makeImporter()->import('https://example.com/feed.xml');

        self::assertSame(1, $result->created);
        self::assertSame(0, $result->skipped);
        self::assertSame(1, $result->failed);
        self::assertDatabaseCount('products', 1);
        self::assertDatabaseHas('products', [
            'external_id' => 202,
            'name' => 'Second valid product',
        ]);
    }

    private function makeImporter(): ProductFeedImporter
    {
        return new ProductFeedImporter(
            new FeedDownloader(),
            new FeedParser(),
            new CategoryResolver(),
            new StatusResolvers(),
            new ManagerResolver(),
            new RegionResolver(),
            new TagResolver(),
            new ImageDownloader(),
            new ProductOgImageSynchronizer(),
        );
    }

    private function fixtureXml(): string
    {
        return file_get_contents(base_path('tests/Fixtures/advertisements.xml'));
    }

    /**
     * @param list<string> $tags
     */
    private function singleAdvertisementXml(
        string $price,
        bool $showPrice,
        string $priceComment,
        string $publishedAt,
        array $tags,
    ): string {
        $tagXml = implode('', array_map(
            static fn (string $tag): string => sprintf('<tag>%s</tag>', $tag),
            $tags,
        ));

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<advertisements_export export_date="2026-06-28 10:11:12">
    <categories>
        <category id="10">
            <name>Экскаваторы</name>
        </category>
    </categories>
    <advertisement_statuses>
        <status id="4">
            <name>Опубликовано</name>
            <color>#00ff00</color>
        </status>
    </advertisement_statuses>
    <check_statuses>
        <status id="7">
            <name>Проверено</name>
            <color>#111111</color>
        </status>
    </check_statuses>
    <install_statuses>
        <status id="9">
            <name>Готово к погрузке</name>
        </status>
    </install_statuses>
    <advertisement id="101">
        <title>CASE CX210 updated name</title>
        <sku>cx210-001</sku>
        <category id="10">Экскаваторы</category>
        <status id="4">Опубликовано</status>
        <product_state id="15">Б/у</product_state>
        <product_available id="21">В наличии</product_available>
        <price>
            <adv_price>{$price}</adv_price>
            <show_price>{$this->boolToInt($showPrice)}</show_price>
            <adv_price_comment><![CDATA[{$priceComment}]]></adv_price_comment>
        </price>
        <location>
            <product_address>Москва, склад 1</product_address>
            <regions>
                <region id="77">
                    <name>Москва</name>
                </region>
            </regions>
        </location>
        <dates>
            <published_at>{$publishedAt}</published_at>
        </dates>
        <manager>
            <product_owner id="501">
                <name>Иван Иванов</name>
                <email>ivan@example.com</email>
                <phone>+7 900 000-00-00</phone>
                <role>product_owner</role>
            </product_owner>
        </manager>
        <tags>{$tagXml}</tags>
        <media>
            <media_item id="9001">
                <file_name>main.jpg</file_name>
                <file_url>https://example.com/main.jpg</file_url>
                <mime_type>image/jpeg</mime_type>
                <file_size>2048</file_size>
                <sort_order>0</sort_order>
                <is_main_image>1</is_main_image>
            </media_item>
        </media>
        <check>
            <status>Проверено</status>
            <comment><![CDATA[Осмотр завершен]]></comment>
        </check>
    </advertisement>
</advertisements_export>
XML;
    }

    private function twoAdvertisementXmlWithBrokenFirstItem(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<advertisements_export export_date="2026-06-28 10:11:12">
    <categories>
        <category id="10">
            <name>Экскаваторы</name>
        </category>
    </categories>
    <advertisement_statuses>
        <status id="4">
            <name>Опубликовано</name>
        </status>
    </advertisement_statuses>
    <check_statuses />
    <install_statuses />
    <advertisement id="201">
        <title>Broken product</title>
        <sku>broken-001</sku>
        <category id="10">Экскаваторы</category>
        <status id="4">Опубликовано</status>
        <dates>
            <published_at>not-a-date</published_at>
        </dates>
    </advertisement>
    <advertisement id="202">
        <title>Second valid product</title>
        <sku>valid-002</sku>
        <category id="10">Экскаваторы</category>
        <status id="4">Опубликовано</status>
        <product_state id="15">Новое</product_state>
        <product_available id="21">В наличии</product_available>
        <price>
            <adv_price>500000</adv_price>
            <show_price>1</show_price>
        </price>
        <dates>
            <published_at>2026-06-21 11:15:00</published_at>
        </dates>
        <media>
            <media_item id="9201">
                <file_name>second.jpg</file_name>
                <file_url>https://example.com/second.jpg</file_url>
                <mime_type>image/jpeg</mime_type>
                <file_size>100</file_size>
                <sort_order>0</sort_order>
                <is_main_image>1</is_main_image>
            </media_item>
        </media>
    </advertisement>
</advertisements_export>
XML;
    }

    private function boolToInt(bool $value): int
    {
        return $value ? 1 : 0;
    }
}
