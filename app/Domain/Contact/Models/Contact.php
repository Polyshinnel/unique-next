<?php

namespace App\Domain\Contact\Models;

use Illuminate\Database\Eloquent\Model;

final class Contact extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'address',
        'phone',
        'email',
        'work_schedule',
        'latitude',
        'longitude',
        'telegram',
        'vk',
        'max',
        'inn',
        'ogrn',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];
}
