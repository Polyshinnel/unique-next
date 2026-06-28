<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class EquipmentState extends Model
{
    protected $table = 'equipment_states';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'external_id',
        'name',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
