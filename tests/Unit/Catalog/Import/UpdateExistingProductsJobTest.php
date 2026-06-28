<?php

namespace Tests\Unit\Catalog\Import;

use App\Domain\Catalog\Import\Jobs\UpdateExistingProductsJob;
use App\Domain\Catalog\Import\Services\ExistingProductUpdater;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductImport;
use App\Domain\Catalog\Models\ProductStatus;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

final class UpdateExistingProductsJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_configures_queue_retries_backoff_and_unique_lock(): void
    {
        config(['catalog_import.queue' => 'imports']);

        $job = new UpdateExistingProductsJob('https://example.com/feed.xml');

        self::assertInstanceOf(ShouldQueue::class, $job);
        self::assertInstanceOf(ShouldBeUnique::class, $job);
        self::assertSame('https://example.com/feed.xml', $job->url);
        self::assertSame('imports', $job->queue);
        self::assertSame(1800, $job->timeout);
        self::assertSame(3, $job->tries);
        self::assertSame([60, 300, 900], $job->backoff());
        self::assertSame('catalog-update-existing-products', $job->uniqueId());
        self::assertSame(1800, $job->uniqueFor());
    }

    public function test_handle_runs_updater_and_logs_result(): void
    {
        Product::query()->create([
            'external_id' => 101,
            'name' => 'CASE CX210',
            'sku' => 'cx210-001',
            'title' => 'CASE CX210',
            'show_price' => false,
        ]);

        Http::fake([
            'https://example.com/feed.xml' => Http::response($this->singleStatusFeedXml(), 200, [
                'Content-Type' => 'application/xml',
            ]),
        ]);
        Log::spy();

        $job = new UpdateExistingProductsJob('https://example.com/feed.xml');

        $job->handle(app(ExistingProductUpdater::class));

        Log::shouldHaveReceived('info')
            ->with('existing product update finished', [
                'product_import_id' => null,
                'updated' => 1,
                'skipped' => 0,
                'failed' => 0,
                'message' => 'Processed 1 advertisements: updated=1, skipped=0, failed=0.',
            ])
            ->once();
    }

    public function test_handle_updates_product_import_status(): void
    {
        Product::query()->create([
            'external_id' => 101,
            'name' => 'CASE CX210',
            'sku' => 'cx210-001',
            'title' => 'CASE CX210',
            'show_price' => false,
        ]);

        Http::fake([
            'https://example.com/feed.xml' => Http::response($this->singleStatusFeedXml(), 200, [
                'Content-Type' => 'application/xml',
            ]),
        ]);

        $productImport = ProductImport::query()->create([
            'status' => 'queued',
        ]);

        $job = new UpdateExistingProductsJob('https://example.com/feed.xml', $productImport->id);

        $job->handle(app(ExistingProductUpdater::class));

        $productImport->refresh();

        self::assertSame('completed', $productImport->status);
        self::assertSame(1, $productImport->created_count);
        self::assertSame(0, $productImport->skipped_count);
        self::assertSame(0, $productImport->failed_count);
        self::assertSame('2026-06-28 10:11:12', $productImport->feed_export_date?->format('Y-m-d H:i:s'));
        self::assertNotNull($productImport->started_at);
        self::assertNotNull($productImport->finished_at);
        self::assertStringContainsString('updated=1', (string) $productImport->message);
    }

    private function singleStatusFeedXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<advertisements_export export_date="2026-06-28 10:11:12">
    <advertisement_statuses>
        <status id="1">
            <name>Ревизия</name>
        </status>
        <status id="4">
            <name>В продаже</name>
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
    </advertisements>
</advertisements_export>
XML;
    }
}
