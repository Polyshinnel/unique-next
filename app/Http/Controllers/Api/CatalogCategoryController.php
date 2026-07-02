<?php

namespace App\Http\Controllers\Api;

use App\Domain\Catalog\Support\CategoryTreeBuilder;
use App\Http\Controllers\Controller;
use App\Http\Resources\Catalog\CatalogCategoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CatalogCategoryController extends Controller
{
    public function byPath(Request $request, CategoryTreeBuilder $categories): JsonResponse
    {
        $path = $this->normalizePath($request->query('path'));

        if ($path === null) {
            throw new NotFoundHttpException('Catalog category path is required.');
        }

        $category = $categories->resolvePath($path);

        if ($category === null) {
            throw new NotFoundHttpException('Catalog category not found.');
        }

        return (new CatalogCategoryResource($category))->response();
    }

    private function normalizePath(mixed $path): ?string
    {
        if (! is_string($path)) {
            return null;
        }

        $path = trim($path);
        $path = trim($path, '/');

        return $path === '' ? null : $path;
    }
}
