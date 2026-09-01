<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title', 'type', 'subtitle', 'image', 'button_text', 'link_url', 'sort_order', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public const TYPES = [
        'hero' => 'Hero Slider',
        'promo_strip' => 'Promo Strip (thin banner)',
        'occasion_reminder' => 'Occasion Reminder',
        'app_deal' => 'App Only Deal',
        'other' => 'Other',
    ];
}