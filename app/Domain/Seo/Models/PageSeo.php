<?php

namespace App\Domain\Seo\Models;

use Illuminate\Database\Eloquent\Model;

final class PageSeo extends Model
{
    protected $table = 'page_seo';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'path',
        'name',
        'title',
        'description',
        'og_image',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'bool',
    ];
}
