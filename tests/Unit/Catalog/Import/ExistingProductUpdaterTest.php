<?php

namespace Tests\Unit\Catalog\Import;

use App\Domain\Catalog\Import\Resolvers\StatusResolvers;
use App\Domain\Catalog\Import\Services\ExistingProductUpdater;
use App\Domain\Catalog\Import\Services\FeedDownloader;
use App\Domain\Catalog\Import\Services\FeedParser;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class ExistingProductUpdaterTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_status_and_price_fields_for_all_existing_products(): void
    {
        $published = ProductStatus::query()->create([
            'external_id' => 4,
            'name' => 'Опубликовано',
        ]);
        $revision = ProductStatus::query()->create([
            'external_id' => 1,
            'name' => 'Ревизия',
        ]);

        $existingByExternalId = Product::query()->create([
            'external_id' => 101,
            'name' => 'CASE CX210',
            'sku' => 'cx210-001',
            'title' => 'CASE CX210',
            'product_status_id' => $revision->id,
            'price' => '120000.00',
            'show_price' => false,
            'price_comment' => 'Старая цена',
        ]);

        $existingBySku = Product::query()->create([
            'name' => 'CAT 320',
            'sku' => 'cat-320',
            'title' => 'CAT 320',
            'product_status_id' => $published->id,
            'price' => '99000.00',
            'show_price' => true,
            'price_comment' => 'Старый комментарий',
        ]);

        Product::query()->create([
            'external_id' => 303,
            'name' => 'Komatsu',
            'sku' => 'komatsu-303',
            'title' => 'Komatsu',
            'product_status_id' => $published->id,
            'price' => '50000.00',
            'show_price' => true,
            'price_comment' => 'Не должно измениться',
        ]);

        Http::fake([
            'https://example.com/statuses.xml' => Http::response($this->statusUpdateFeedXml(), 200, [
                'Content-Type' => 'application/xml',
            ]),
        ]);

        $result = $this->makeUpdater()->update('https://example.com/statuses.xml');

        self::assertSame(3, $result->updated);
        self::assertSame(1, $result->skipped);
        self::assertSame(0, $result->failed);

        $saleStatus = ProductStatus::query()->where('external_id', 4)->firstOrFail();
        $soldStatus = ProductStatus::query()->where('external_id', 5)->firstOrFail();

        $existingByExternalId->refresh();
        self::assertSame($saleStatus->id, $existingByExternalId->product_status_id);
        self::assertSame('180000.00', $existingByExternalId->price);
        self::assertTrue($existingByExternalId->show_price);
        self::assertSame(' Возможны различные формы оплаты. ', $existingByExternalId->price_comment);

        $existingBySku->refresh();
        self::assertSame(202, $existingBySku->external_id);
        self::assertSame($soldStatus->id, $existingBySku->product_status_id);
        self::assertSame('280000.00', $existingBySku->price);
        self::assertFalse($existingBySku->show_price);
        self::assertSame(' Цена по запросу. ', $existingBySku->price_comment);

        self::assertDatabaseHas('products', [
            'external_id' => 303,
            'product_status_id' => $saleStatus->id,
            'price' => '55000.00',
            'show_price' => 1,
            'price_comment' => ' Новая цена. ',
        ]);

        self::assertDatabaseMissing('products', [
            'external_id' => 404,
        ]);
    }

    private function makeUpdater(): ExistingProductUpdater
    {
        return new ExistingProductUpdater(
            new FeedDownloader(),
            new FeedParser(),
            new StatusResolvers(),
        );
    }

    private function statusUpdateFeedXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<advertisements_export export_date="2026-06-28 10:11:12">
    <advertisement_statuses>
        <status id="1">
            <name>Ревизия</name>
            <color>#cccccc</color>
        </status>
        <status id="4">
            <name>В продаже</name>
            <color>#00ff00</color>
        </status>
        <status id="5">
            <name>Продано</name>
            <color>#ff0000</color>
        </status>
    </advertisement_statuses>
    <advertisements>
        <advertisement id="101">
            <title>CASE CX210</title>
            <sku>cx210-001</sku>
            <status id="4">В продаже</status>
            <price>
                <adv_price>180000.00</adv_price>
                <show_price>1</show_price>
                <adv_price_comment><![CDATA[ Возможны различные формы оплаты. ]]></adv_price_comment>
            </price>
        </advertisement>
        <advertisement id="202">
            <title>CAT 320</title>
            <sku>cat-320</sku>
            <status id="5">Продано</status>
            <price>
                <adv_price>280000.00</adv_price>
                <show_price>0</show_price>
                <adv_price_comment><![CDATA[ Цена по запросу. ]]></adv_price_comment>
            </price>
        </advertisement>
        <advertisement id="303">
            <title>Komatsu</title>
            <sku>komatsu-303</sku>
            <status id="4">В продаже</status>
            <price>
                <adv_price>55000.00</adv_price>
                <show_price>1</show_price>
                <adv_price_comment><![CDATA[ Новая цена. ]]></adv_price_comment>
            </price>
        </advertisement>
        <advertisement id="404">
            <title>New machine</title>
            <sku>new-404</sku>
            <status id="4">В продаже</status>
            <price>
                <adv_price>999999.00</adv_price>
                <show_price>1</show_price>
                <adv_price_comment><![CDATA[ Не создавать новый товар. ]]></adv_price_comment>
            </price>
        </advertisement>
    </advertisements>
</advertisements_export>
XML;
    }
}
