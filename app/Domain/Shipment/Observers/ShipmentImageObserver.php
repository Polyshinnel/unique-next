<?php

namespace App\Domain\Shipment\Observers;

use App\Domain\Media\Jobs\ConvertModelImageToWebpJob;
use App\Domain\Media\Services\ImageToWebpConverter;
use App\Domain\Shipment\Models\ShipmentImage;
use Illuminate\Support\Facades\Storage;

final class ShipmentImageObserver
{
    public function created(ShipmentImage $image): void
    {
        $this->dispatchConversionIfNeeded($image->file_path, $image);
    }

    public function updated(ShipmentImage $image): void
    {
        if (! $image->wasChanged('file_path')) {
            return;
        }

        $this->dispatchConversionIfNeeded($image->file_path, $image);
    }

    public function deleted(ShipmentImage $image): void
    {
        if (! is_string($image->file_path) || $image->file_path === '') {
            return;
        }

        Storage::disk('public')->delete($image->file_path);
    }

    private function dispatchConversionIfNeeded(?string $path, ShipmentImage $image): void
    {
        if (! app(ImageToWebpConverter::class)->shouldConvertPath($path)) {
            return;
        }

        ConvertModelImageToWebpJob::dispatch(
            modelClass: ShipmentImage::class,
            modelKey: $image->getKey(),
            attribute: 'file_path',
            disk: 'public',
            sourcePath: $path,
        )->afterCommit();
    }
}
