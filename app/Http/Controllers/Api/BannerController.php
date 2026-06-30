<?php

namespace App\Http\Controllers\Api;

use App\Domain\Banner\Models\Banner;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class BannerController extends Controller
{
    public function index(): JsonResponse
    {
        $banners = Banner::query()
            ->active()
            ->orderBy('id')
            ->get([
                'id',
                'image',
                'title',
                'text',
                'button_one_text',
                'button_one_url',
                'button_two_text',
                'button_two_url',
            ])
            ->map(fn (Banner $banner): array => [
                'id' => $banner->id,
                'image' => $banner->image,
                'title' => $banner->title,
                'text' => $banner->text,
                'button_one_text' => $banner->button_one_text,
                'button_one_url' => $banner->button_one_url,
                'button_two_text' => $banner->button_two_text,
                'button_two_url' => $banner->button_two_url,
            ]);

        return response()->json($banners);
    }
}
