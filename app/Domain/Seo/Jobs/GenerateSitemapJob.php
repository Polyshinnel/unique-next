<?php

namespace App\Domain\Seo\Jobs;

use App\Domain\Seo\Services\SitemapGenerator;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

final class GenerateSitemapJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $timeout = 600;

    public int $tries = 3;

    public function __construct()
    {
        $this->onQueue((string) config('sitemap.queue'));
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(SitemapGenerator $generator): void
    {
        try {
            $result = $generator->generate();

            Log::info('sitemap generated', [
                'url_count' => $result->urlCount,
                'category_count' => $result->categoryCount,
                'product_count' => $result->productCount,
                'shipment_count' => $result->shipmentCount,
                'file_path' => $result->filePath,
            ]);
        } catch (Throwable $exception) {
            Log::error('sitemap generation failed', [
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function uniqueId(): string
    {
        return 'seo-generate-sitemap';
    }

    public function uniqueFor(): int
    {
        return 600;
    }
}
