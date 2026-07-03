<?php

namespace Tests\Feature\Media;

use App\Domain\Banner\Models\Banner;
use App\Domain\Media\Jobs\ConvertModelImageToWebpJob;
use App\Domain\Media\Services\ImageToWebpConverter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesTestImages;
use Tests\TestCase;

final class ConvertModelImageToWebpJobTest extends TestCase
{
    use CreatesTestImages;
    use RefreshDatabase;

    public function test_it_replaces_banner_image_from_jpg_to_webp(): void
    {
        $this->skipIfWebpEncodingUnavailable();

        Storage::fake('public');
        Queue::fake();

        $banner = $this->createBannerWithoutObservers('banners/main.jpg');

        Storage::disk('public')->put('banners/main.jpg', $this->createJpegBytes());

        $job = new ConvertModelImageToWebpJob(Banner::class, $banner->getKey(), 'image', 'public', 'banners/main.jpg');

        $job->handle(app(ImageToWebpConverter::class));

        self::assertSame('banners/main.webp', $banner->fresh()->image);
        Storage::disk('public')->assertExists('banners/main.webp');
        Storage::disk('public')->assertMissing('banners/main.jpg');
    }

    public function test_it_replaces_banner_image_from_png_to_webp(): void
    {
        $this->skipIfWebpEncodingUnavailable();

        Storage::fake('public');
        Queue::fake();

        $banner = $this->createBannerWithoutObservers('banners/main.png');

        Storage::disk('public')->put('banners/main.png', $this->createPngBytes());

        $job = new ConvertModelImageToWebpJob(Banner::class, $banner->getKey(), 'image', 'public', 'banners/main.png');

        $job->handle(app(ImageToWebpConverter::class));

        self::assertSame('banners/main.webp', $banner->fresh()->image);
        Storage::disk('public')->assertExists('banners/main.webp');
        Storage::disk('public')->assertMissing('banners/main.png');
    }

    public function test_it_deletes_original_only_after_successful_replacement_when_flag_enabled(): void
    {
        $this->skipIfWebpEncodingUnavailable();

        Storage::fake('public');
        Queue::fake();

        config(['images.webp.delete_original_after_conversion' => true]);

        $banner = $this->createBannerWithoutObservers('banners/original.jpg');

        Storage::disk('public')->put('banners/original.jpg', $this->createJpegBytes());

        (new ConvertModelImageToWebpJob(Banner::class, $banner->getKey(), 'image', 'public', 'banners/original.jpg'))
            ->handle(app(ImageToWebpConverter::class));

        self::assertSame('banners/original.webp', $banner->fresh()->image);
        Storage::disk('public')->assertExists('banners/original.webp');
        Storage::disk('public')->assertMissing('banners/original.jpg');
    }

    public function test_it_does_not_overwrite_image_when_model_changed_before_job_runs(): void
    {
        Storage::fake('public');
        Queue::fake();

        $banner = $this->createBannerWithoutObservers('banners/old.jpg');

        Storage::disk('public')->put('banners/old.jpg', $this->createJpegBytes());
        Storage::disk('public')->put('banners/new.webp', 'already-webp');

        $job = new ConvertModelImageToWebpJob(Banner::class, $banner->getKey(), 'image', 'public', 'banners/old.jpg');

        Banner::withoutEvents(function () use ($banner): void {
            $banner->forceFill(['image' => 'banners/new.webp'])->save();
        });

        $job->handle(app(ImageToWebpConverter::class));

        self::assertSame('banners/new.webp', $banner->fresh()->image);
        Storage::disk('public')->assertExists('banners/old.jpg');
        Storage::disk('public')->assertMissing('banners/old.webp');
    }

    public function test_it_exits_without_error_when_model_is_deleted(): void
    {
        Storage::fake('public');
        Queue::fake();

        $banner = $this->createBannerWithoutObservers('banners/main.jpg');

        Storage::disk('public')->put('banners/main.jpg', $this->createJpegBytes());

        $job = new ConvertModelImageToWebpJob(Banner::class, $banner->getKey(), 'image', 'public', 'banners/main.jpg');

        $banner->forceDelete();

        $job->handle(app(ImageToWebpConverter::class));

        self::assertDatabaseMissing('banners', ['id' => $banner->getKey()]);
        Storage::disk('public')->assertMissing('banners/main.webp');
    }

    public function test_it_logs_warning_and_keeps_database_unchanged_when_source_file_is_missing(): void
    {
        Storage::fake('public');
        Queue::fake();
        Log::spy();

        $banner = $this->createBannerWithoutObservers('banners/missing.jpg');

        $job = new ConvertModelImageToWebpJob(Banner::class, $banner->getKey(), 'image', 'public', 'banners/missing.jpg');

        $job->handle(app(ImageToWebpConverter::class));

        self::assertSame('banners/missing.jpg', $banner->fresh()->image);

        Log::shouldHaveReceived('warning')
            ->withArgs(function (string $message, array $context) use ($banner): bool {
                return $message === 'model image WebP conversion failed'
                    && $context['model'] === Banner::class
                    && $context['model_key'] === $banner->getKey()
                    && $context['attribute'] === 'image'
                    && $context['disk'] === 'public'
                    && $context['source_path'] === 'banners/missing.jpg';
            })
            ->once();
    }

    private function createBannerWithoutObservers(string $imagePath): Banner
    {
        return Banner::withoutEvents(fn (): Banner => Banner::query()->create([
            'image' => $imagePath,
            'title' => 'Main banner',
            'sort_order' => 1,
            'is_active' => true,
        ]));
    }
}
