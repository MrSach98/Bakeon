<?php
// app/Models/Order.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
    'order_number', 'idempotency_key', 'user_id', 'customer_name', 'customer_phone', 'customer_email',
    'receiver_name', 'receiver_phone', 'alternate_phone',
    'delivery_address', 'pincode', 'city', 'address_type',
    'delivery_date', 'delivery_time_slot', 'cake_message',
    'delivery_option_id', 'delivery_charge',
    'coupon_id', 'coupon_code', 'discount_amount',
    'subtotal', 'total_amount',
    'payment_status', 'payment_method',
    'status', 'admin_notes', 'cancellation_reason',
];

    protected $casts = [
        'delivery_date' => 'date',
        'delivery_charge' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public const STATUS_FLOW = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'preparing' => 'Preparing',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];

    public static function generateOrderNumber(): string
    {
        do {
            $number = 'SB' . now()->format('ymd') . strtoupper(Str::random(5));
        } while (static::where('order_number', $number)->exists());

        return $number;
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function deliveryOption(): BelongsTo
    {
        return $this->belongsTo(DeliveryOption::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function payment()
    {
        return $this->hasOne(\App\Models\Payment::class, 'order_id');
    }
}