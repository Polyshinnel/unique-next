<?php

namespace App\Domain\Catalog\Import\Resolvers;

use App\Domain\Catalog\Import\Data\RegionData;
use App\Domain\Catalog\Models\Region;

final class RegionResolver
{
    public function upsert(RegionData $regionData): Region
    {
        $region = Region::firstOrNew([
            'external_id' => $regionData->externalId,
        ]);

        if (! $region->exists) {
            $region->fill([
                'name' => $regionData->name,
            ])->save();

            return $region;
        }

        if ($region->name !== $regionData->name) {
            $region->update([
                'name' => $regionData->name,
            ]);
        }

        return $region;
    }
}
