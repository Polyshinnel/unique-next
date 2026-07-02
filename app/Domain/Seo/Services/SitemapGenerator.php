<?php

namespace App\Domain\Seo\Services;

use App\Domain\Catalog\Actions\ResolveCatalogProductHrefAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Support\CatalogQuery;
use App\Domain\Catalog\Support\CategoryTreeBuilder;
use App\Domain\Shipment\Models\Shipment;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

final class SitemapGenerator
{
    /**
     * @var list<string>
     */
    private const STATIC_PATHS = [
        '/',
        '/services',
        '/services/prodazha-oborudovaniya',
        '/services/vykup',
        '/services/prodazha-instrumenta',
        '/services/import-oborudovaniya',
        '/catalog',
        '/otgruzki',
        '/about',
        '/contacts',
        '/why-we',
        '/vacancy',
        '/ohrana-truda',
        '/private-policy',
    ];

    public function __construct(
        private readonly CatalogQuery $catalog = new CatalogQuery,
        private readonly CategoryTreeBuilder $categories = new CategoryTreeBuilder,
        private readonly ResolveCatalogProductHrefAction $productHref = new ResolveCatalogProductHrefAction,
    ) {}

    public function generate(): SitemapGenerationResult
    {
        $products = $this->publicProducts();
        $shipments = $this->publicShipments();
        $categoryUrls = $this->categoryUrls($products);
        $productUrls = $this->productUrls($products);
        $shipmentUrls = $this->shipmentUrls($shipments);
        $staticUrls = $this->staticUrls();
        $urls = [...$staticUrls, ...$categoryUrls, ...$productUrls, ...$shipmentUrls];
        $xml = $this->buildXml($urls);

        Storage::disk((string) config('sitemap.disk'))->put(
            (string) config('sitemap.path'),
            $xml,
        );

        return new SitemapGenerationResult(
            urlCount: count($urls),
            categoryCount: count($categoryUrls),
            productCount: count($productUrls),
            shipmentCount: count($shipmentUrls),
            filePath: $this->storagePath(),
        );
    }

    /**
     * @return Collection<int, Product>
     */
    private function publicProducts(): Collection
    {
        return $this->catalog
            ->baseQuery()
            ->with('category')
            ->orderBy('products.id')
            ->get([
                'products.id',
                'products.slug',
                'products.category_id',
                'products.updated_at',
            ]);
    }

    /**
     * @return Collection<int, Shipment>
     */
    private function publicShipments(): Collection
    {
        return Shipment::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('shipment_date')
            ->orderByDesc('id')
            ->get([
                'id',
                'slug',
                'updated_at',
            ]);
    }

    /**
     * @param  Collection<int, Product>  $products
     * @return list<array{loc: string, lastmod: string|null}>
     */
    private function categoryUrls(Collection $products): array
    {
        /** @var array<int, int> $directCounts */
        $directCounts = $products
            ->filter(fn (Product $product): bool => $product->category_id !== null)
            ->countBy(fn (Product $product): int => (int) $product->category_id)
            ->map(fn (int $count): int => $count)
            ->all();

        $urls = [];

        foreach ($this->categories->tree() as $node) {
            array_push($urls, ...$this->categoryUrlBranch($node, $directCounts));
        }

        return $urls;
    }

    /**
     * @param  array<string, mixed>  $node
     * @param  array<int, int>  $directCounts
     * @return list<array{loc: string, lastmod: string|null}>
     */
    private function categoryUrlBranch(array $node, array $directCounts): array
    {
        $urls = [];

        foreach ($node['children'] ?? [] as $child) {
            array_push($urls, ...$this->categoryUrlBranch($child, $directCounts));
        }

        if ($this->categoryProductCount($node, $directCounts) === 0) {
            return $urls;
        }

        array_unshift($urls, [
            'loc' => $this->absoluteUrl((string) $node['href']),
            'lastmod' => null,
        ]);

        return $urls;
    }

    /**
     * @param  array<string, mixed>  $node
     * @param  array<int, int>  $directCounts
     */
    private function categoryProductCount(array $node, array $directCounts): int
    {
        $count = (int) ($directCounts[(int) $node['id']] ?? 0);

        foreach ($node['children'] ?? [] as $child) {
            $count += $this->categoryProductCount($child, $directCounts);
        }

        return $count;
    }

    /**
     * @param  Collection<int, Product>  $products
     * @return list<array{loc: string, lastmod: string|null}>
     */
    private function productUrls(Collection $products): array
    {
        return $products
            ->map(fn (Product $product): array => [
                'loc' => $this->absoluteUrl($this->productHref->execute($product)),
                'lastmod' => $this->formatLastModified($product->updated_at),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Shipment>  $shipments
     * @return list<array{loc: string, lastmod: string|null}>
     */
    private function shipmentUrls(Collection $shipments): array
    {
        return $shipments
            ->map(fn (Shipment $shipment): array => [
                'loc' => $this->absoluteUrl($this->shipmentHref($shipment)),
                'lastmod' => $this->formatLastModified($shipment->updated_at),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{loc: string, lastmod: string|null}>
     */
    private function staticUrls(): array
    {
        return array_map(
            fn (string $path): array => ['loc' => $this->absoluteUrl($path), 'lastmod' => null],
            self::STATIC_PATHS,
        );
    }

    /**
     * @param  list<array{loc: string, lastmod: string|null}>  $urls
     */
    private function buildXml(array $urls): string
    {
        $xml = new \XMLWriter;
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->setIndent(true);
        $xml->startElement('urlset');
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($urls as $url) {
            $xml->startElement('url');
            $xml->writeElement('loc', $url['loc']);

            if ($url['lastmod'] !== null) {
                $xml->writeElement('lastmod', $url['lastmod']);
            }

            $xml->endElement();
        }

        $xml->endElement();
        $xml->endDocument();

        return $xml->outputMemory();
    }

    private function absoluteUrl(string $path): string
    {
        $baseUrl = rtrim((string) config('app.url'), '/');

        return $baseUrl.'/'.ltrim($path, '/');
    }

    private function formatLastModified(mixed $value): ?string
    {
        if (! $value instanceof CarbonInterface) {
            return null;
        }

        return $value->copy()->utc()->toAtomString();
    }

    private function shipmentHref(Shipment $shipment): string
    {
        $routeKey = filled($shipment->slug) ? $shipment->slug : (string) $shipment->getKey();

        return '/otgruzki/'.$routeKey;
    }

    private function storagePath(): string
    {
        return Storage::disk((string) config('sitemap.disk'))
            ->path((string) config('sitemap.path'));
    }
}
