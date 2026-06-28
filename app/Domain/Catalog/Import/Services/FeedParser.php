<?php

namespace App\Domain\Catalog\Import\Services;

use App\Domain\Catalog\Import\Data\AdvertisementData;
use App\Domain\Catalog\Import\Data\CategoryData;
use App\Domain\Catalog\Import\Data\ManagerData;
use App\Domain\Catalog\Import\Data\MediaItemData;
use App\Domain\Catalog\Import\Data\RegionData;
use App\Domain\Catalog\Import\Data\StatusData;
use App\Domain\Catalog\Import\Data\StatusWithCommentData;
use DOMDocument;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;
use Throwable;
use XMLReader;

final class FeedParser
{
    /**
     * @return iterable<CategoryData>
     */
    public function categories(string $path): iterable
    {
        return $this->iterateTopLevelItems(
            $path,
            'categories',
            'category',
            fn (SimpleXMLElement $node): CategoryData => $this->mapCategory($node),
            'category'
        );
    }

    /**
     * @return iterable<StatusData>
     */
    public function advertisementStatuses(string $path): iterable
    {
        return $this->iterateTopLevelItems(
            $path,
            'advertisement_statuses',
            'status',
            fn (SimpleXMLElement $node): StatusData => $this->mapStatus($node),
            'advertisement status'
        );
    }

    /**
     * @return iterable<StatusData>
     */
    public function checkStatuses(string $path): iterable
    {
        return $this->iterateTopLevelItems(
            $path,
            'check_statuses',
            'status',
            fn (SimpleXMLElement $node): StatusData => $this->mapStatus($node),
            'check status'
        );
    }

    /**
     * @return iterable<StatusData>
     */
    public function installStatuses(string $path): iterable
    {
        return $this->iterateTopLevelItems(
            $path,
            'install_statuses',
            'status',
            fn (SimpleXMLElement $node): StatusData => $this->mapStatus($node),
            'install status'
        );
    }

    /**
     * @return iterable<AdvertisementData>
     */
    public function advertisements(string $path): iterable
    {
        $reader = $this->openReader($path);
        $insideAdvertisementsContainer = false;

        try {
            while ($reader->read()) {
                if ($reader->nodeType !== XMLReader::ELEMENT) {
                    continue;
                }

                if ($reader->depth === 1) {
                    $insideAdvertisementsContainer = $reader->localName === 'advertisements';
                }

                if ($reader->localName !== 'advertisement') {
                    continue;
                }

                $isDirectAdvertisement = $reader->depth === 1;
                $isNestedAdvertisement = $insideAdvertisementsContainer && $reader->depth === 2;

                if (! $isDirectAdvertisement && ! $isNestedAdvertisement) {
                    continue;
                }

                try {
                    yield $this->mapAdvertisement($this->expandCurrentNode($reader));
                } catch (Throwable $exception) {
                    $this->logSkippedNode('advertisement', $path, $exception);
                }
            }
        } finally {
            $reader->close();
        }
    }

    public function exportDate(string $path): ?string
    {
        $reader = $this->openReader($path);

        try {
            while ($reader->read()) {
                if ($reader->nodeType !== XMLReader::ELEMENT || $reader->depth !== 0) {
                    continue;
                }

                return $this->normalizeNullableString($reader->getAttribute('export_date'));
            }

            return null;
        } finally {
            $reader->close();
        }
    }

    /**
     * @template T
     *
     * @param callable(SimpleXMLElement): T $mapper
     * @return iterable<T>
     */
    private function iterateTopLevelItems(
        string $path,
        string $containerElement,
        string $itemElement,
        callable $mapper,
        string $context,
    ): iterable {
        $reader = $this->openReader($path);
        $insideContainer = $containerElement === 'advertisements_export';

        try {
            while ($reader->read()) {
                if ($reader->nodeType !== XMLReader::ELEMENT) {
                    continue;
                }

                if ($containerElement !== 'advertisements_export' && $reader->depth === 1) {
                    $insideContainer = $reader->localName === $containerElement;
                }

                if (! $insideContainer || $reader->localName !== $itemElement) {
                    continue;
                }

                if ($containerElement === 'advertisements_export' && $reader->depth !== 1) {
                    continue;
                }

                if ($containerElement !== 'advertisements_export' && $reader->depth !== 2) {
                    continue;
                }

                try {
                    yield $mapper($this->expandCurrentNode($reader));
                } catch (Throwable $exception) {
                    $this->logSkippedNode($context, $path, $exception);
                }
            }
        } finally {
            $reader->close();
        }
    }

    private function mapCategory(SimpleXMLElement $node): CategoryData
    {
        return new CategoryData(
            externalId: $this->requireIntAttribute($node, 'id'),
            name: $this->requireString($node->name),
            parentExternalId: $this->intOrNull($node->parent_id),
        );
    }

    private function mapStatus(SimpleXMLElement $node): StatusData
    {
        return new StatusData(
            externalId: $this->requireIntAttribute($node, 'id'),
            name: $this->requireString($node->name),
            color: $this->stringOrNull($node->color),
        );
    }

    private function mapAdvertisement(SimpleXMLElement $node): AdvertisementData
    {
        $category = $node->category instanceof SimpleXMLElement ? $node->category : null;
        $status = $node->status instanceof SimpleXMLElement ? $node->status : null;
        $state = $node->product_state instanceof SimpleXMLElement ? $node->product_state : null;
        $availability = $node->product_available instanceof SimpleXMLElement ? $node->product_available : null;
        $price = $node->price instanceof SimpleXMLElement ? $node->price : null;
        $location = $node->location instanceof SimpleXMLElement ? $node->location : null;
        $dates = $node->dates instanceof SimpleXMLElement ? $node->dates : null;

        return new AdvertisementData(
            externalId: $this->requireIntAttribute($node, 'id'),
            name: $this->requireString($node->title),
            sku: $this->stringOrNull($node->sku),
            categoryExternalId: $category ? $this->intAttributeOrNull($category, 'id') : null,
            statusExternalId: $status ? $this->intAttributeOrNull($status, 'id') : null,
            stateExternalId: $state ? $this->intAttributeOrNull($state, 'id') : null,
            stateName: $state ? $this->stringOrNull($state) : null,
            availabilityExternalId: $availability ? $this->intAttributeOrNull($availability, 'id') : null,
            availabilityName: $availability ? $this->stringOrNull($availability) : null,
            price: $price ? $this->stringOrNull($price->adv_price) : null,
            showPrice: $price ? $this->boolFromValue($price->show_price) : false,
            priceComment: $price ? $this->contentOrNull($price->adv_price_comment) : null,
            publishedAt: $dates ? $this->stringOrNull($dates->published_at) : null,
            manager: $this->mapManager($node),
            regions: $this->mapRegions($node),
            tags: $this->mapTags($node),
            media: $this->mapMedia($node),
            check: $this->mapStatusWithComment($node->check),
            loading: $this->mapStatusWithComment($node->loading),
            removal: $this->mapStatusWithComment($node->removal),
            mainCharacteristics: $this->htmlContentOrNull($node->main_characteristics),
            complectation: $this->htmlContentOrNull($node->complectation),
            technicalCharacteristics: $this->htmlContentOrNull($node->technical_characteristics),
            mainInfo: $this->htmlContentOrNull($node->main_info),
            additionalInfo: $this->htmlContentOrNull($node->additional_info),
        );
    }

    private function mapManager(SimpleXMLElement $node): ?ManagerData
    {
        $manager = $node->manager?->product_owner;

        if (! $manager instanceof SimpleXMLElement) {
            return null;
        }

        try {
            return new ManagerData(
                externalId: $this->requireIntAttribute($manager, 'id'),
                name: $this->requireString($manager->name),
                email: $this->stringOrNull($manager->email),
                phone: $this->stringOrNull($manager->phone),
                role: $this->stringOrNull($manager->role),
            );
        } catch (Throwable $exception) {
            Log::warning('Catalog feed parser skipped invalid manager node.', [
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @return list<RegionData>
     */
    private function mapRegions(SimpleXMLElement $node): array
    {
        $regions = [];

        foreach ($node->location?->regions?->region ?? [] as $region) {
            try {
                $regions[] = new RegionData(
                    externalId: $this->requireIntAttribute($region, 'id'),
                    name: $this->requireString($region->name),
                );
            } catch (Throwable $exception) {
                Log::warning('Catalog feed parser skipped invalid region node.', [
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $regions;
    }

    /**
     * @return list<string>
     */
    private function mapTags(SimpleXMLElement $node): array
    {
        $tags = [];

        foreach ($node->tags?->tag ?? [] as $tag) {
            $value = $this->stringOrNull($tag);

            if ($value !== null) {
                $tags[] = $value;
            }
        }

        return $tags;
    }

    /**
     * @return list<MediaItemData>
     */
    private function mapMedia(SimpleXMLElement $node): array
    {
        $mediaItems = [];

        foreach ($node->media?->media_item ?? [] as $mediaItem) {
            try {
                $mediaItems[] = new MediaItemData(
                    externalId: $this->requireIntAttribute($mediaItem, 'id'),
                    fileName: $this->requireString($mediaItem->file_name),
                    fileUrl: $this->requireString($mediaItem->file_url),
                    mimeType: $this->stringOrNull($mediaItem->mime_type),
                    fileSize: $this->intOrNull($mediaItem->file_size),
                    sortOrder: $this->intOrNull($mediaItem->sort_order) ?? 0,
                    isMain: $this->boolFromValue($mediaItem->is_main_image),
                );
            } catch (Throwable $exception) {
                Log::warning('Catalog feed parser skipped invalid media node.', [
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $mediaItems;
    }

    private function mapStatusWithComment(mixed $node): ?StatusWithCommentData
    {
        if (! $node instanceof SimpleXMLElement) {
            return null;
        }

        $name = $this->stringOrNull($node->status);

        if ($name === null) {
            return null;
        }

        return new StatusWithCommentData(
            name: $name,
            comment: $this->contentOrNull($node->comment),
        );
    }

    private function openReader(string $path): XMLReader
    {
        $reader = new XMLReader();
        $reader->open($path, null, LIBXML_COMPACT | LIBXML_NONET | LIBXML_NOCDATA | LIBXML_NOBLANKS);

        return $reader;
    }

    private function expandCurrentNode(XMLReader $reader): SimpleXMLElement
    {
        $node = $reader->expand();
        $document = new DOMDocument('1.0', 'UTF-8');
        $importedNode = $document->importNode($node, true);
        $document->appendChild($importedNode);

        return simplexml_import_dom($importedNode);
    }

    private function requireIntAttribute(SimpleXMLElement $node, string $name): int
    {
        $value = $this->intAttributeOrNull($node, $name);

        if ($value === null) {
            throw new \UnexpectedValueException("Missing required attribute [{$name}].");
        }

        return $value;
    }

    private function intAttributeOrNull(SimpleXMLElement $node, string $name): ?int
    {
        $value = $this->normalizeNullableString((string) $node[$name]);

        if ($value === null) {
            return null;
        }

        if (! is_numeric($value)) {
            throw new \UnexpectedValueException("Attribute [{$name}] must be numeric.");
        }

        return (int) $value;
    }

    private function requireString(mixed $value): string
    {
        $normalized = $this->stringOrNull($value);

        if ($normalized === null) {
            throw new \UnexpectedValueException('Missing required text value.');
        }

        return $normalized;
    }

    private function stringOrNull(mixed $value): ?string
    {
        return $this->normalizeNullableString((string) $value);
    }

    private function contentOrNull(mixed $value): ?string
    {
        $content = (string) $value;

        return trim($content) === '' ? null : $content;
    }

    private function htmlContentOrNull(mixed $value): ?string
    {
        $content = trim((string) $value);

        return $content === '' ? null : $content;
    }

    private function intOrNull(mixed $value): ?int
    {
        $normalized = $this->stringOrNull($value);

        if ($normalized === null) {
            return null;
        }

        if (! is_numeric($normalized)) {
            throw new \UnexpectedValueException('Value must be numeric.');
        }

        return (int) $normalized;
    }

    private function boolFromValue(mixed $value): bool
    {
        $normalized = $this->stringOrNull($value);

        return in_array($normalized, ['1', 'true', 'yes'], true);
    }

    private function normalizeNullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function logSkippedNode(string $context, string $path, Throwable $exception): void
    {
        Log::warning("Catalog feed parser skipped invalid {$context} node.", [
            'path' => $path,
            'error' => $exception->getMessage(),
        ]);
    }
}
