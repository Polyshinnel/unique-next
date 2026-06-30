<?php

namespace Tests\Unit\Catalog\Import;

use App\Domain\Catalog\Import\Data\MediaItemData;
use App\Domain\Catalog\Import\Services\ImageDownloader;
use App\Domain\Catalog\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ImageDownloaderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_downloads_an_image_and_persists_product_image_row(): void
    {
        Storage::fake('public');
        Http::fake([
            'https://example.com/main.jpg' => Http::response('image-binary', 200, [
                'Content-Type' => 'image/jpeg',
                'Content-Length' => '12',
            ]),
        ]);

        $product = Product::query()->create(['name' => 'CASE CX210', 'title' => 'CASE CX210']);
        $media = new MediaItemData(
            externalId: 9001,
            fileName: 'main.jpg',
            fileUrl: 'https://example.com/main.jpg',
            mimeType: 'image/jpeg',
            fileSize: null,
            sortOrder: 1,
            isMain: true,
        );

        $image = (new ImageDownloader)->download($product, $media);

        self::assertNotNull($image);
        self::assertSame($product->id, $image->product_id);
        self::assertSame(9001, $image->external_id);
        self::assertSame('main.jpg', $image->file_name);
        self::assertSame('https://example.com/main.jpg', $image->file_url);
        self::assertSame('image/jpeg', $image->mime_type);
        self::assertSame(12, $image->file_size);
        self::assertTrue($image->is_main);
        self::assertSame(1, $image->sort_order);
        self::assertSame("products/{$product->id}/9001_main.jpg", $image->file_path);

        Storage::disk('public')->assertExists($image->file_path);
        self::assertDatabaseHas('product_images', [
            'product_id' => $product->id,
            'external_id' => 9001,
            'file_path' => "products/{$product->id}/9001_main.jpg",
        ]);
    }

    public function test_it_returns_existing_image_without_downloading_again_when_file_exists(): void
    {
        Storage::fake('public');
        Http::fake();

        $product = Product::query()->create(['name' => 'CASE CX210', 'title' => 'CASE CX210']);
        $path = "products/{$product->id}/9001_main.jpg";

        Storage::disk('public')->put($path, 'existing-image');

        $existingImage = $product->images()->create([
            'external_id' => 9001,
            'file_name' => 'main.jpg',
            'file_path' => $path,
            'file_url' => 'https://example.com/main.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 12,
            'sort_order' => 1,
            'is_main' => true,
        ]);

        $media = new MediaItemData(
            externalId: 9001,
            fileName: 'main.jpg',
            fileUrl: 'https://example.com/main.jpg',
            mimeType: 'image/jpeg',
            fileSize: 12,
            sortOrder: 1,
            isMain: true,
        );

        $image = (new ImageDownloader)->download($product, $media);

        self::assertNotNull($image);
        self::assertTrue($image->is($existingImage));
        Http::assertNothingSent();
        self::assertDatabaseCount('product_images', 1);
    }

    public function test_it_updates_existing_image_metadata_without_redownloading_when_file_exists(): void
    {
        Storage::fake('public');
        Http::fake();

        $product = Product::query()->create(['name' => 'CASE CX210', 'title' => 'CASE CX210']);
        $path = "products/{$product->id}/9001_main.jpg";

        Storage::disk('public')->put($path, 'existing-image');

        $product->images()->create([
            'external_id' => 9001,
            'file_name' => 'main.jpg',
            'file_path' => $path,
            'file_url' => 'https://example.com/main.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 12,
            'sort_order' => 1,
            'is_main' => true,
        ]);

        $media = new MediaItemData(
            externalId: 9001,
            fileName: 'main-renamed.jpg',
            fileUrl: 'https://example.com/main.jpg',
            mimeType: 'image/jpeg',
            fileSize: 13,
            sortOrder: 0,
            isMain: false,
        );

        $image = (new ImageDownloader)->download($product, $media);

        self::assertNotNull($image);
        self::assertSame('main-renamed.jpg', $image->file_name);
        self::assertSame(13, $image->file_size);
        self::assertSame(0, $image->sort_order);
        self::assertFalse($image->is_main);
        Http::assertNothingSent();
    }

    public function test_it_logs_and_skips_invalid_downloads_without_failing_the_import(): void
    {
        Storage::fake('public');
        Log::spy();
        Http::fake([
            'https://example.com/not-image.txt' => Http::response('plain-text', 200, [
                'Content-Type' => 'text/plain',
            ]),
        ]);

        $product = Product::query()->create(['name' => 'CASE CX210', 'title' => 'CASE CX210']);
        $media = new MediaItemData(
            externalId: 9002,
            fileName: 'manual.txt',
            fileUrl: 'https://example.com/not-image.txt',
            mimeType: 'image/jpeg',
            fileSize: null,
            sortOrder: 2,
            isMain: false,
        );

        $image = (new ImageDownloader)->download($product, $media);

        self::assertNull($image);
        Storage::disk('public')->assertDirectoryEmpty("products/{$product->id}");
        self::assertDatabaseCount('product_images', 0);

        Log::shouldHaveReceived('warning')
            ->withArgs(function (string $message, array $context) use ($product): bool {
                return $message === 'Catalog image download skipped non-image response.'
                    && $context['product_id'] === $product->id
                    && $context['media_external_id'] === 9002;
            })
            ->once();
    }
}
