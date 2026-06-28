<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductMainInfo extends Model
{
    protected $table = 'product_main_infos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'content',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
