<?php

namespace Tests\Feature;

use App\Domain\Media\Jobs\ConvertModelImageToWebpJob;
use App\Domain\Shipment\Models\Shipment;
use App\Domain\Shipment\Models\ShipmentImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ShipmentImageObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_conversion_job_after_shipment_image_creation_for_convertible_image(): void
    {
        Queue::fake();

        $shipment = $this->createShipment();
        $image = ShipmentImage::query()->create([
            'shipment_id' => $shipment->getKey(),
            'file_path' => 'shipments/main.jpg',
            'is_main' => true,
        ]);

        Queue::assertPushed(ConvertModelImageToWebpJob::class, function (ConvertModelImageToWebpJob $job) use ($image): bool {
            return $job->modelClass === ShipmentImage::class
                && $job->modelKey === $image->getKey()
                && $job->attribute === 'file_path'
                && $job->disk === 'public'
                && $job->sourcePath === 'shipments/main.jpg';
        });
    }

    public function test_it_does_not_dispatch_conversion_job_for_webp_shipment_image_creation(): void
    {
        Queue::fake();

        ShipmentImage::query()->create([
            'shipment_id' => $this->createShipment()->getKey(),
            'file_path' => 'shipments/main.webp',
            'is_main' => true,
        ]);

        Queue::assertNothingPushed();
    }

    public function test_it_dispatches_conversion_job_only_when_file_path_changes_to_convertible_path(): void
    {
        Queue::fake();

        $image = ShipmentImage::query()->create([
            'shipment_id' => $this->createShipment()->getKey(),
            'file_path' => 'shipments/main.webp',
            'is_main' => true,
        ]);

        Queue::assertNothingPushed();

        $image->update([
            'is_main' => false,
        ]);

        Queue::assertNothingPushed();

        $image->update([
            'file_path' => 'shipments/updated.png',
        ]);

        Queue::assertPushed(ConvertModelImageToWebpJob::class, function (ConvertModelImageToWebpJob $job) use ($image): bool {
            return $job->modelKey === $image->getKey()
                && $job->sourcePath === 'shipments/updated.png';
        });
    }

    public function test_it_deletes_image_file_when_shipment_image_is_deleted(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('shipments/main.webp', 'image-bytes');

        $image = ShipmentImage::query()->create([
            'shipment_id' => $this->createShipment()->getKey(),
            'file_path' => 'shipments/main.webp',
            'is_main' => true,
        ]);

        $image->delete();

        Storage::disk('public')->assertMissing('shipments/main.webp');
    }

    private function createShipment(): Shipment
    {
        return Shipment::query()->create([
            'title' => 'Shipment',
            'shipment_date' => '2024-01-29',
            'is_active' => true,
        ]);
    }
}
