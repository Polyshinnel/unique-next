<?php

namespace App\Domain\Catalog\Enums;

enum ManagerRole: string
{
    case Creator = 'creator';
    case Owner = 'owner';
    case RegionalRepresentative = 'regional_representative';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
