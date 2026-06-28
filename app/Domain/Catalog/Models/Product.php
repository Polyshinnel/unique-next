<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Product extends Model
{
    use SoftDeletes;

    protected $table = 'products';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'external_id',
        'name',
        'sku',
        'title',
        'description',
        'og_image',
        'category_id',
        'manager_id',
        'equipment_state_id',
        'equipment_availability_id',
        'product_status_id',
        'price',
        'show_price',
        'price_comment',
        'region_id',
        'published_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'show_price' => 'bool',
        'published_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class);
    }

    public function equipmentState(): BelongsTo
    {
        return $this->belongsTo(EquipmentState::class);
    }

    public function equipmentAvailability(): BelongsTo
    {
        return $this->belongsTo(EquipmentAvailability::class);
    }

    public function productStatus(): BelongsTo
    {
        return $this->belongsTo(ProductStatus::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function mainCharacteristics(): HasOne
    {
        return $this->hasOne(ProductMainCharacteristic::class);
    }

    public function complectation(): HasOne
    {
        return $this->hasOne(ProductComplectation::class);
    }

    public function technicalCharacteristics(): HasOne
    {
        return $this->hasOne(ProductTechnicalCharacteristic::class);
    }

    public function mainInfo(): HasOne
    {
        return $this->hasOne(ProductMainInfo::class);
    }

    public function additionalInfo(): HasOne
    {
        return $this->hasOne(ProductAdditionalInfo::class);
    }

    public function check(): HasOne
    {
        return $this->hasOne(ProductCheck::class);
    }

    public function loading(): HasOne
    {
        return $this->hasOne(ProductLoading::class);
    }

    public function dismantling(): HasOne
    {
        return $this->hasOne(ProductDismantling::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function mainImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_main', true);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tag');
    }

    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class, 'product_region');
    }

    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(Manager::class, 'product_manager')->withPivot('role');
    }
}
