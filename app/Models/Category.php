<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'parent_id', 'name', 'slug', 'image', 'description', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function productsAsSubcategory(): HasMany
    {
        return $this->hasMany(Product::class, 'subcategory_id');
    }

    public function productsAsChildCategory(): HasMany
    {
        return $this->hasMany(Product::class, 'child_category_id');
    }

    /**
     * 0 = top-level Category
     * 1 = Subcategory
     * 2 = Child Category
     */
    public function depth(): int
    {
        $depth = 0;
        $node = $this;

        while ($node->parent_id) {
            $depth++;
            $node = $node->parent;
        }

        return $depth;
    }
}