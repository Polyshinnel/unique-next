<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductCheck extends Model
{
    protected $table = 'product_checks';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'check_status_id',
        'comment',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(CheckStatus::class, 'check_status_id');
    }
}
