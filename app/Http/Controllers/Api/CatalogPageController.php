<?php

namespace App\Http\Controllers\Api;

use App\Domain\Catalog\Actions\BuildCatalogPageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\CatalogPageRequest;
use App\Http\Resources\Catalog\CatalogPageResource;
use Illuminate\Http\JsonResponse;

final class CatalogPageController extends Controller
{
    public function show(CatalogPageRequest $request, BuildCatalogPageAction $action): JsonResponse
    {
        return (new CatalogPageResource($action->execute($request->normalized())))->response();
    }
}
