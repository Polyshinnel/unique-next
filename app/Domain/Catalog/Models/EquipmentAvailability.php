<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class EquipmentAvailability extends Model
{
    protected $table = 'equipment_availabilities';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'external_id',
        'name',
        'color',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
