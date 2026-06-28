<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductImage extends Model
{
    protected $table = 'product_images';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'external_id',
        'product_id',
        'file_name',
        'file_path',
        'file_url',
        'mime_type',
        'file_size',
        'is_main',
        'sort_order',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'file_size' => 'int',
        'is_main' => 'bool',
        'sort_order' => 'int',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
