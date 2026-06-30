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
use App\Domain\Catalog\Import\Services\ProductImageSynchronizer;
use App\Domain\Catalog\Import\Services\ProductOgImageSynchronizer;
use App\Domain\Catalog\Import\Services\RevisionProductUpdater;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductMainCharacteristic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class RevisionProductUpdaterTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_fully_updates_existing_product_fields_and_images_and_skips_new(): void
    {
        Storage::fake('public');
        Http::fake([
            'https://example.com/feed.xml' => Http::response($this->feedXml(), 200, [
                'Content-Type' => 'application/xml',
            ]),
            'https://example.com/9001.jpg' => Http::response('new-main', 200, [
                'Content-Type' => 'image/jpeg',
                'Content-Length' => '8',
            ]),
        ]);

        $product = Product::query()->create([
            'external_id' => 101,
            'name' => 'Old name',
            'sku' => 'cx210-001',
            'title' => 'Old name',
            'price' => '100000.00',
            'show_price' => false,
            'price_comment' => 'Старая цена',
        ]);

        // Orphan image that is no longer present in the feed.
        $orphanPath = "products/{$product->id}/9000.jpg";
        Storage::disk('public')->put($orphanPath, 'orphan');
        $product->images()->create([
            'external_id' => 9000,
            'file_name' => '9000.jpg',
            'file_path' => $orphanPath,
            'file_url' => 'https://example.com/9000.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 6,
            'sort_order' => 1,
            'is_main' => true,
        ]);

        $result = $this->makeUpdater()->update('https://example.com/feed.xml');

        self::assertSame(1, $result->updated);
        self::assertSame(1, $result->skipped);
        self::assertSame(0, $result->failed);

        $product->refresh()->load(['region', 'tags']);

        // Scalar fields fully updated.
        self::assertSame('CASE CX210 updated', $product->name);
        self::assertSame('CASE CX210 updated', $product->title);
        self::assertSame('12500000.00', $product->price);
        self::assertTrue($product->show_price);
        self::assertSame('С НДС', $product->price_comment);
        self::assertNotNull($product->region);
        self::assertSame('Москва', $product->region->name);
        self::assertSame("products/{$product->id}/9001_9001.jpg", $product->og_image);

        // Relations + text blocks updated.
        self::assertSame(['лизинг'], $product->tags->pluck('name')->all());
        self::assertSame('Новые характеристики', ProductMainCharacteristic::query()->value('content'));

        // Image set synchronised: orphan removed, new image downloaded and main.
        self::assertDatabaseMissing('product_images', [
            'product_id' => $product->id,
            'external_id' => 9000,
        ]);
        Storage::disk('public')->assertMissing($orphanPath);
        self::assertSame(1, $product->images()->count());

        $image = $product->images()->firstOrFail();
        self::assertSame(9001, $image->external_id);
        self::assertTrue($image->is_main);
        Storage::disk('public')->assertExists("products/{$product->id}/9001_9001.jpg");

        // New advertisement (404) must NOT be created.
        self::assertDatabaseMissing('products', ['external_id' => 404]);
        self::assertSame(1, Product::query()->count());
    }

    private function makeUpdater(): RevisionProductUpdater
    {
        return new RevisionProductUpdater(
            new FeedDownloader(),
            new FeedParser(),
            new CategoryResolver(),
            new StatusResolvers(),
            new ManagerResolver(),
            new RegionResolver(),
            new TagResolver(),
            new ProductImageSynchronizer(new ImageDownloader()),
            new ProductOgImageSynchronizer(),
        );
    }

    private function feedXml(): string
    {
        return <<<'XML'
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
    <check_statuses />
    <install_statuses />
    <advertisement id="101">
        <title>CASE CX210 updated</title>
        <sku>cx210-001</sku>
        <category id="10">Экскаваторы</category>
        <status id="4">Опубликовано</status>
        <product_state id="15">Б/у</product_state>
        <product_available id="21">В наличии</product_available>
        <price>
            <adv_price>12500000</adv_price>
            <show_price>1</show_price>
            <adv_price_comment><![CDATA[С НДС]]></adv_price_comment>
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
            <published_at>2026-06-25 18:30:00</published_at>
        </dates>
        <tags>
            <tag>лизинг</tag>
        </tags>
        <main_characteristics><![CDATA[Новые характеристики]]></main_characteristics>
        <media>
            <media_item id="9001">
                <file_name>9001.jpg</file_name>
                <file_url>https://example.com/9001.jpg</file_url>
                <mime_type>image/jpeg</mime_type>
                <file_size>2048</file_size>
                <sort_order>0</sort_order>
                <is_main_image>1</is_main_image>
            </media_item>
        </media>
    </advertisement>
    <advertisement id="404">
        <title>New machine</title>
        <sku>new-404</sku>
        <category id="10">Экскаваторы</category>
        <status id="4">Опубликовано</status>
        <price>
            <adv_price>999999</adv_price>
            <show_price>1</show_price>
        </price>
    </advertisement>
</advertisements_export>
XML;
    }
}
