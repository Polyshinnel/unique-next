<?php

namespace App\Domain\Catalog\Import\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

final class FeedDownloader
{
    public function download(string $url): string
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'catalog_feed_');

        if ($temporaryPath === false) {
            throw new RuntimeException('Unable to create a temporary file for the catalog feed.');
        }

        Http::timeout(config('catalog_import.http_timeout'))
            ->sink($temporaryPath)
            ->throw()
            ->get($url);

        return $temporaryPath;
    }
}
