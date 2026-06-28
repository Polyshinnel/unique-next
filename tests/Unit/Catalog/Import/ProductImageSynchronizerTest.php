<?php

namespace Tests\Unit\Catalog\Import;

use App\Domain\Catalog\Import\Data\MediaItemData;
use App\Domain\Catalog\Import\Services\ImageDownloader;
use App\Domain\Catalog\Import\Services\ProductImageSynchronizer;
use App\Domain\Catalog\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ProductImageSynchronizerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_reorders_existing_removes_orphans_and_downloads_new_images(): void
    {
        Storage::fake('public');
        Http::fake([
            'https://example.com/4.jpg' => Http::response('image-4', 200, [
                'Content-Type' => 'image/jpeg',
                'Content-Length' => '7',
            ]),
        ]);

        $product = Product::query()->create(['name' => 'CASE CX210', 'title' => 'CASE CX210']);

        foreach ([1, 2, 3] as $externalId) {
            $path = "products/{$product->id}/{$externalId}.jpg";
            Storage::disk('public')->put($path, "image-{$externalId}");

            $product->images()->create([
                'external_id' => $externalId,
                'file_name' => "{$externalId}.jpg",
                'file_path' => $path,
                'file_url' => "https://example.com/{$externalId}.jpg",
                'mime_type' => 'image/jpeg',
                'file_size' => 7,
                'sort_order' => $externalId,
                'is_main' => $externalId === 1,
            ]);
        }

        $media = [
            $this->media(3, sortOrder: 1, isMain: true),
            $this->media(2, sortOrder: 2, isMain: false),
            $this->media(4, sortOrder: 3, isMain: false),
        ];

        $stats = $this->synchronizer()->sync($product, $media);

        self::assertSame(1, $stats['downloaded']);
        self::assertSame(1, $stats['deleted']);
        Http::assertSentCount(1);

        // Orphan (external_id=1) removed from DB and disk.
        self::assertDatabaseMissing('product_images', [
            'product_id' => $product->id,
            'external_id' => 1,
        ]);
        Storage::disk('public')->assertMissing("products/{$product->id}/1.jpg");

        // New image (external_id=4) downloaded and stored.
        self::assertDatabaseHas('product_images', [
            'product_id' => $product->id,
            'external_id' => 4,
        ]);
        Storage::disk('public')->assertExists("products/{$product->id}/4_4.jpg");

        // Existing images reordered without re-downloading.
        $image3 = $product->images()->where('external_id', 3)->firstOrFail();
        self::assertSame(1, $image3->sort_order);
        self::assertTrue($image3->is_main);

        $image2 = $product->images()->where('external_id', 2)->firstOrFail();
        self::assertSame(2, $image2->sort_order);
        self::assertFalse($image2->is_main);

        // Exactly one main image.
        self::assertSame(1, $product->images()->where('is_main', true)->count());
        self::assertSame(3, $product->images()->count());
    }

    public function test_it_is_idempotent_on_a_second_run_without_feed_changes(): void
    {
        Storage::fake('public');
        Http::fake([
            'https://example.com/1.jpg' => Http::response('image-1', 200, [
                'Content-Type' => 'image/jpeg',
                'Content-Length' => '7',
            ]),
        ]);

        $product = Product::query()->create(['name' => 'CASE CX210', 'title' => 'CASE CX210']);
        $media = [$this->media(1, sortOrder: 1, isMain: true)];

        $first = $this->synchronizer()->sync($product, $media);
        self::assertSame(1, $first['downloaded']);

        Http::fake();

        $second = $this->synchronizer()->sync($product, $media);

        self::assertSame(0, $second['downloaded']);
        self::assertSame(0, $second['deleted']);
        self::assertSame(0, $second['reordered']);
        Http::assertNothingSent();
        self::assertSame(1, $product->images()->count());
    }

    public function test_it_promotes_first_image_to_main_when_feed_has_no_main(): void
    {
        Storage::fake('public');
        Http::fake([
            'https://example.com/1.jpg' => Http::response('image-1', 200, ['Content-Type' => 'image/jpeg']),
            'https://example.com/2.jpg' => Http::response('image-2', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $product = Product::query()->create(['name' => 'CASE CX210', 'title' => 'CASE CX210']);
        $media = [
            $this->media(2, sortOrder: 1, isMain: false),
            $this->media(1, sortOrder: 2, isMain: false),
        ];

        $this->synchronizer()->sync($product, $media);

        self::assertSame(1, $product->images()->where('is_main', true)->count());
        $main = $product->images()->where('is_main', true)->firstOrFail();
        self::assertSame(2, $main->external_id);
        self::assertSame(1, $main->sort_order);
    }

    public function test_it_removes_all_images_when_feed_has_no_media(): void
    {
        Storage::fake('public');
        Http::fake();

        $product = Product::query()->create(['name' => 'CASE CX210', 'title' => 'CASE CX210']);
        $path = "products/{$product->id}/1.jpg";
        Storage::disk('public')->put($path, 'image-1');
        $product->images()->create([
            'external_id' => 1,
            'file_name' => '1.jpg',
            'file_path' => $path,
            'file_url' => 'https://example.com/1.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 7,
            'sort_order' => 1,
            'is_main' => true,
        ]);

        $stats = $this->synchronizer()->sync($product, []);

        self::assertSame(1, $stats['deleted']);
        self::assertSame(0, $product->images()->count());
        Storage::disk('public')->assertMissing($path);
    }

    private function synchronizer(): ProductImageSynchronizer
    {
        return new ProductImageSynchronizer(new ImageDownloader());
    }

    private function media(int $externalId, int $sortOrder, bool $isMain): MediaItemData
    {
        return new MediaItemData(
            externalId: $externalId,
            fileName: "{$externalId}.jpg",
            fileUrl: "https://example.com/{$externalId}.jpg",
            mimeType: 'image/jpeg',
            fileSize: 7,
            sortOrder: $sortOrder,
            isMain: $isMain,
        );
    }
}
