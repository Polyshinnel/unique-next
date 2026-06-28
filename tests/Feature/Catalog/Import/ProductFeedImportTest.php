<?php

namespace Tests\Feature\Catalog\Import;

use App\Domain\Catalog\Import\Services\ProductFeedImporter;
use App\Domain\Catalog\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ProductFeedImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_fixture_idempotently_without_creating_duplicates(): void
    {
        Storage::fake('public');
        $this->fakeFixtureFeed();

        /** @var ProductFeedImporter $importer */
        $importer = app(ProductFeedImporter::class);

        $firstRun = $importer->import('https://panel.uniqset.com/feed.xml');
        $secondRun = $importer->import('https://panel.uniqset.com/feed.xml');

        self::assertSame(1, $firstRun->created);
        self::assertSame(0, $firstRun->skipped);
        self::assertSame(0, $firstRun->failed);
        self::assertSame(0, $secondRun->created);
        self::assertSame(1, $secondRun->skipped);
        self::assertSame(0, $secondRun->failed);

        self::assertDatabaseCount('categories', 1);
        self::assertDatabaseCount('product_statuses', 1);
        self::assertDatabaseCount('check_statuses', 1);
        self::assertDatabaseCount('shipment_statuses', 1);
        self::assertDatabaseCount('products', 1);
        self::assertDatabaseCount('regions', 1);
        self::assertDatabaseCount('tags', 2);
        self::assertDatabaseCount('product_images', 2);
        self::assertDatabaseCount('product_region', 1);
        self::assertDatabaseCount('product_tag', 2);
    }

    public function test_it_renames_reference_records_without_creating_new_rows(): void
    {
        Storage::fake('public');
        Http::fake([
            'panel.uniqset.com/*' => Http::sequence()
                ->push($this->fixtureXml(), 200, ['Content-Type' => 'application/xml'])
                ->push($this->renamedFixtureXml(), 200, ['Content-Type' => 'application/xml']),
            '*' => Http::response('fake-image-bytes', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        /** @var ProductFeedImporter $importer */
        $importer = app(ProductFeedImporter::class);
        $importer->import('https://panel.uniqset.com/feed.xml');

        $result = $importer->import('https://panel.uniqset.com/feed.xml');

        self::assertSame(0, $result->created);
        self::assertSame(1, $result->skipped);
        self::assertSame(0, $result->failed);

        self::assertDatabaseCount('categories', 1);
        self::assertDatabaseCount('product_statuses', 1);
        self::assertDatabaseHas('categories', [
            'external_id' => 10,
            'name' => 'Гусеничные экскаваторы',
            'slug' => 'ekskavatory',
        ]);
        self::assertDatabaseHas('product_statuses', [
            'external_id' => 4,
            'name' => 'Опубликован повторно',
        ]);
    }

    public function test_it_keeps_product_import_successful_when_one_image_download_fails(): void
    {
        Storage::fake('public');

        Http::fake([
            'panel.uniqset.com/*' => Http::response($this->fixtureXml(), 200, [
                'Content-Type' => 'application/xml',
            ]),
            'https://example.com/main.jpg' => Http::response('fake-main-image', 200, [
                'Content-Type' => 'image/jpeg',
            ]),
            'https://example.com/extra.jpg' => Http::response('', 500),
        ]);

        /** @var ProductFeedImporter $importer */
        $importer = app(ProductFeedImporter::class);
        $result = $importer->import('https://panel.uniqset.com/feed.xml');

        self::assertSame(1, $result->created);
        self::assertSame(0, $result->skipped);
        self::assertSame(0, $result->failed);

        /** @var Product $product */
        $product = Product::query()->firstOrFail();

        self::assertDatabaseCount('products', 1);
        self::assertDatabaseCount('product_images', 1);
        self::assertDatabaseHas('product_images', [
            'product_id' => $product->id,
            'external_id' => 9001,
            'is_main' => true,
        ]);
        self::assertDatabaseMissing('product_images', [
            'product_id' => $product->id,
            'external_id' => 9002,
        ]);
        Storage::disk('public')->assertExists("products/{$product->id}/9001_main.jpg");
        Storage::disk('public')->assertMissing("products/{$product->id}/9002_extra.jpg");
    }

    private function fakeFixtureFeed(): void
    {
        Http::fake([
            'panel.uniqset.com/*' => fn () => Http::response($this->fixtureXml(), 200, [
                'Content-Type' => 'application/xml',
            ]),
            '*' => Http::response('fake-image-bytes', 200, ['Content-Type' => 'image/jpeg']),
        ]);
    }

    private function fixtureXml(): string
    {
        return file_get_contents(base_path('tests/Fixtures/advertisements.xml'));
    }

    private function renamedFixtureXml(): string
    {
        return str_replace(
            [
                "<category id=\"10\">\n            <name>Экскаваторы</name>\n            <parent_id>2</parent_id>\n        </category>",
                "<status id=\"4\">\n            <name>Опубликовано</name>\n            <color>#00ff00</color>\n        </status>",
            ],
            [
                "<category id=\"10\">\n            <name>Гусеничные экскаваторы</name>\n            <parent_id>2</parent_id>\n        </category>",
                "<status id=\"4\">\n            <name>Опубликован повторно</name>\n            <color>#00ff00</color>\n        </status>",
            ],
            $this->fixtureXml(),
        );
    }
}
