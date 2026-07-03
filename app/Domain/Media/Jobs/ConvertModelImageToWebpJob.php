<?php

namespace App\Domain\Media\Jobs;

use App\Domain\Media\Exceptions\ImageConversionException;
use App\Domain\Media\Services\ImageToWebpConverter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

final class ConvertModelImageToWebpJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly string $modelClass,
        public readonly int|string $modelKey,
        public readonly string $attribute,
        public readonly string $disk,
        public readonly string $sourcePath,
    ) {
        $this->onQueue((string) config('images.webp.queue'));
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(ImageToWebpConverter $converter): void
    {
        $model = $this->resolveModel();

        if ($model === null) {
            return;
        }

        if ($this->attributeValue($model) !== $this->sourcePath) {
            return;
        }

        if (! $converter->shouldConvertPath($this->sourcePath)) {
            return;
        }

        try {
            $webpPath = $converter->convertStoredImage($this->disk, $this->sourcePath);
        } catch (ImageConversionException $exception) {
            Log::warning('model image WebP conversion failed', [
                'model' => $this->modelClass,
                'model_key' => $this->modelKey,
                'attribute' => $this->attribute,
                'disk' => $this->disk,
                'source_path' => $this->sourcePath,
                'error' => $exception->getMessage(),
            ]);

            return;
        }

        $freshModel = $this->resolveModel();

        if ($freshModel === null) {
            return;
        }

        if ($this->attributeValue($freshModel) !== $this->sourcePath) {
            return;
        }

        $this->modelClass::withoutEvents(function () use ($freshModel, $webpPath): void {
            $freshModel->forceFill([
                $this->attribute => $webpPath,
            ])->save();
        });

        $this->deleteOriginalIfUnused($webpPath);
    }

    private function resolveModel(): ?Model
    {
        /** @var Model|null $model */
        $model = $this->modelClass::query()->find($this->modelKey);

        return $model;
    }

    private function attributeValue(Model $model): mixed
    {
        return $model->getAttribute($this->attribute);
    }

    private function deleteOriginalIfUnused(string $webpPath): void
    {
        if (! config('images.webp.delete_original_after_conversion')) {
            return;
        }

        if ($this->sourcePath === $webpPath) {
            return;
        }

        if ($this->modelClass::query()->where($this->attribute, $this->sourcePath)->exists()) {
            return;
        }

        Storage::disk($this->disk)->delete($this->sourcePath);
    }
}
