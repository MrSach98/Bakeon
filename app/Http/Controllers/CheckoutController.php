<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\DeliveryOption;
use Illuminate\Http\Request;

class CheckoutController extends StorefrontController
{
    public function show(Request $request)
    {
        $owner = $this->cartOwnerQuery($request);

        $items = CartItem::where($owner)
            ->where('status', 'active')
            ->with(['product.primaryImage', 'productWeight.weight', 'addon'])
            ->get();

        if ($items->isEmpty()) {
            return redirect('/cart')->with('error', 'Your cart is empty.');
        }

        $lineItems = [];
        $subtotal = 0;

        foreach ($items as $item) {
            if ($item->addon_id) {
                $addon = $item->addon;
                if (! $addon) continue;

                $unitPrice = (float) $addon->price;
                $lineTotal = $unitPrice * $item->quantity;
                $subtotal += $lineTotal;

                $lineItems[] = [
                    'is_addon' => true,
                    'name' => $addon->name,
                    'weight_label' => null,
                    'quantity' => $item->quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'image' => $addon->image ? asset($addon->image) : null,
                ];
                continue;
            }

            $variant = $item->productWeight;
            $product = $item->product;
            if (! $product || ! $variant) continue;

            $unitPrice = (float) ($variant->discount_price ?? $variant->price);
            $lineTotal = $unitPrice * $item->quantity;
            $subtotal += $lineTotal;

            $lineItems[] = [
                'is_addon' => false,
                'name' => $product->name,
                'weight_label' => $variant->weight->label ?? '',
                'quantity' => $item->quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
                'image' => $product->primaryImage ? asset($product->primaryImage->image_path) : null,
            ];
        }

        $deliveryOptions = DeliveryOption::where('is_active', true)->orderBy('extra_charge')->get();

        $user = auth()->user();

        return view('checkout', compact('lineItems', 'subtotal', 'deliveryOptions', 'user'));
    }

    private function cartOwnerQuery(Request $request): array
    {
        if (auth()->check()) {
            return ['user_id' => auth()->id()];
        }

        return ['guest_token' => $request->cookie('guest_token')];
    }
}