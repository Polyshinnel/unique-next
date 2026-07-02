<?php

namespace Tests\Feature\Seo;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductStatus;
use App\Domain\Seo\Jobs\GenerateSitemapJob;
use App\Domain\Shipment\Models\Shipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class GenerateSitemapCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_queues_sitemap_generation_job(): void
    {
        Queue::fake();

        $this->artisan('seo:generate-sitemap')
            ->expectsOutput('GenerateSitemapJob поставлен в очередь.')
            ->assertSuccessful();

        Queue::assertPushed(GenerateSitemapJob::class);
    }

    public function test_sync_command_generates_sitemap_in_public_storage(): void
    {
        Storage::fake('public');

        config([
            'app.url' => 'https://uniqset.test',
            'sitemap.disk' => 'public',
            'sitemap.path' => 'sitemap.xml',
        ]);

        $saleStatus = ProductStatus::query()->create(['name' => 'В продаже']);
        $hiddenStatus = ProductStatus::query()->create(['name' => 'Продан']);

        $root = Category::query()->create([
            'name' => 'Металлообработка',
            'slug' => 'metalloobrabotka',
        ]);
        $child = Category::query()->create([
            'name' => 'Токарные станки',
            'slug' => 'tokarnye-stanki',
            'parent_id' => $root->id,
        ]);
        $emptyCategory = Category::query()->create([
            'name' => 'Пустая категория',
            'slug' => 'pustaya-kategoriya',
        ]);

        Product::query()->create([
            'name' => 'Публичный станок',
            'sku' => 'UNQ-16K20',
            'title' => 'Публичный станок',
            'category_id' => $child->id,
            'product_status_id' => $saleStatus->id,
            'published_at' => now(),
        ]);

        Product::query()->create([
            'name' => 'Скрытый станок',
            'sku' => 'UNQ-HIDDEN',
            'title' => 'Скрытый станок',
            'category_id' => $emptyCategory->id,
            'product_status_id' => $hiddenStatus->id,
            'published_at' => now(),
        ]);

        Product::query()->create([
            'name' => 'Черновик',
            'sku' => 'UNQ-DRAFT',
            'title' => 'Черновик',
            'category_id' => $child->id,
            'product_status_id' => $saleStatus->id,
            'published_at' => null,
        ]);

        Shipment::query()->create([
            'title' => 'Активная отгрузка',
            'slug' => 'aktivnaya-otgruzka',
            'shipment_date' => '2026-07-01',
            'is_active' => true,
        ]);

        Shipment::query()->create([
            'title' => 'Активная отгрузка без slug',
            'shipment_date' => '2026-07-02',
            'is_active' => true,
        ]);

        Shipment::query()->create([
            'title' => 'Скрытая отгрузка',
            'slug' => 'skrytaya-otgruzka',
            'shipment_date' => '2026-07-03',
            'is_active' => false,
        ]);

        $this->artisan('seo:generate-sitemap --sync')
            ->expectsOutput('Sitemap успешно сгенерирован.')
            ->assertSuccessful();

        Storage::disk('public')->assertExists('sitemap.xml');

        $xml = Storage::disk('public')->get('sitemap.xml');

        $this->assertStringContainsString('<loc>https://uniqset.test/</loc>', $xml);
        $this->assertStringContainsString('<loc>https://uniqset.test/catalog</loc>', $xml);
        $this->assertStringContainsString('<loc>https://uniqset.test/catalog/metalloobrabotka</loc>', $xml);
        $this->assertStringContainsString('<loc>https://uniqset.test/catalog/metalloobrabotka/tokarnye-stanki</loc>', $xml);
        $this->assertStringContainsString('<loc>https://uniqset.test/catalog/metalloobrabotka/tokarnye-stanki/unq-16k20</loc>', $xml);
        $this->assertStringContainsString('<loc>https://uniqset.test/otgruzki</loc>', $xml);
        $this->assertStringContainsString('<loc>https://uniqset.test/otgruzki/aktivnaya-otgruzka</loc>', $xml);
        $this->assertMatchesRegularExpression('/<loc>https:\\/\\/uniqset\\.test\\/otgruzki\\/\\d+<\\/loc>/', $xml);
        $this->assertStringNotContainsString('https://uniqset.test/catalog/pustaya-kategoriya', $xml);
        $this->assertStringNotContainsString('https://uniqset.test/catalog/pustaya-kategoriya/unq-hidden', $xml);
        $this->assertStringNotContainsString('https://uniqset.test/catalog/metalloobrabotka/tokarnye-stanki/unq-draft', $xml);
        $this->assertStringNotContainsString('https://uniqset.test/otgruzki/skrytaya-otgruzka', $xml);
    }
}
