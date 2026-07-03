<?php

namespace App\Domain\Banner\Observers;

use App\Domain\Banner\Models\Banner;
use App\Domain\Media\Jobs\ConvertModelImageToWebpJob;
use App\Domain\Media\Services\ImageToWebpConverter;
use Illuminate\Support\Facades\Storage;

final class BannerObserver
{
    public function created(Banner $banner): void
    {
        $this->dispatchConversionIfNeeded($banner->image, $banner);
    }

    public function updated(Banner $banner): void
    {
        if (! $banner->wasChanged('image')) {
            return;
        }

        $this->dispatchConversionIfNeeded($banner->image, $banner);
    }

    public function deleted(Banner $banner): void
    {
        // Do not delete files on soft delete.
    }

    public function forceDeleted(Banner $banner): void
    {
        if (! is_string($banner->image) || $banner->image === '') {
            return;
        }

        Storage::disk('public')->delete($banner->image);
    }

    private function dispatchConversionIfNeeded(?string $path, Banner $banner): void
    {
        if (! app(ImageToWebpConverter::class)->shouldConvertPath($path)) {
            return;
        }

        ConvertModelImageToWebpJob::dispatch(
            modelClass: Banner::class,
            modelKey: $banner->getKey(),
            attribute: 'image',
            disk: 'public',
            sourcePath: $path,
        )->afterCommit();
    }
}
