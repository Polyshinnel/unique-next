<?php

namespace Tests\Unit\Catalog\Import;

use App\Domain\Catalog\Import\Jobs\ImportProductsJob;
use App\Domain\Catalog\Import\Services\ProductFeedImporter;
use App\Domain\Catalog\Models\ProductImport;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

final class ImportProductsJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_configures_queue_retries_backoff_and_unique_lock(): void
    {
        config(['catalog_import.queue' => 'imports']);

        $job = new ImportProductsJob('https://example.com/feed.xml', true);

        self::assertInstanceOf(ShouldQueue::class, $job);
        self::assertInstanceOf(ShouldBeUnique::class, $job);
        self::assertSame('https://example.com/feed.xml', $job->url);
        self::assertTrue($job->updateExisting);
        self::assertSame('imports', $job->queue);
        self::assertSame(1800, $job->timeout);
        self::assertSame(3, $job->tries);
        self::assertSame([60, 300, 900], $job->backoff());
        self::assertSame('catalog-import-products', $job->uniqueId());
        self::assertSame(1800, $job->uniqueFor());
    }

    public function test_handle_runs_importer_and_logs_result(): void
    {
        Http::fake([
            'https://example.com/feed.xml' => Http::response($this->emptyFeedXml(), 200, [
                'Content-Type' => 'application/xml',
            ]),
        ]);
        Log::spy();

        $job = new ImportProductsJob('https://example.com/feed.xml', true);

        $job->handle(app(ProductFeedImporter::class));

        Log::shouldHaveReceived('info')
            ->with('catalog import finished', [
                'product_import_id' => null,
                'created' => 0,
                'skipped' => 0,
                'failed' => 0,
                'message' => 'Processed 0 advertisements: created=0, skipped=0, failed=0.',
            ])
            ->once();
    }

    public function test_handle_updates_product_import_status(): void
    {
        Http::fake([
            'https://example.com/feed.xml' => Http::response($this->emptyFeedXml(), 200, [
                'Content-Type' => 'application/xml',
            ]),
        ]);

        $productImport = ProductImport::query()->create([
            'status' => 'queued',
        ]);

        $job = new ImportProductsJob('https://example.com/feed.xml', false, $productImport->id);

        $job->handle(app(ProductFeedImporter::class));

        $productImport->refresh();

        self::assertSame('completed', $productImport->status);
        self::assertSame(0, $productImport->created_count);
        self::assertSame(0, $productImport->skipped_count);
        self::assertSame(0, $productImport->failed_count);
        self::assertSame('2026-06-28 10:11:12', $productImport->feed_export_date?->format('Y-m-d H:i:s'));
        self::assertNotNull($productImport->started_at);
        self::assertNotNull($productImport->finished_at);
        self::assertStringContainsString('processed=0', (string) $productImport->message);
    }

    private function emptyFeedXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<advertisements_export export_date="2026-06-28 10:11:12">
    <categories />
    <advertisement_statuses />
    <check_statuses />
    <install_statuses />
</advertisements_export>
XML;
    }
}
