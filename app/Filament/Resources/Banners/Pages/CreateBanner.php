<?php

namespace App\Filament\Resources\Banners\Pages;

use App\Filament\Resources\Banners\BannerResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateBanner extends CreateRecord
{
    protected static string $resource = BannerResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;
}
