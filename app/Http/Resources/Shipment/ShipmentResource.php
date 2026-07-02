<?php

namespace App\Http\Resources\Shipment;

use App\Domain\Shipment\Models\Shipment;
use App\Domain\Shipment\Models\ShipmentImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

final class ShipmentResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Shipment $shipment */
        $shipment = $this->resource;
        $images = $this->images($shipment);
        $summary = $shipment->short_description ?: $this->firstParagraph($shipment->description);

        return [
            'id' => (int) $shipment->getKey(),
            'title' => $shipment->title,
            'slug' => $shipment->slug,
            'seoTitle' => $shipment->seo_title,
            'seoDescription' => $shipment->seo_description,
            'date' => $shipment->shipment_date?->format('d.m.Y'),
            'location' => $shipment->location,
            'image' => $this->imageUrl($shipment->mainImage) ?: ($images[0] ?? '/assets/img/otgruzki-banner.JPEG'),
            'summary' => $summary ?: '',
            'tags' => $shipment->tags
                ->pluck('name')
                ->filter()
                ->values()
                ->all(),
            'galleryImages' => $images,
            'content' => $this->content($shipment->description, $summary),
        ];
    }

    /**
     * @return list<string>
     */
    private function images(Shipment $shipment): array
    {
        return $shipment->images
            ->sortBy([
                ['sort_order', 'asc'],
                ['id', 'asc'],
            ])
            ->map(fn (ShipmentImage $image): ?string => $this->imageUrl($image))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private function content(?string $description, ?string $summary): array
    {
        $paragraphs = Collection::make(preg_split('/\R{2,}/u', trim((string) $description)) ?: [])
            ->map(static fn (string $paragraph): string => trim($paragraph))
            ->filter()
            ->values()
            ->all();

        if ($paragraphs !== []) {
            return $paragraphs;
        }

        return filled($summary) ? [$summary] : [];
    }

    private function firstParagraph(?string $text): ?string
    {
        $paragraph = Collection::make(preg_split('/\R{2,}/u', trim((string) $text)) ?: [])
            ->map(static fn (string $paragraph): string => trim($paragraph))
            ->first(static fn (string $paragraph): bool => $paragraph !== '');

        return is_string($paragraph) ? $paragraph : null;
    }

    private function imageUrl(?ShipmentImage $image): ?string
    {
        $path = $image?->file_path;

        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return '/storage/'.ltrim($path, '/');
    }
}
