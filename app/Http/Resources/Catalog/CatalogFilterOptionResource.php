<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CatalogFilterOptionResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $option = [
            'id' => (int) data_get($this->resource, 'id'),
            'name' => data_get($this->resource, 'name'),
            'count' => (int) (data_get($this->resource, 'count') ?? data_get($this->resource, 'products_count') ?? 0),
            'href' => data_get($this->resource, 'href'),
        ];

        if ($this->hasValue('slug')) {
            $option['slug'] = data_get($this->resource, 'slug');
        }

        if ($this->hasValue('level')) {
            $option['level'] = (int) data_get($this->resource, 'level');
        }

        if ($this->hasValue('children')) {
            $option['children'] = array_map(
                fn (mixed $child): array => (new self($child))->resolve($request),
                data_get($this->resource, 'children') ?? [],
            );
        }

        return $option;
    }

    private function hasValue(string $key): bool
    {
        if (is_array($this->resource)) {
            return array_key_exists($key, $this->resource);
        }

        return data_get($this->resource, $key) !== null;
    }
}
