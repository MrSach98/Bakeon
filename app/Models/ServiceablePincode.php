<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceablePincode extends Model
{
    protected $fillable = [
        'pincode', 'city', 'state', 'same_day_available',
        'midnight_available', 'express_available', 'is_active',
    ];

    protected $casts = [
        'same_day_available' => 'boolean',
        'midnight_available' => 'boolean',
        'express_available' => 'boolean',
        'is_active' => 'boolean',
    ];
}