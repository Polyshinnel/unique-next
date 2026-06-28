<?php

namespace Tests\Unit\Catalog\Import;

use App\Domain\Catalog\Import\Services\FeedDownloader;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class FeedDownloaderTest extends TestCase
{
    public function test_it_downloads_feed_into_temporary_file(): void
    {
        Http::fake([
            'https://example.com/feed.xml' => Http::response('<root>ok</root>', 200, [
                'Content-Type' => 'application/xml',
            ]),
        ]);

        $downloader = new FeedDownloader();
        $path = $downloader->download('https://example.com/feed.xml');

        try {
            self::assertFileExists($path);
            self::assertSame('<root>ok</root>', file_get_contents($path));
        } finally {
            @unlink($path);
        }
    }
}
