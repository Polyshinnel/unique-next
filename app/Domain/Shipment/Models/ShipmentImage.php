<?php

namespace App\Domain\Shipment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

    public function setFilePathAttribute(?string $value): void
    {
        $this->attributes['file_path'] = $value;

        if (filled($value)) {
            $this->attributes['file_name'] = (string) Str::of($value)
                ->afterLast('/')
                ->toString();
        }
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    protected static function booted(): void
    {
        static::saved(static function (ShipmentImage $image): void {
            if (! $image->is_main || ! $image->shipment_id) {
                return;
            }

            DB::table($image->getTable())
                ->where('shipment_id', $image->shipment_id)
                ->where('id', '!=', $image->id)
                ->update(['is_main' => false]);
        });
    }
}
