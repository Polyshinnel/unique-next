<?php

namespace Tests\Feature;

use App\Domain\Banner\Models\Banner;
use App\Domain\Media\Jobs\ConvertModelImageToWebpJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class BannerObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_conversion_job_after_banner_creation_for_convertible_image(): void
    {
        Queue::fake();

        $banner = Banner::query()->create([
            'image' => 'banners/main.jpg',
            'title' => 'Main banner',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Queue::assertPushed(ConvertModelImageToWebpJob::class, function (ConvertModelImageToWebpJob $job) use ($banner): bool {
            return $job->modelClass === Banner::class
                && $job->modelKey === $banner->getKey()
                && $job->attribute === 'image'
                && $job->disk === 'public'
                && $job->sourcePath === 'banners/main.jpg';
        });
    }

    public function test_it_dispatches_conversion_job_after_banner_creation_for_png_image(): void
    {
        Queue::fake();

        $banner = Banner::query()->create([
            'image' => 'banners/main.png',
            'title' => 'Main banner',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Queue::assertPushed(ConvertModelImageToWebpJob::class, function (ConvertModelImageToWebpJob $job) use ($banner): bool {
            return $job->modelKey === $banner->getKey()
                && $job->sourcePath === 'banners/main.png';
        });
    }

    public function test_it_does_not_dispatch_conversion_job_for_webp_banner_creation(): void
    {
        Queue::fake();

        Banner::query()->create([
            'image' => 'banners/main.webp',
            'title' => 'Main banner',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Queue::assertNothingPushed();
    }

    public function test_it_dispatches_conversion_job_only_when_image_changes_to_convertible_path(): void
    {
        Queue::fake();

        $banner = Banner::query()->create([
            'image' => 'banners/main.webp',
            'title' => 'Main banner',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Queue::assertNothingPushed();

        $banner->update([
            'title' => 'Updated title',
        ]);

        Queue::assertNothingPushed();

        $banner->update([
            'image' => 'banners/updated.png',
        ]);

        Queue::assertPushed(ConvertModelImageToWebpJob::class, function (ConvertModelImageToWebpJob $job) use ($banner): bool {
            return $job->modelKey === $banner->getKey()
                && $job->sourcePath === 'banners/updated.png';
        });
    }

    public function test_it_deletes_image_file_on_force_delete(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('banners/main.webp', 'image-bytes');

        $banner = Banner::query()->create([
            'image' => 'banners/main.webp',
            'title' => 'Main banner',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $banner->forceDelete();

        Storage::disk('public')->assertMissing('banners/main.webp');
    }
}
