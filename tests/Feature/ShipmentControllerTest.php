<?php

namespace Tests\Feature;

use App\Domain\Shipment\Models\Shipment;
use App\Domain\Shipment\Models\ShipmentImage;
use App\Domain\Shipment\Models\ShipmentTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ShipmentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_shipments_endpoint_returns_active_shipments_with_related_data(): void
    {
        $hidden = Shipment::query()->create([
            'title' => 'Hidden shipment',
            'shipment_date' => '2024-02-01',
            'is_active' => false,
        ]);

        ShipmentImage::query()->create([
            'shipment_id' => $hidden->id,
            'file_path' => 'shipments/hidden.jpg',
            'is_main' => true,
        ]);

        $shipment = Shipment::query()->create([
            'title' => 'Visible shipment',
            'slug' => 'visible-shipment',
            'seo_title' => 'Visible shipment SEO',
            'seo_description' => 'Visible shipment SEO description.',
            'shipment_date' => '2024-01-29',
            'location' => 'Республика Беларусь',
            'short_description' => 'Короткое описание отгрузки.',
            'description' => "Первый абзац.\n\nВторой абзац.",
            'sort_order' => 10,
            'is_active' => true,
        ]);

        ShipmentImage::query()->create([
            'shipment_id' => $shipment->id,
            'file_path' => 'shipments/visible-main.jpg',
            'is_main' => true,
            'sort_order' => 20,
        ]);

        ShipmentImage::query()->create([
            'shipment_id' => $shipment->id,
            'file_path' => 'shipments/visible-gallery.jpg',
            'sort_order' => 10,
        ]);

        $tag = ShipmentTag::query()->create(['name' => 'Экспорт']);
        $shipment->tags()->attach($tag);

        $response = $this->getJson('/api/shipments');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $shipment->id)
            ->assertJsonPath('data.0.title', 'Visible shipment')
            ->assertJsonPath('data.0.slug', 'visible-shipment')
            ->assertJsonPath('data.0.seoTitle', 'Visible shipment SEO')
            ->assertJsonPath('data.0.seoDescription', 'Visible shipment SEO description.')
            ->assertJsonPath('data.0.date', '29.01.2024')
            ->assertJsonPath('data.0.location', 'Республика Беларусь')
            ->assertJsonPath('data.0.image', '/storage/shipments/visible-main.jpg')
            ->assertJsonPath('data.0.galleryImages.0', '/storage/shipments/visible-gallery.jpg')
            ->assertJsonPath('data.0.galleryImages.1', '/storage/shipments/visible-main.jpg')
            ->assertJsonPath('data.0.tags.0', 'Экспорт')
            ->assertJsonPath('data.0.content.0', 'Первый абзац.')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_shipment_detail_endpoint_does_not_return_inactive_shipments(): void
    {
        $shipment = Shipment::query()->create([
            'title' => 'Inactive shipment',
            'slug' => 'inactive-shipment',
            'shipment_date' => '2024-01-29',
            'is_active' => false,
        ]);

        $this->getJson("/api/shipments/{$shipment->id}")
            ->assertNotFound();
    }

    public function test_shipment_detail_endpoint_supports_slug_and_id_fallback(): void
    {
        $withSlug = Shipment::query()->create([
            'title' => 'Shipment with slug',
            'slug' => 'shipment-with-slug',
            'shipment_date' => '2024-01-29',
            'is_active' => true,
        ]);

        $withoutSlug = Shipment::query()->create([
            'title' => 'Shipment without slug',
            'shipment_date' => '2024-01-30',
            'is_active' => true,
        ]);

        $this->getJson('/api/shipments/shipment-with-slug')
            ->assertOk()
            ->assertJsonPath('slug', 'shipment-with-slug')
            ->assertJsonPath('seoTitle', null)
            ->assertJsonPath('seoDescription', null)
            ->assertJsonPath('id', $withSlug->id);

        $this->getJson("/api/shipments/{$withoutSlug->id}")
            ->assertOk()
            ->assertJsonPath('slug', null)
            ->assertJsonPath('id', $withoutSlug->id);
    }
}
