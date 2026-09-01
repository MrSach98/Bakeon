<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'razorpay_order_id', 'razorpay_payment_id', 'order_id',
        'guest_token', 'user_id', 'amount', 'status', 'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'meta' => 'array',
    ];
}