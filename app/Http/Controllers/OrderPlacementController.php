<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\DeliveryOption;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductWeight;
use App\Models\Addon;
use App\Models\ServiceablePincode;
use App\Services\DeliveryChargeCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderPlacementController extends StorefrontController
{
    public function applyCoupon(Request $request)
    {
        $validated = $request->validate(['coupon_code' => ['required', 'string', 'max:50']]);

        $coupon = Coupon::where('code', strtoupper($validated['coupon_code']))->first();

        if (! $coupon || ! $coupon->isCurrentlyValid()) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired coupon code.'], 422);
        }

        $subtotal = $this->liveSubtotal($request);

        if ($subtotal < $coupon->min_order_value) {
            return response()->json([
                'success' => false,
                'message' => 'Minimum order of ₹' . number_format($coupon->min_order_value, 0) . ' required.',
            ], 422);
        }

        $discount = $coupon->calculateDiscount($subtotal);
        session(['applied_coupon_code' => $coupon->code]);

        return response()->json(['success' => true, 'message' => 'Coupon applied!', 'discount' => $discount]);
    }

    public function removeCoupon()
    {
        session()->forget('applied_coupon_code');
        return response()->json(['success' => true]);
    }

    public function place(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'customer_email' => ['nullable', 'email', 'max:255'],

            'receiver_name' => ['required', 'string', 'max:255'],
            'receiver_phone' => ['required', 'string', 'max:20'],
            'alternate_phone' => ['nullable', 'string', 'max:20'],
            'address_line' => ['required', 'string', 'max:500'],
            'area_locality' => ['required', 'string', 'max:255'],
            'pincode' => ['required', 'string', 'max:10'],
            'city' => ['required', 'string', 'max:255'],
            'address_type' => ['required', 'in:home,office,others'],

            'delivery_date' => ['required', 'date', 'after_or_equal:today'],
            'delivery_time_slot' => ['nullable', 'string', 'max:50'],
            'delivery_option_id' => ['required', 'integer', 'exists:delivery_options,id'],
            'cake_message' => ['nullable', 'string', 'max:250'],

            'payment_method' => ['required', 'in:cod,online'],
        ]);

        $idempotencyKey = $request->session()->get('checkout_idempotency_key');
        if (! $idempotencyKey) {
            $idempotencyKey = bin2hex(random_bytes(20));
            $request->session()->put('checkout_idempotency_key', $idempotencyKey);
        } else {
            $existingOrder = Order::where('idempotency_key', $idempotencyKey)->first();
            if ($existingOrder) {
                return redirect('/order-confirmation/' . $existingOrder->order_number);
            }
        }

        $pincodeRecord = ServiceablePincode::where('pincode', $validated['pincode'])
            ->where('is_active', true)
            ->first();

        if (! $pincodeRecord) {
            return back()->withErrors(['pincode' => 'Sorry, we do not deliver to this pincode.'])->withInput();
        }

        $deliveryOption = DeliveryOption::where('id', $validated['delivery_option_id'])
            ->where('is_active', true)
            ->first();

        if (! $deliveryOption) {
            return back()->withErrors(['delivery_option_id' => 'Selected delivery option is not available.'])->withInput();
        }

        $optionAllowed = match ($deliveryOption->slug) {
            'same-day' => $pincodeRecord->same_day_available,
            'midnight' => $pincodeRecord->midnight_available,
            'express' => $pincodeRecord->express_available,
            default => true,
        };

        if (! $optionAllowed) {
            return back()->withErrors(['delivery_option_id' => 'This delivery option is not available for your pincode.'])->withInput();
        }

        $owner = auth()->check() ? ['user_id' => auth()->id()] : ['guest_token' => $request->cookie('guest_token')];

        $fullAddress = $validated['address_line'] . ', ' . $validated['area_locality'] . ', ' . $validated['city'] . ', ' . $validated['pincode'];

        try {
            $order = DB::transaction(function () use ($validated, $owner, $deliveryOption, $idempotencyKey, $fullAddress) {
                $cartItems = CartItem::where($owner)
                    ->where('status', 'active')
                    ->with(['product', 'productWeight.weight', 'addon'])
                    ->lockForUpdate()
                    ->get();

                if ($cartItems->isEmpty()) {
                    throw new \RuntimeException('CART_EMPTY');
                }

                $subtotal = 0;
                $orderItemsData = [];

                foreach ($cartItems as $cartItem) {
                    if ($cartItem->addon_id) {
                        $addon = Addon::where('id', $cartItem->addon_id)->where('is_active', true)->lockForUpdate()->first();

                        if (! $addon) {
                            throw new \RuntimeException('ITEM_UNAVAILABLE:An add-on item');
                        }

                        if ($addon->stock !== null && $addon->stock < $cartItem->quantity) {
                            throw new \RuntimeException('OUT_OF_STOCK:' . $addon->name);
                        }

                        $unitPrice = (float) $addon->price;
                        $lineTotal = round($unitPrice * $cartItem->quantity, 2);
                        $subtotal += $lineTotal;

                        $orderItemsData[] = [
                            'cart_item' => $cartItem,
                            'product' => null,
                            'variant' => null,
                            'addon' => $addon,
                            'unit_price' => $unitPrice,
                            'total_price' => $lineTotal,
                        ];

                        if ($addon->stock !== null) {
                            $addon->decrement('stock', $cartItem->quantity);
                        }

                        continue;
                    }

                    $variant = ProductWeight::where('id', $cartItem->product_weight_id)
                        ->where('is_active', true)
                        ->lockForUpdate()
                        ->first();

                    $product = $cartItem->product;

                    if (! $variant || ! $product || $product->status !== 'active') {
                        throw new \RuntimeException('ITEM_UNAVAILABLE:' . ($product->name ?? 'An item'));
                    }

                    if ($variant->stock !== null && $variant->stock < $cartItem->quantity) {
                        throw new \RuntimeException('OUT_OF_STOCK:' . $product->name);
                    }

                    $unitPrice = (float) ($variant->discount_price ?? $variant->price);
                    $lineTotal = round($unitPrice * $cartItem->quantity, 2);
                    $subtotal += $lineTotal;

                    $orderItemsData[] = [
                        'cart_item' => $cartItem,
                        'product' => $product,
                        'variant' => $variant,
                        'addon' => null,
                        'unit_price' => $unitPrice,
                        'total_price' => $lineTotal,
                    ];

                    if ($variant->stock !== null) {
                        $variant->decrement('stock', $cartItem->quantity);
                    }
                }

                $discount = 0;
                $coupon = null;
                $couponCode = session('applied_coupon_code');

                if ($couponCode) {
                    $coupon = Coupon::where('code', $couponCode)->lockForUpdate()->first();

                    if ($coupon && $coupon->isCurrentlyValid() && $subtotal >= $coupon->min_order_value) {
                        $discount = $coupon->calculateDiscount($subtotal);
                        $coupon->increment('used_count');
                    } else {
                        $coupon = null;
                    }
                }

                $cartItemsForDelivery = $cartItems;
                $deliveryCharge = DeliveryChargeCalculator::calculate(
                    $subtotal,
                    $validated['pincode'],
                    $deliveryOption,
                    $cartItemsForDelivery
                );

                $totalAmount = round($subtotal + $deliveryCharge - $discount, 2);

                $order = Order::create([
                    'order_number' => Order::generateOrderNumber(),
                    'idempotency_key' => $idempotencyKey,
                    'user_id' => auth()->id(),
                    'customer_name' => $validated['customer_name'],
                    'customer_phone' => $validated['customer_phone'],
                    'customer_email' => $validated['customer_email'] ?? null,
                    'receiver_name' => $validated['receiver_name'],
                    'receiver_phone' => $validated['receiver_phone'],
                    'alternate_phone' => $validated['alternate_phone'] ?? null,
                    'delivery_address' => $fullAddress,
                    'pincode' => $validated['pincode'],
                    'city' => $validated['city'],
                    'address_type' => $validated['address_type'],
                    'delivery_date' => $validated['delivery_date'],
                    'delivery_time_slot' => $validated['delivery_time_slot'] ?? null,
                    'cake_message' => $validated['cake_message'] ?? null,
                    'delivery_option_id' => $deliveryOption->id,
                    'delivery_charge' => $deliveryCharge,
                    'coupon_id' => $coupon->id ?? null,
                    'coupon_code' => $coupon->code ?? null,
                    'discount_amount' => $discount,
                    'subtotal' => $subtotal,
                    'total_amount' => $totalAmount,
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => 'pending',
                    'status' => 'pending',
                ]);

                foreach ($orderItemsData as $data) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $data['product']->id ?? null,
                        'product_weight_id' => $data['variant']->id ?? null,
                        'product_name' => $data['product']->name ?? $data['addon']->name,
                        'weight_label' => $data['variant']->weight->label ?? null,
                        'egg_type' => $data['variant']->egg_type ?? null,
                        'quantity' => $data['cart_item']->quantity,
                        'unit_price' => $data['unit_price'],
                        'total_price' => $data['total_price'],
                    ]);

                    $data['cart_item']->update(['status' => 'ordered', 'order_id' => $order->id]);
                }

                return $order;
            });
        } catch (\RuntimeException $e) {
            $message = match (true) {
                $e->getMessage() === 'CART_EMPTY' => 'Your cart is empty.',
                str_starts_with($e->getMessage(), 'OUT_OF_STOCK:') => 'Sorry, "' . substr($e->getMessage(), 13) . '" just went out of stock.',
                str_starts_with($e->getMessage(), 'ITEM_UNAVAILABLE:') => substr($e->getMessage(), 18) . ' is no longer available.',
                default => 'Something went wrong placing your order.',
            };

            return back()->withErrors(['order' => $message])->withInput();
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['order' => 'Something went wrong. Please try again.'])->withInput();
        }

        session()->forget(['applied_coupon_code', 'checkout_idempotency_key']);

        return redirect('/order-confirmation/' . $order->order_number);
    }

    public function confirmation(string $orderNumber)
    {
        $order = Order::with(['items', 'deliveryOption'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if (auth()->check() && $order->user_id && $order->user_id !== auth()->id()) {
            abort(403);
        }

        return view('order-confirmation', compact('order'));
    }

    private function liveSubtotal(Request $request): float
    {
        $owner = auth()->check() ? ['user_id' => auth()->id()] : ['guest_token' => $request->cookie('guest_token')];

        $items = CartItem::where($owner)->where('status', 'active')->with(['productWeight', 'addon'])->get();

        return (float) $items->sum(function ($item) {
            if ($item->addon_id) {
                return $item->addon ? (float) $item->addon->price * $item->quantity : 0;
            }
            $variant = $item->productWeight;
            if (! $variant) return 0;
            return (float) ($variant->discount_price ?? $variant->price) * $item->quantity;
        });
    }
}