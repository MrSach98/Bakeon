<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductWeight extends Model
{
    protected $fillable = [
        'product_id', 'weight_id', 'egg_type', 'price',
        'discount_price', 'stock', 'is_default', 'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function weight(): BelongsTo
    {
        return $this->belongsTo(Weight::class);
    }
}