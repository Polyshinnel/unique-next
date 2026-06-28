<?php

namespace App\Domain\Shipment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ShipmentImage extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'shipment_id',
        'file_name',
        'file_path',
        'is_main',
        'sort_order',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_main' => 'bool',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
