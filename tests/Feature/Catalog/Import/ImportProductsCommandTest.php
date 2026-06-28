<?php

namespace Tests\Feature\Catalog\Import;

use App\Domain\Catalog\Import\Jobs\ImportProductsJob;
use App\Domain\Catalog\Models\ProductImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ImportProductsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_job_with_configured_feed_url_by_default(): void
    {
        Queue::fake();
        config(['catalog_import.feed_url' => 'https://example.com/default-feed.xml']);
        config(['catalog_import.queue' => 'imports']);

        $this->artisan('catalog:import-products')
            ->expectsOutput('ImportProductsJob поставлен в очередь.')
            ->assertSuccessful();

        Queue::assertPushed(ImportProductsJob::class, function (ImportProductsJob $job): bool {
            return $job->url === 'https://example.com/default-feed.xml'
                && $job->updateExisting === false;
        });

        $productImport = ProductImport::query()->latest('id')->first();

        self::assertNotNull($productImport);
        self::assertSame('queued', $productImport->status);
        self::assertStringContainsString('queue=imports', (string) $productImport->message);
        self::assertStringContainsString('url=https://example.com/default-feed.xml', (string) $productImport->message);
    }

    public function test_it_dispatches_job_with_custom_url_and_update_existing_flag(): void
    {
        Queue::fake();

        $this->artisan('catalog:import-products', [
            '--url' => 'https://example.com/custom-feed.xml',
            '--update-existing' => true,
        ])
            ->expectsOutput('ImportProductsJob поставлен в очередь.')
            ->assertSuccessful();

        Queue::assertPushed(ImportProductsJob::class, function (ImportProductsJob $job): bool {
            return $job->url === 'https://example.com/custom-feed.xml'
                && $job->updateExisting === true;
        });
    }

    public function test_it_can_run_import_synchronously_and_show_progress(): void
    {
        Storage::fake('public');
        Http::fake([
            'https://example.com/feed.xml' => Http::response($this->emptyFeedXml(), 200, [
                'Content-Type' => 'application/xml',
            ]),
        ]);

        $this->artisan('catalog:import-products', [
            '--url' => 'https://example.com/feed.xml',
            '--sync' => true,
        ])
            ->expectsOutput('ImportProductsJob выполняется синхронно.')
            ->expectsOutput('Mode: sync')
            ->expectsOutput('Import started. update_existing=false; url=https://example.com/feed.xml')
            ->expectsOutput('Feed downloaded. export_date=2026-06-28 10:11:12')
            ->expectsOutput('References imported. categories=0; advertisement_statuses=0; check_statuses=0; install_statuses=0')
            ->expectsOutput('Import finished. processed=0; created=0; skipped=0; failed=0; export_date=2026-06-28 10:11:12')
            ->expectsOutput('Импорт завершён: created=0, skipped=0, failed=0.')
            ->assertSuccessful();

        $productImport = ProductImport::query()->latest('id')->firstOrFail();

        self::assertSame('completed', $productImport->status);
        self::assertSame(0, $productImport->created_count);
        self::assertSame(0, $productImport->skipped_count);
        self::assertSame(0, $productImport->failed_count);
        self::assertNotNull($productImport->feed_export_date);
        self::assertNotNull($productImport->started_at);
        self::assertNotNull($productImport->finished_at);
    }

    public function test_it_does_not_enqueue_duplicate_unique_jobs(): void
    {
        config(['cache.default' => 'array']);
        Cache::store('array')->flush();
        Queue::fake();

        $firstDispatch = ImportProductsJob::dispatch('https://example.com/feed.xml');
        unset($firstDispatch);

        $secondDispatch = ImportProductsJob::dispatch('https://example.com/feed.xml');
        unset($secondDispatch);

        Queue::assertPushed(ImportProductsJob::class, 1);
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
