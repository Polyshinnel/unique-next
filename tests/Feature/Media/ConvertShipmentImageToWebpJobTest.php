<?php

namespace Tests\Feature\Media;

use App\Domain\Media\Jobs\ConvertModelImageToWebpJob;
use App\Domain\Media\Services\ImageToWebpConverter;
use App\Domain\Shipment\Models\Shipment;
use App\Domain\Shipment\Models\ShipmentImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesTestImages;
use Tests\TestCase;

final class ConvertShipmentImageToWebpJobTest extends TestCase
{
    use CreatesTestImages;
    use RefreshDatabase;

    public function test_it_replaces_shipment_image_path_and_file_name_from_jpg_to_webp(): void
    {
        $this->skipIfWebpEncodingUnavailable();

        Storage::fake('public');
        Queue::fake();

        $image = $this->createShipmentImageWithoutObservers('shipments/main.jpg');

        Storage::disk('public')->put('shipments/main.jpg', $this->createJpegBytes());

        $job = new ConvertModelImageToWebpJob(ShipmentImage::class, $image->getKey(), 'file_path', 'public', 'shipments/main.jpg');

        $job->handle(app(ImageToWebpConverter::class));

        self::assertSame('shipments/main.webp', $image->fresh()->file_path);
        self::assertSame('main.webp', $image->fresh()->file_name);
        Storage::disk('public')->assertExists('shipments/main.webp');
        Storage::disk('public')->assertMissing('shipments/main.jpg');
    }

    private function createShipmentImageWithoutObservers(string $path): ShipmentImage
    {
        $shipment = Shipment::query()->create([
            'title' => 'Shipment',
            'shipment_date' => '2024-01-29',
            'is_active' => true,
        ]);

        return ShipmentImage::withoutEvents(fn (): ShipmentImage => ShipmentImage::query()->create([
            'shipment_id' => $shipment->getKey(),
            'file_path' => $path,
            'is_main' => true,
        ]));
    }
}
