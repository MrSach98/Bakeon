<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Weight extends Model
{
    protected $fillable = ['label', 'value_kg', 'serves', 'serves_max', 'sort_order', 'is_active'];

    protected $casts = [
        'value_kg' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function productWeights()
    {
        return $this->hasMany(ProductWeight::class);
    }
    public function getServesRangeAttribute(): string
    {
        if ($this->serves && $this->serves_max) {
            return $this->serves . '-' . $this->serves_max;
        }

        return (string) ($this->serves ?? '');
    }
}