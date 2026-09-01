<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'max_discount_amount', 'min_order_value',
        'usage_limit', 'used_count', 'per_user_limit',
        'valid_from', 'valid_until', 'description', 'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
    ];

    /** Is this coupon currently usable at all (status/date/usage limit) */
    public function isCurrentlyValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $today = Carbon::today();

        if ($this->valid_from && $today->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $today->gt($this->valid_until)) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /** Calculate discount amount for a given order total */
    public function calculateDiscount(float $orderTotal): float
    {
        if ($orderTotal < $this->min_order_value) {
            return 0;
        }

        if ($this->type === 'flat') {
            return min((float) $this->value, $orderTotal);
        }

        // percentage
        $discount = $orderTotal * ((float) $this->value / 100);

        if ($this->max_discount_amount) {
            $discount = min($discount, (float) $this->max_discount_amount);
        }

        return round($discount, 2);
    }
}