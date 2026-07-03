<?php

namespace Tests\Unit\Media;

use App\Domain\Media\Exceptions\ImageConversionException;
use App\Domain\Media\Services\ImageToWebpConverter;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesTestImages;
use Tests\TestCase;

final class ImageToWebpConverterTest extends TestCase
{
    use CreatesTestImages;

    public function test_it_converts_jpg_image_to_webp(): void
    {
        $this->skipIfWebpEncodingUnavailable();

        Storage::fake('public');

        $sourcePath = 'banners/main.jpg';

        Storage::disk('public')->put($sourcePath, $this->createJpegBytes());

        $webpPath = app(ImageToWebpConverter::class)->convertStoredImage('public', $sourcePath);

        self::assertSame('banners/main.webp', $webpPath);
        self::assertStringStartsWith('banners/', $webpPath);
        Storage::disk('public')->assertExists($webpPath);
        $this->assertWebpSignature(Storage::disk('public')->get($webpPath));
    }

    public function test_it_converts_jpeg_image_to_webp(): void
    {
        $this->skipIfWebpEncodingUnavailable();

        Storage::fake('public');

        $sourcePath = 'banners/main.jpeg';

        Storage::disk('public')->put($sourcePath, $this->createJpegBytes());

        $webpPath = app(ImageToWebpConverter::class)->convertStoredImage('public', $sourcePath);

        self::assertSame('banners/main.webp', $webpPath);
        Storage::disk('public')->assertExists($webpPath);
        $this->assertWebpSignature(Storage::disk('public')->get($webpPath));
    }

    public function test_it_converts_uppercase_jpeg_extension_to_webp(): void
    {
        $this->skipIfWebpEncodingUnavailable();

        Storage::fake('public');

        $sourcePath = 'banners/main.JPEG';

        Storage::disk('public')->put($sourcePath, $this->createJpegBytes());

        self::assertTrue(app(ImageToWebpConverter::class)->shouldConvertPath($sourcePath));

        $webpPath = app(ImageToWebpConverter::class)->convertStoredImage('public', $sourcePath);

        self::assertSame('banners/main.webp', $webpPath);
        Storage::disk('public')->assertExists($webpPath);
        $this->assertWebpSignature(Storage::disk('public')->get($webpPath));
    }

    public function test_it_converts_png_image_to_webp(): void
    {
        $this->skipIfWebpEncodingUnavailable();

        Storage::fake('public');

        $sourcePath = 'banners/main.png';

        Storage::disk('public')->put($sourcePath, $this->createPngBytes());

        $webpPath = app(ImageToWebpConverter::class)->convertStoredImage('public', $sourcePath);

        self::assertSame('banners/main.webp', $webpPath);
        Storage::disk('public')->assertExists($webpPath);
        $this->assertWebpSignature(Storage::disk('public')->get($webpPath));
    }

    public function test_it_does_not_require_conversion_for_webp_path(): void
    {
        self::assertFalse(app(ImageToWebpConverter::class)->shouldConvertPath('banners/main.webp'));
    }

    public function test_it_throws_when_source_bytes_are_not_a_valid_image(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('banners/broken.jpg', 'not-an-image');

        $this->expectException(ImageConversionException::class);

        app(ImageToWebpConverter::class)->convertStoredImage('public', 'banners/broken.jpg');
    }

    private function assertWebpSignature(string $contents): void
    {
        self::assertSame('RIFF', substr($contents, 0, 4));
        self::assertSame('WEBP', substr($contents, 8, 4));
    }
}
