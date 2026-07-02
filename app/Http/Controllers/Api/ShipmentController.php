<?php

namespace App\Http\Controllers\Api;

use App\Domain\Shipment\Models\Shipment;
use App\Http\Controllers\Controller;
use App\Http\Resources\Shipment\ShipmentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ShipmentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = max(1, min((int) $request->integer('per_page', 8), 24));

        $shipments = Shipment::query()
            ->where('is_active', true)
            ->with([
                'tags:id,name',
                'mainImage:id,shipment_id,file_path,is_main,sort_order',
                'images:id,shipment_id,file_path,is_main,sort_order',
            ])
            ->orderBy('sort_order')
            ->orderByDesc('shipment_date')
            ->orderByDesc('id')
            ->paginate($perPage);

        return ShipmentResource::collection($shipments);
    }

    public function show(string $shipment): JsonResponse
    {
        $shipment = Shipment::query()
            ->where('is_active', true)
            ->where(static function (Builder $query) use ($shipment): void {
                $query->where('slug', $shipment);

                if (ctype_digit($shipment)) {
                    $query->orWhereKey((int) $shipment);
                }
            })
            ->with([
                'tags:id,name',
                'mainImage:id,shipment_id,file_path,is_main,sort_order',
                'images:id,shipment_id,file_path,is_main,sort_order',
            ])
            ->firstOrFail();

        return (new ShipmentResource($shipment))->response();
    }
}
