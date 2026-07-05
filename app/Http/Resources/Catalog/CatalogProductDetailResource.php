<?php

namespace App\Http\Resources\Catalog;

use App\Domain\Catalog\Actions\ResolveCatalogProductHrefAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductImage;
use App\Domain\Catalog\Support\CategoryTreeBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CatalogProductDetailResource extends JsonResource
{
    public static $wrap = null;

    private CategoryTreeBuilder $categories;

    private ResolveCatalogProductHrefAction $href;

    public function __construct(
        $resource,
        ?CategoryTreeBuilder $categories = null,
        ?ResolveCatalogProductHrefAction $href = null,
    ) {
        parent::__construct($resource);

        $this->categories = $categories ?? new CategoryTreeBuilder;
        $this->href = $href ?? new ResolveCatalogProductHrefAction($this->categories);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Product $product */
        $product = $this->resource;
        $card = (new CatalogProductCardResource($product, $this->categories, $this->href))->resolve($request);

        return [
            ...$card,
            'description' => $this->utf8($product->description),
            'summary' => $this->utf8($product->description),
            'canonicalHref' => $card['href'],
            'manager' => $this->manager($product),
            'images' => $this->images($product),
            'tags' => $product->tags->map(fn ($tag): string => $this->utf8($tag->name) ?? '')->values()->all(),
            'characteristicBlocks' => $this->characteristicBlocks($product),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function manager(Product $product): ?array
    {
        if ($product->manager === null) {
            return null;
        }

        return [
            'id' => (int) $product->manager->getKey(),
            'name' => $this->utf8($product->manager->name),
            'phone' => $this->utf8($product->manager->phone),
            'email' => $this->utf8($product->manager->email),
            'socialLinks' => [
                'vk' => $this->utf8($product->manager->vk),
                'max' => $this->utf8($product->manager->max),
                'telegram' => $this->utf8($product->manager->telegram),
            ],
        ];
    }

    /**
     * @return list<string>
     */
    private function images(Product $product): array
    {
        $images = $product->images
            ->map(fn (ProductImage $image): string => $this->imageUrl($image))
            ->filter()
            ->values()
            ->all();

        return $images === [] ? [$this->pathUrl($product->og_image)] : $images;
    }

    /**
     * @return list<array{title: string, contentHtml: string}>
     */
    private function characteristicBlocks(Product $product): array
    {
        $isSold = $this->isSoldProduct($product);
        $blocks = collect([
            ['title' => 'Основные характеристики', 'content' => $product->mainCharacteristics?->content],
            ['title' => 'Основная информация', 'content' => $product->mainInfo?->content],
            ['title' => 'Комплектация', 'content' => $isSold ? null : $product->complectation?->content],
            ['title' => 'Технические характеристики', 'content' => $isSold ? null : $product->technicalCharacteristics?->content],
            ['title' => 'Условия продажи', 'content' => $isSold ? null : $this->saleConditionsHtml($product)],
            ['title' => 'Проверка', 'content' => $isSold ? null : $this->statusBlockHtml($product->check?->status?->name, $product->check?->comment)],
            ['title' => 'Демонтаж', 'content' => $isSold ? null : $this->statusBlockHtml($product->dismantling?->status?->name, $product->dismantling?->comment)],
            ['title' => 'Погрузка', 'content' => $isSold ? null : $this->statusBlockHtml($product->loading?->status?->name, $product->loading?->comment)],
            ['title' => 'Дополнительная информация', 'content' => $isSold ? null : $product->additionalInfo?->content],
        ]);

        return $blocks
            ->filter(fn (array $block): bool => filled($block['content']))
            ->map(fn (array $block): array => [
                'title' => $block['title'],
                'contentHtml' => trim($this->utf8((string) $block['content']) ?? ''),
            ])
            ->values()
            ->all();
    }

    private function saleConditionsHtml(Product $product): string
    {
        $price = $this->salePriceLabel($product);
        $comment = $this->utf8($product->price_comment);

        return collect([
            '<p class="product-sale-price"><strong>Цена:</strong> '.e($price).'</p>',
            $this->labeledHtml('Комментарий', $comment),
        ])->filter()->implode('');
    }

    private function statusBlockHtml(?string $status, ?string $comment): ?string
    {
        $status = $this->utf8($status);
        $comment = $this->utf8($comment);

        if (! filled($status) && ! filled($comment)) {
            return null;
        }

        return collect([
            filled($status) ? '<p><strong>Статус:</strong> '.e($status).'</p>' : null,
            $this->labeledHtml('Комментарий', $comment),
        ])->filter()->implode('');
    }

    private function labeledHtml(string $label, ?string $content): ?string
    {
        if (! filled($content)) {
            return null;
        }

        if (preg_match('/<\s*(p|div|ul|ol|table|blockquote|h[1-6])\b/i', $content) === 1) {
            return '<div><strong>'.e($label).':</strong></div>'.$content;
        }

        return '<p><strong>'.e($label).':</strong> '.$content.'</p>';
    }

    private function utf8(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (! mb_check_encoding($value, 'UTF-8')) {
            $value = strtr($value, [
                "\xD7" => '×',
                "\xF5" => 'х',
            ]);
        }

        return mb_scrub($value, 'UTF-8');
    }

    private function salePriceLabel(Product $product): string
    {
        if ($this->isSoldProduct($product)) {
            return 'Продано';
        }

        if ($product->productStatus?->name === 'Резерв') {
            return 'Резерв';
        }

        if ((bool) $product->show_price && $product->price !== null) {
            return number_format((float) $product->price, 0, '.', ' ').' ₽';
        }

        return 'По запросу';
    }

    private function isSoldProduct(Product $product): bool
    {
        return ! in_array($product->productStatus?->name, ['В продаже', 'Резерв'], true);
    }

    private function imageUrl(?ProductImage $image): string
    {
        return $this->pathUrl($image?->file_path ?: $image?->file_url);
    }

    private function pathUrl(?string $path): string
    {
        if ($path === null || $path === '') {
            return '/assets/img/catalog.jpeg';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return '/storage/'.ltrim($path, '/');
    }
}
