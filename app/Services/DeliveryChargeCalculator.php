<?php

namespace App\Services;

use App\Models\DeliveryOption;
use App\Models\ServiceablePincode;
use App\Models\SiteSetting;

class DeliveryChargeCalculator
{
    /**
     * The ONLY place delivery charge is ever computed. Always called
     * server-side — never trust a charge value coming from the client.
     */
    public static function calculate(float $subtotal, string $pincode, DeliveryOption $deliveryOption, $cartItems): float
    {
        $settings = SiteSetting::current();

        if ($subtotal >= (float) $settings->free_delivery_threshold) {
            return 0;
        }

        $productOverride = collect($cartItems)
            ->map(fn ($item) => $item->product->delivery_charge_override ?? null)
            ->filter()
            ->max();

        if ($productOverride !== null) {
            return (float) $productOverride;
        }

        $pincodeRecord = ServiceablePincode::where('pincode', $pincode)->where('is_active', true)->first();
        $locationCharge = $pincodeRecord?->chargeForSlug($deliveryOption->slug);

        if ($locationCharge !== null) {
            return (float) $locationCharge;
        }

        if ($deliveryOption->extra_charge > 0) {
            return (float) $deliveryOption->extra_charge;
        }

        return (float) $settings->default_delivery_charge;
    }
}