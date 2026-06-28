<?php

namespace App\Domain\Catalog\Import\Services;

use App\Domain\Catalog\Import\Data\MediaItemData;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductImage;
use Illuminate\Support\Facades\DB;

/**
 * Brings a product's images in line with the full media set from the feed:
 *  - removes images that disappeared (DB row + file on disk);
 *  - downloads newly appeared images;
 *  - updates order (sort_order) and main flag (is_main) of existing images;
 *  - guarantees exactly one main image.
 *
 * File deletions are deferred to after the surrounding DB transaction commits
 * (DB::afterCommit runs immediately when there is no active transaction), so an
 * aborted update never leaves a product without its on-disk files.
 */
final class ProductImageSynchronizer
{
    public function __construct(private ImageDownloader $downloader) {}

    /**
     * @param list<MediaItemData> $media
     *
     * @return array{downloaded: int, deleted: int, reordered: int}
     */
    public function sync(Product $product, array $media): array
    {
        $deleted = $this->deleteOrphans($product, $media);

        $downloaded = 0;
        $reordered = 0;

        foreach ($media as $item) {
            /** @var null|ProductImage $existing */
            $existing = $product->images()
                ->where('external_id', $item->externalId)
                ->first();

            if ($this->needsDownload($existing, $item)) {
                if ($existing !== null && $existing->file_url !== $item->fileUrl) {
                    $this->scheduleFileDeletion($existing->file_path);
                }

                $downloadedImage = $this->downloader->download($product, $item);

                if ($downloadedImage !== null) {
                    $downloaded++;
                    $existing = $downloadedImage;
                }
            }

            if ($existing !== null && $this->applyOrder($existing, $item)) {
                $reordered++;
            }
        }

        $this->ensureSingleMain($product);

        return [
            'downloaded' => $downloaded,
            'deleted' => $deleted,
            'reordered' => $reordered,
        ];
    }

    /**
     * @param list<MediaItemData> $media
     */
    private function deleteOrphans(Product $product, array $media): int
    {
        $feedIds = array_map(
            static fn (MediaItemData $item): int => $item->externalId,
            $media,
        );

        /** @var \Illuminate\Database\Eloquent\Collection<int, ProductImage> $orphans */
        $orphans = $product->images()
            ->when($feedIds !== [], fn ($query) => $query->whereNotIn('external_id', $feedIds))
            ->get();

        foreach ($orphans as $orphan) {
            $this->scheduleFileDeletion($orphan->file_path);
            $orphan->delete();
        }

        return $orphans->count();
    }

    private function needsDownload(?ProductImage $existing, MediaItemData $item): bool
    {
        if ($existing === null) {
            return true;
        }

        if ($existing->file_path === null || $existing->file_url !== $item->fileUrl) {
            return true;
        }

        return ! $this->downloader->fileExists($existing->file_path);
    }

    private function applyOrder(ProductImage $image, MediaItemData $item): bool
    {
        $image->fill([
            'sort_order' => $item->sortOrder,
            'is_main' => $item->isMain,
            'file_name' => $item->fileName,
        ]);

        if (! $image->isDirty()) {
            return false;
        }

        $image->save();

        return true;
    }

    private function ensureSingleMain(Product $product): void
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, ProductImage> $images */
        $images = $product->images()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($images->isEmpty()) {
            return;
        }

        $main = $images->firstWhere('is_main', true) ?? $images->first();

        foreach ($images as $image) {
            $shouldBeMain = $image->is($main);

            if ($image->is_main !== $shouldBeMain) {
                $image->forceFill(['is_main' => $shouldBeMain])->save();
            }
        }
    }

    private function scheduleFileDeletion(?string $filePath): void
    {
        if ($filePath === null || $filePath === '') {
            return;
        }

        $delete = fn () => $this->downloader->deleteFile($filePath);

        if (DB::transactionLevel() > 0) {
            DB::afterCommit($delete);

            return;
        }

        $delete();
    }
}
