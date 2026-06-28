<?php

namespace App\Http\Controllers\Api;

use App\Domain\Seo\Models\PageSeo;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

final class PageSeoController extends Controller
{
    public function byKey(string $key): PageSeo
    {
        return PageSeo::query()
            ->where('key', $key)
            ->where('is_active', true)
            ->firstOrFail();
    }

    public function byPath(Request $request): PageSeo
    {
        $rawPath = $request->query('path', '/');

        if (! is_string($rawPath)) {
            throw (new ModelNotFoundException())->setModel(PageSeo::class);
        }

        $path = $this->normalizePath($rawPath);

        return PageSeo::query()
            ->where('path', $path)
            ->where('is_active', true)
            ->firstOrFail();
    }

    private function normalizePath(string $path): string
    {
        $normalizedPath = parse_url($path, PHP_URL_PATH);

        if (! is_string($normalizedPath) || $normalizedPath === '') {
            return '/';
        }

        $normalizedPath = '/'.ltrim($normalizedPath, '/');
        $normalizedPath = rtrim($normalizedPath, '/');

        return $normalizedPath === '' ? '/' : $normalizedPath;
    }
}
