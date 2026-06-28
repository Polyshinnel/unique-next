<?php

namespace App\Domain\Catalog\Import\Services;

use App\Domain\Catalog\Import\Data\MediaItemData;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductImage;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

final class ImageDownloader
{
    public function download(Product $product, MediaItemData $media): ?ProductImage
    {
        $disk = Storage::disk(config('catalog_import.image_disk'));

        $image = ProductImage::query()->firstOrNew([
            'product_id' => $product->id,
            'external_id' => $media->externalId,
        ]);

        if ($image->exists && $image->file_path !== null && $disk->exists($image->file_path)) {
            return $image;
        }

        if (! $this->looksLikeImage($media->mimeType)) {
            $this->logWarning($product, $media, 'Catalog image download skipped non-image media metadata.');

            return null;
        }

        try {
            $response = Http::timeout(config('catalog_import.http_timeout'))
                ->get($media->fileUrl);
        } catch (Throwable $exception) {
            $this->logWarning($product, $media, 'Catalog image download request failed.', [
                'error' => $exception->getMessage(),
            ]);

            return null;
        }

        if (! $response->successful()) {
            $this->logWarning($product, $media, 'Catalog image download failed.', [
                'status' => $response->status(),
            ]);

            return null;
        }

        $contentType = $this->normalizeMimeType($response->header('Content-Type'));

        if (! $this->looksLikeImage($contentType)) {
            $this->logWarning($product, $media, 'Catalog image download skipped non-image response.', [
                'content_type' => $response->header('Content-Type'),
            ]);

            return null;
        }

        $path = $this->buildStoragePath($product, $media, $contentType);

        if (! $disk->put($path, $response->body())) {
            $this->logWarning($product, $media, 'Catalog image download could not persist file.', [
                'path' => $path,
            ]);

            return null;
        }

        $image->fill([
            'file_name' => $media->fileName,
            'file_path' => $path,
            'file_url' => $media->fileUrl,
            'mime_type' => $contentType ?? $this->normalizeMimeType($media->mimeType),
            'file_size' => $media->fileSize ?? $this->contentLength($response),
            'sort_order' => $media->sortOrder,
            'is_main' => $media->isMain,
        ]);
        $image->save();

        return $image->fresh();
    }

    private function buildStoragePath(Product $product, MediaItemData $media, ?string $contentType): string
    {
        $directory = "products/{$product->id}";
        $safeFileName = $this->safeFileName($media, $contentType);

        return "{$directory}/{$media->externalId}_{$safeFileName}";
    }

    private function safeFileName(MediaItemData $media, ?string $contentType): string
    {
        $extension = strtolower(pathinfo($media->fileName, PATHINFO_EXTENSION));
        $baseName = pathinfo($media->fileName, PATHINFO_FILENAME);
        $normalizedBaseName = Str::of(Str::transliterate($baseName))
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->value();

        if ($normalizedBaseName === '') {
            $normalizedBaseName = sha1($media->fileName.'|'.$media->fileUrl);
        }

        if ($extension === '') {
            $extension = $this->extensionFromMimeType($contentType)
                ?? $this->extensionFromMimeType($media->mimeType)
                ?? 'img';
        }

        return "{$normalizedBaseName}.{$extension}";
    }

    private function looksLikeImage(?string $mimeType): bool
    {
        return $mimeType !== null && str_starts_with($mimeType, 'image/');
    }

    private function normalizeMimeType(?string $mimeType): ?string
    {
        if ($mimeType === null) {
            return null;
        }

        return Str::of($mimeType)->before(';')->trim()->lower()->value() ?: null;
    }

    private function extensionFromMimeType(?string $mimeType): ?string
    {
        return match ($this->normalizeMimeType($mimeType)) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            'image/bmp' => 'bmp',
            'image/tiff' => 'tiff',
            default => null,
        };
    }

    private function contentLength(Response $response): ?int
    {
        $contentLength = $response->header('Content-Length');

        if (! is_string($contentLength) || ! is_numeric($contentLength)) {
            return null;
        }

        return (int) $contentLength;
    }

    private function logWarning(Product $product, MediaItemData $media, string $message, array $context = []): void
    {
        Log::warning($message, array_merge($context, [
            'product_id' => $product->id,
            'product_external_id' => $product->external_id,
            'media_external_id' => $media->externalId,
            'file_url' => $media->fileUrl,
        ]));
    }
}
