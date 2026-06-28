<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Manager extends Model
{
    protected $table = 'managers';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'external_id',
        'name',
        'phone',
        'email',
        'vk',
        'max',
        'telegram',
        'role',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_manager')->withPivot('role');
    }
}
