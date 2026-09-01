<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\DeliveryOption;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductWeight;
use App\Models\ServiceablePincode;
use App\Services\DeliveryChargeCalculator;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RazorpayController extends StorefrontController
{
    public function __construct(Request $request, private RazorpayService $razorpay)
    {
        parent::__construct($request);
    }

    /**
     * Step A: Create a Razorpay order. Amount is recalculated here from
     * the DB — nothing from the request body is trusted for pricing.
     */
    public function createOrder(Request $request)
    {
        $validated = $request->validate([
            'pincode' => ['required', 'string', 'max:10'],
            'delivery_option_id' => ['required', 'integer', 'exists:delivery_options,id'],
        ]);

        $owner = $this->cartOwnerQuery($request);

        $cartItems = CartItem::where($owner)
            ->where('status', 'active')
            ->with(['product', 'productWeight', 'addon'])
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Your cart is empty.'], 422);
        }

        $deliveryOption = DeliveryOption::where('id', $validated['delivery_option_id'])
            ->where('is_active', true)
            ->first();

        if (! $deliveryOption) {
            return response()->json(['success' => false, 'message' => 'Invalid delivery option.'], 422);
        }

        $pincodeRecord = ServiceablePincode::where('pincode', $validated['pincode'])
            ->where('is_active', true)
            ->first();

        if (! $pincodeRecord) {
            return response()->json(['success' => false, 'message' => 'We do not deliver to this pincode.'], 422);
        }

        // Recalculate subtotal purely from DB
        $subtotal = 0;
        foreach ($cartItems as $item) {
            if ($item->addon_id) {
                $addon = $item->addon;
                if ($addon) {
                    $subtotal += (float) $addon->price * $item->quantity;
                }
                continue;
            }

            $variant = $item->productWeight;
            if ($variant) {
                $subtotal += (float) ($variant->discount_price ?? $variant->price) * $item->quantity;
            }
        }

        // Coupon discount, recalculated from DB
        $discount = 0;
        $couponCode = session('applied_coupon_code');
        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();
            if ($coupon && $coupon->isCurrentlyValid() && $subtotal >= $coupon->min_order_value) {
                $discount = $coupon->calculateDiscount($subtotal);
            }
        }

        $deliveryCharge = DeliveryChargeCalculator::calculate(
            $subtotal,
            $validated['pincode'],
            $deliveryOption,
            $cartItems
        );

        $totalAmount = round($subtotal + $deliveryCharge - $discount, 2);

        if ($totalAmount <= 0) {
            return response()->json(['success' => false, 'message' => 'Invalid order amount.'], 422);
        }

        $receipt = 'rcpt_' . Str::random(16);

        try {
            $razorpayOrder = $this->razorpay->createOrder($totalAmount, $receipt);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'Unable to initiate payment. Please try again.'], 500);
        }

        $payment = Payment::create([
            'razorpay_order_id' => $razorpayOrder['id'],
            'user_id' => auth()->id(),
            'guest_token' => auth()->check() ? null : $request->cookie('guest_token'),
            'amount' => $totalAmount,
            'status' => 'created',
            'meta' => [
                'pincode' => $validated['pincode'],
                'delivery_option_id' => $deliveryOption->id,
            ],
        ]);

        return response()->json([
            'success' => true,
            'razorpay_order_id' => $razorpayOrder['id'],
            'amount' => $razorpayOrder['amount'],
            'currency' => 'INR',
            'key' => config('services.razorpay.key'),
        ]);
    }

    /**
     * Step B: Verify signature, then create the real Order using the
     * exact same locked, server-authoritative logic as the COD flow.
     */
    public function verifyAndPlaceOrder(Request $request)
    {
        $validated = $request->validate([
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],

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
        ]);

        $payment = Payment::where('razorpay_order_id', $validated['razorpay_order_id'])
            ->where('status', 'created')
            ->first();

        if (! $payment) {
            return response()->json(['success' => false, 'message' => 'Invalid or already processed payment order.'], 422);
        }

        // Signature verification — the ONLY proof that this payment is genuine
        $isValid = $this->razorpay->verifySignature(
            $validated['razorpay_order_id'],
            $validated['razorpay_payment_id'],
            $validated['razorpay_signature']
        );

        if (! $isValid) {
            $payment->update(['status' => 'failed']);
            return response()->json(['success' => false, 'message' => 'Payment verification failed.'], 422);
        }

        // Idempotency: this payment_id must not already be used elsewhere
        if (Payment::where('razorpay_payment_id', $validated['razorpay_payment_id'])->exists()) {
            return response()->json(['success' => false, 'message' => 'This payment has already been processed.'], 422);
        }

        $pincodeRecord = ServiceablePincode::where('pincode', $validated['pincode'])
            ->where('is_active', true)
            ->first();

        if (! $pincodeRecord) {
            return response()->json(['success' => false, 'message' => 'We do not deliver to this pincode.'], 422);
        }

        $deliveryOption = DeliveryOption::where('id', $validated['delivery_option_id'])
            ->where('is_active', true)
            ->first();

        if (! $deliveryOption) {
            return response()->json(['success' => false, 'message' => 'Invalid delivery option.'], 422);
        }

        $optionAllowed = match ($deliveryOption->slug) {
            'same-day' => $pincodeRecord->same_day_available,
            'midnight' => $pincodeRecord->midnight_available,
            'express' => $pincodeRecord->express_available,
            default => true,
        };

        if (! $optionAllowed) {
            return response()->json(['success' => false, 'message' => 'This delivery option is not available for your pincode.'], 422);
        }

        $owner = auth()->check() ? ['user_id' => auth()->id()] : ['guest_token' => $request->cookie('guest_token')];
        $fullAddress = $validated['address_line'] . ', ' . $validated['area_locality'] . ', ' . $validated['city'] . ', ' . $validated['pincode'];

        try {
            $order = DB::transaction(function () use ($validated, $owner, $deliveryOption, $fullAddress, $payment) {
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
                        $addon = \App\Models\Addon::where('id', $cartItem->addon_id)->where('is_active', true)->lockForUpdate()->first();

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
                            'cart_item' => $cartItem, 'product' => null, 'variant' => null,
                            'addon' => $addon, 'unit_price' => $unitPrice, 'total_price' => $lineTotal,
                        ];

                        if ($addon->stock !== null) {
                            $addon->decrement('stock', $cartItem->quantity);
                        }
                        continue;
                    }

                    $variant = ProductWeight::where('id', $cartItem->product_weight_id)
                        ->where('is_active', true)->lockForUpdate()->first();
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
                        'cart_item' => $cartItem, 'product' => $product, 'variant' => $variant,
                        'addon' => null, 'unit_price' => $unitPrice, 'total_price' => $lineTotal,
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

                $deliveryCharge = DeliveryChargeCalculator::calculate(
                    $subtotal, $validated['pincode'], $deliveryOption, $cartItems
                );

                $totalAmount = round($subtotal + $deliveryCharge - $discount, 2);

                // Safety check: the amount actually paid via Razorpay must match
                // what we just recalculated from the live cart — if a cart item
                // changed between payment creation and now, refuse to place the order.
                if (abs($totalAmount - (float) $payment->amount) > 0.01) {
                    throw new \RuntimeException('AMOUNT_MISMATCH');
                }

                $order = Order::create([
                    'order_number' => Order::generateOrderNumber(),
                    'idempotency_key' => 'razorpay_' . $validated['razorpay_payment_id'],
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
                    'payment_method' => 'online',
                    'payment_status' => 'paid',
                    'status' => 'confirmed',
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

                $payment->update([
                    'razorpay_payment_id' => $validated['razorpay_payment_id'],
                    'order_id' => $order->id,
                    'status' => 'paid',
                ]);

                return $order;
            });
        } catch (\RuntimeException $e) {
            $message = match (true) {
                $e->getMessage() === 'CART_EMPTY' => 'Your cart is empty.',
                $e->getMessage() === 'AMOUNT_MISMATCH' => 'Order amount mismatch. Please contact support with your payment ID.',
                str_starts_with($e->getMessage(), 'OUT_OF_STOCK:') => 'Sorry, "' . substr($e->getMessage(), 13) . '" just went out of stock. Please contact support for a refund.',
                str_starts_with($e->getMessage(), 'ITEM_UNAVAILABLE:') => substr($e->getMessage(), 18) . ' is no longer available. Please contact support for a refund.',
                default => 'Something went wrong placing your order.',
            };

            return response()->json(['success' => false, 'message' => $message], 422);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'Something went wrong. Please contact support.'], 500);
        }

        session()->forget('applied_coupon_code');

        return response()->json([
            'success' => true,
            'redirect_url' => url('/order-confirmation/' . $order->order_number),
        ]);
    }

    private function cartOwnerQuery(Request $request): array
    {
        if (auth()->check()) {
            return ['user_id' => auth()->id()];
        }
        return ['guest_token' => $request->cookie('guest_token')];
    }
}