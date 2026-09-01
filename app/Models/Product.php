<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'subcategory_id', 'child_category_id',
        'name', 'slug', 'sku',
        'short_description', 'description', 'base_price', 'discount_price',
        'egg_type', 'is_photo_cake', 'is_message_enabled', 'message_char_limit',
        'meta_title', 'meta_description','url','meta_keywords',
        'is_featured', 'is_bestseller', 'status',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'is_photo_cake' => 'boolean',
        'is_message_enabled' => 'boolean',
        'is_featured' => 'boolean',
        'is_bestseller' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function childCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'child_category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function weightVariants(): HasMany
    {
        return $this->hasMany(ProductWeight::class);
    }

    public function flavors(): BelongsToMany
    {
        return $this->belongsToMany(Flavor::class, 'product_flavors')
            ->withPivot('price_modifier', 'is_default')
            ->withTimestamps();
    }

    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(Addon::class, 'product_addons')
            ->withPivot('price_override')
            ->withTimestamps();
    }

    public function deliveryOptions(): BelongsToMany
    {
        return $this->belongsToMany(DeliveryOption::class, 'product_delivery_options')->withTimestamps();
    }

    public function occasions(): BelongsToMany
    {
        return $this->belongsToMany(Occasion::class, 'product_occasions')->withTimestamps();
    }

    public function defaultVariant()
    {
        return $this->weightVariants()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('price')
            ->first();
    }
}