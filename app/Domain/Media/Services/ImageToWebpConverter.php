<?php

namespace App\Domain\Media\Services;

use App\Domain\Media\Exceptions\ImageConversionException;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image;
use Throwable;

final class ImageToWebpConverter
{
    public function shouldConvertPath(?string $path): bool
    {
        if ($path === null || $path === '') {
            return false;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'webp' || $extension === '') {
            return false;
        }

        /** @var array<int, string> $convertExtensions */
        $convertExtensions = config('images.webp.convert_extensions', []);

        return in_array($extension, $convertExtensions, true);
    }

    public function buildWebpPath(string $path): string
    {
        $directory = pathinfo($path, PATHINFO_DIRNAME);
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $webpFileName = "{$filename}.webp";

        if ($directory === '' || $directory === '.') {
            return $webpFileName;
        }

        return "{$directory}/{$webpFileName}";
    }

    public function convertStoredImage(
        string $disk,
        string $sourcePath,
        ?string $targetPath = null,
    ): string {
        $storage = Storage::disk($disk);

        if (! $storage->exists($sourcePath)) {
            throw new ImageConversionException(
                "Image file [{$sourcePath}] was not found on disk [{$disk}].",
            );
        }

        $targetPath ??= $this->buildWebpPath($sourcePath);

        try {
            $contents = $storage->get($sourcePath);
            $image = Image::decode($contents);
            $encoded = $image->encode(new WebpEncoder(
                quality: (int) config('images.webp.quality'),
            ));
        } catch (Throwable $exception) {
            throw new ImageConversionException(
                "Failed to convert image [{$sourcePath}] to WebP on disk [{$disk}].",
                previous: $exception,
            );
        }

        if (! $storage->put($targetPath, (string) $encoded, ['visibility' => 'public'])) {
            throw new ImageConversionException(
                "Failed to store converted WebP image [{$targetPath}] on disk [{$disk}].",
            );
        }

        return $targetPath;
    }
}
