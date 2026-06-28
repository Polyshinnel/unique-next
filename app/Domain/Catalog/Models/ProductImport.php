<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;

final class ProductImport extends Model
{
    protected $table = 'product_imports';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'feed_export_date',
        'status',
        'created_count',
        'skipped_count',
        'failed_count',
        'message',
        'started_at',
        'finished_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'feed_export_date' => 'datetime',
        'created_count' => 'int',
        'skipped_count' => 'int',
        'failed_count' => 'int',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];
}
