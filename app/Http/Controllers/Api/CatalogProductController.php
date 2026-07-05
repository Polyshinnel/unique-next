<?php

namespace App\Http\Controllers\Api;

use App\Domain\Catalog\Support\CatalogQuery;
use App\Http\Controllers\Controller;
use App\Http\Resources\Catalog\CatalogProductDetailResource;
use Illuminate\Http\JsonResponse;

final class CatalogProductController extends Controller
{
    public function show(string $product, CatalogQuery $catalog): JsonResponse
    {
        $product = $catalog
            ->detailQuery()
            ->with([
                'category.parent',
                'images',
                'mainImage',
                'productStatus',
                'equipmentAvailability',
                'equipmentState',
                'regions',
                'region',
                'manager',
                'tags',
                'mainCharacteristics',
                'complectation',
                'technicalCharacteristics',
                'mainInfo',
                'additionalInfo',
                'check.status',
                'dismantling.status',
                'loading.status',
            ])
            ->where(function ($query) use ($product): void {
                $query->where('slug', $product);

                if (ctype_digit($product)) {
                    $query->orWhere('id', (int) $product);
                }
            })
            ->firstOrFail();

        return (new CatalogProductDetailResource($product))->response();
    }
}
