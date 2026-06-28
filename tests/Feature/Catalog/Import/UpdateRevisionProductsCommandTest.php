<?php

namespace Tests\Feature\Catalog\Import;

use App\Domain\Catalog\Import\Jobs\UpdateRevisionProductsJob;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class UpdateRevisionProductsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_job_with_configured_feed_url_by_default(): void
    {
        Queue::fake();
        config(['catalog_import.feed_url' => 'https://example.com/default-feed.xml']);
        config(['catalog_import.queue' => 'imports']);

        $this->artisan('catalog:update-revision-products')
            ->expectsOutput('UpdateRevisionProductsJob поставлен в очередь.')
            ->assertSuccessful();

        Queue::assertPushed(UpdateRevisionProductsJob::class, function (UpdateRevisionProductsJob $job): bool {
            return $job->url === 'https://example.com/default-feed.xml';
        });

        $productImport = ProductImport::query()->latest('id')->first();

        self::assertNotNull($productImport);
        self::assertSame('queued', $productImport->status);
        self::assertStringContainsString('queue=imports', (string) $productImport->message);
    }

    public function test_it_can_run_update_synchronously_and_show_progress(): void
    {
        Storage::fake('public');
        Product::query()->create([
            'external_id' => 101,
            'name' => 'Old name',
            'sku' => 'cx210-001',
            'title' => 'Old name',
            'show_price' => false,
        ]);

        Http::fake([
            'https://example.com/feed.xml' => Http::response($this->feedXml(), 200, [
                'Content-Type' => 'application/xml',
            ]),
        ]);

        $this->artisan('catalog:update-revision-products', [
            '--url' => 'https://example.com/feed.xml',
            '--sync' => true,
        ])
            ->expectsOutput('UpdateRevisionProductsJob выполняется синхронно.')
            ->expectsOutput('Mode: sync')
            ->expectsOutput('Обновление завершено: updated=1, skipped=0, failed=0.')
            ->assertSuccessful();

        $product = Product::query()->where('external_id', 101)->firstOrFail();
        self::assertSame('CASE CX210', $product->name);
        self::assertSame('CASE CX210', $product->title);
        self::assertSame('180000.00', $product->price);

        $productImport = ProductImport::query()->latest('id')->firstOrFail();
        self::assertSame('completed', $productImport->status);
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
