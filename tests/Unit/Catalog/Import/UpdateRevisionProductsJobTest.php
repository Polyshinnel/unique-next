<?php

namespace Tests\Unit\Catalog\Import;

use App\Domain\Catalog\Import\Jobs\UpdateRevisionProductsJob;
use App\Domain\Catalog\Import\Services\RevisionProductUpdater;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductImport;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class UpdateRevisionProductsJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_configures_queue_retries_backoff_and_unique_lock(): void
    {
        config(['catalog_import.queue' => 'imports']);

        $job = new UpdateRevisionProductsJob('https://example.com/feed.xml');

        self::assertInstanceOf(ShouldQueue::class, $job);
        self::assertInstanceOf(ShouldBeUnique::class, $job);
        self::assertSame('https://example.com/feed.xml', $job->url);
        self::assertSame('imports', $job->queue);
        self::assertSame(1800, $job->timeout);
        self::assertSame(3, $job->tries);
        self::assertSame([60, 300, 900], $job->backoff());
        self::assertSame('catalog-update-revision-products', $job->uniqueId());
        self::assertSame(1800, $job->uniqueFor());
    }

    public function test_handle_runs_updater_and_updates_product_import_status(): void
    {
        Storage::fake('public');
        Product::query()->create([
            'external_id' => 101,
            'name' => 'CASE CX210',
            'sku' => 'cx210-001',
            'title' => 'CASE CX210',
            'show_price' => false,
        ]);

        Http::fake([
            'https://example.com/feed.xml' => Http::response($this->feedXml(), 200, [
                'Content-Type' => 'application/xml',
            ]),
        ]);
        Log::spy();

        $productImport = ProductImport::query()->create(['status' => 'queued']);

        $job = new UpdateRevisionProductsJob('https://example.com/feed.xml', $productImport->id);
        $job->handle(app(RevisionProductUpdater::class));

        Log::shouldHaveReceived('info')
            ->with('revision product update finished', [
                'product_import_id' => $productImport->id,
                'updated' => 1,
                'skipped' => 0,
                'failed' => 0,
                'message' => 'Processed 1 advertisements: updated=1, skipped=0, failed=0.',
            ])
            ->once();

        $productImport->refresh();

        self::assertSame('completed', $productImport->status);
        self::assertSame(1, $productImport->created_count);
        self::assertNotNull($productImport->finished_at);
    }

    private function feedXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<advertisements_export export_date="2026-06-28 10:11:12">
    <advertisement_statuses>
        <status id="4">
            <name>В продаже</name>
        </status>
    </advertisement_statuses>
    <check_statuses />
    <install_statuses />
    <advertisement id="101">
        <title>CASE CX210</title>
        <sku>cx210-001</sku>
        <status id="4">В продаже</status>
        <price>
            <adv_price>180000.00</adv_price>
            <show_price>1</show_price>
        </price>
    </advertisement>
</advertisements_export>
XML;
    }
}
