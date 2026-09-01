<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'user_id', 'guest_token', 'product_id', 'product_weight_id', 'addon_id',
        'quantity', 'status', 'order_id',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productWeight(): BelongsTo
    {
        return $this->belongsTo(ProductWeight::class);
    }

    public function addon(): BelongsTo
    {
        return $this->belongsTo(Addon::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}