<?php

namespace Tests\Unit\Catalog\Import;

use App\Domain\Catalog\Import\Jobs\ImportProductsJob;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Tests\TestCase;

final class ImportProductsJobTest extends TestCase
{
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
}
