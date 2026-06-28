<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Region extends Model
{
    protected $table = 'regions';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'external_id',
        'name',
        'city_name',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_region');
    }
}
