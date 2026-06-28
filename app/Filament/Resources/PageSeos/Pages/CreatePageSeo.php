<?php

namespace App\Filament\Resources\PageSeos\Pages;

use App\Filament\Resources\PageSeos\PageSeoResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreatePageSeo extends CreateRecord
{
    protected static string $resource = PageSeoResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;
}
