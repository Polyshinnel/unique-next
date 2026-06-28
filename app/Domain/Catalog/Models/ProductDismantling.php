<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductDismantling extends Model
{
    protected $table = 'product_dismantlings';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'dismantle_status_id',
        'comment',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(DismantleStatus::class, 'dismantle_status_id');
    }
}
