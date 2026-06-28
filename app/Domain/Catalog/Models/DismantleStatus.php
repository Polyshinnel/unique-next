<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class DismantleStatus extends Model
{
    protected $table = 'dismantle_statuses';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'external_id',
        'name',
    ];

    public function dismantlings(): HasMany
    {
        return $this->hasMany(ProductDismantling::class);
    }
}
