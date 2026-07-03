<?php

namespace Tests\Support;

trait CreatesTestImages
{
    protected function skipIfWebpEncodingUnavailable(): void
    {
        $gdInfo = function_exists('gd_info') ? gd_info() : [];
        $supportsWebp = function_exists('imagewebp')
            && (($gdInfo['WebP Support'] ?? false) === true || (int) ($gdInfo['WebP Support'] ?? 0) === 1);

        if ($supportsWebp) {
            return;
        }

        self::markTestSkipped('GD WebP support is unavailable in the current container. Re-run after step 13.');
    }

    protected function createJpegBytes(int $width = 8, int $height = 8): string
    {
        return $this->createImageBytes('jpeg', $width, $height);
    }

    protected function createPngBytes(int $width = 8, int $height = 8): string
    {
        return $this->createImageBytes('png', $width, $height);
    }

    private function createImageBytes(string $format, int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);

        if ($image === false) {
            self::fail('Failed to create GD image resource.');
        }

        $background = imagecolorallocate($image, 12, 100, 180);

        if ($background === false) {
            imagedestroy($image);
            self::fail('Failed to allocate GD image color.');
        }

        imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $background);

        ob_start();

        $written = match ($format) {
            'jpeg' => imagejpeg($image),
            'png' => imagepng($image),
            default => false,
        };

        $contents = ob_get_clean();

        imagedestroy($image);

        if ($written !== true || ! is_string($contents)) {
            self::fail("Failed to generate {$format} image bytes.");
        }

        return $contents;
    }
}
