<?php

namespace App\Domain\Shipment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Shipment extends Model
{
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'shipment_date',
        'location',
        'title',
        'short_description',
        'sort_order',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'shipment_date' => 'date',
        'sort_order' => 'int',
        'is_active' => 'bool',
    ];

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ShipmentTag::class, 'shipment_tag');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ShipmentImage::class);
    }

    public function mainImage(): HasOne
    {
        return $this->hasOne(ShipmentImage::class)->where('is_main', true);
    }
}
