<?php

namespace Tests\Feature\Catalog\Import;

use App\Domain\Catalog\Import\Jobs\UpdateExistingProductsJob;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class UpdateExistingProductsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_job_with_configured_feed_url_by_default(): void
    {
        Queue::fake();
        config(['catalog_import.feed_url' => 'https://example.com/default-feed.xml']);
        config(['catalog_import.queue' => 'imports']);

        $this->artisan('catalog:update-existing-products')
            ->expectsOutput('UpdateExistingProductsJob поставлен в очередь.')
            ->assertSuccessful();

        Queue::assertPushed(UpdateExistingProductsJob::class, function (UpdateExistingProductsJob $job): bool {
            return $job->url === 'https://example.com/default-feed.xml';
        });

        $productImport = ProductImport::query()->latest('id')->first();

        self::assertNotNull($productImport);
        self::assertSame('queued', $productImport->status);
        self::assertStringContainsString('queue=imports', (string) $productImport->message);
        self::assertStringContainsString('url=https://example.com/default-feed.xml', (string) $productImport->message);
    }

    public function test_it_can_run_update_synchronously_and_show_progress(): void
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

        $this->artisan('catalog:update-existing-products', [
            '--url' => 'https://example.com/feed.xml',
            '--sync' => true,
        ])
            ->expectsOutput('UpdateExistingProductsJob выполняется синхронно.')
            ->expectsOutput('Mode: sync')
            ->expectsOutput('Existing product update started. url=https://example.com/feed.xml')
            ->expectsOutput('Existing product feed downloaded. export_date=2026-06-28 10:11:12')
            ->expectsOutput('Existing product update progress. processed=1; updated=1; skipped=0; failed=0; advertisement_external_id=101; sku=cx210-001')
            ->expectsOutput('Existing product update finished. processed=1; updated=1; skipped=0; failed=0; export_date=2026-06-28 10:11:12')
            ->expectsOutput('Обновление завершено: updated=1, skipped=0, failed=0.')
            ->assertSuccessful();

        $productImport = ProductImport::query()->latest('id')->firstOrFail();

        self::assertSame('completed', $productImport->status);
        self::assertSame(1, $productImport->created_count);
        self::assertSame(0, $productImport->skipped_count);
        self::assertSame(0, $productImport->failed_count);
        self::assertNotNull($productImport->feed_export_date);
        self::assertNotNull($productImport->started_at);
        self::assertNotNull($productImport->finished_at);
    }

    private function singleStatusFeedXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<advertisements_export export_date="2026-06-28 10:11:12">
    <advertisement_statuses>
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
