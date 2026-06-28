<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CheckStatus extends Model
{
    protected $table = 'check_statuses';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'external_id',
        'name',
        'color',
    ];

    public function checks(): HasMany
    {
        return $this->hasMany(ProductCheck::class);
    }
}
