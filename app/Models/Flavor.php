<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Flavor extends Model
{
    protected $fillable = ['name', 'slug', 'swatch_color', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_flavors')
            ->withPivot('price_modifier', 'is_default')
            ->withTimestamps();
    }
}