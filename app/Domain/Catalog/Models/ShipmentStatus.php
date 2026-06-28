<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ShipmentStatus extends Model
{
    protected $table = 'shipment_statuses';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'external_id',
        'name',
    ];

    public function loadings(): HasMany
    {
        return $this->hasMany(ProductLoading::class);
    }
}
