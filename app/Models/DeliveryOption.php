<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryOption extends Model
{
    protected $fillable = [
        'name', 'slug', 'order_cutoff_time', 'delivery_window_start',
        'delivery_window_end', 'extra_charge', 'is_active',
    ];

    protected $casts = [
        'extra_charge' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_delivery_options')->withTimestamps();
    }
}