<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'store_name', 'logo', 'favicon', 'contact_phone', 'contact_email', 'address',
        'facebook_url', 'instagram_url', 'twitter_url', 'youtube_url',
        'currency_symbol', 'default_meta_title', 'default_meta_description',
        'online_payment_enabled', 'free_delivery_threshold', 'default_delivery_charge',
        'feed_enabled', 'feed_token', 'feed_currency',
    ];

    protected $casts = [
        'online_payment_enabled' => 'boolean',
        'free_delivery_threshold' => 'decimal:2',
        'default_delivery_charge' => 'decimal:2',
        'feed_enabled' => 'boolean',
    ];

    /** Always returns the single settings row, creating it with defaults if missing */
    // public static function current(): self
    // {
    //     return static::first() ?? static::create([]);
    // }

    public static function current(): self
    {
        $settings = static::first() ?? static::create([]);

        if (! $settings->feed_token) {
            $settings->update(['feed_token' => \Illuminate\Support\Str::random(40)]);
        }

        return $settings;
    }
}