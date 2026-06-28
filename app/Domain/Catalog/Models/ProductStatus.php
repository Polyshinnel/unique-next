<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ProductStatus extends Model
{
    protected $table = 'product_statuses';

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
