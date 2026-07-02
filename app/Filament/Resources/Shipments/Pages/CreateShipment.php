<?php

namespace App\Filament\Resources\Shipments\Pages;

use App\Filament\Resources\Shipments\ShipmentResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateShipment extends CreateRecord
{
    protected static string $resource = ShipmentResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;
}
