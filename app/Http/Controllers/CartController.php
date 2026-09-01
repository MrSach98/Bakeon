<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\CartItem;
use App\Models\DeliveryOption;
use App\Models\Product;
use App\Models\ProductWeight;
use App\Models\SiteSetting;
use App\Services\DeliveryChargeCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartController extends StorefrontController
{
    private const MAX_QTY_PER_ITEM = 20;
    private const MAX_CART_ITEMS = 30;
    private const GUEST_TOKEN_COOKIE = 'guest_token';
    private const GUEST_TOKEN_DAYS = 60;

    /**
     * Resolves how to scope cart queries for the current visitor:
     * logged-in users use user_id, guests use a random guest_token
     * stored in an httpOnly cookie (never exposed to JS, unguessable).
     */
    private function cartOwnerQuery(Request $request): array
    {
        if (auth()->check()) {
            return ['user_id' => auth()->id()];
        }

        $token = $request->cookie(self::GUEST_TOKEN_COOKIE);

        if (! $token) {
            $token = Str::random(40);
            cookie()->queue(cookie(
                self::GUEST_TOKEN_COOKIE,
                $token,
                60 * 24 * self::GUEST_TOKEN_DAYS,
                null, null, false, true // httpOnly — JS can never read/steal this
            ));
        }

        return ['guest_token' => $token];
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_weights,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:' . self::MAX_QTY_PER_ITEM],
        ]);

        $product = Product::where('id', $validated['product_id'])->where('status', 'active')->first();

        if (! $product) {
            return response()->json(['success' => false, 'message' => 'This product is not available.'], 404);
        }

        $owner = $this->cartOwnerQuery($request);
        $requestedQty = $validated['quantity'] ?? 1;

        try {
            $result = DB::transaction(function () use ($validated, $product, $requestedQty, $owner) {
                $variantQuery = ProductWeight::where('product_id', $product->id)->where('is_active', true);

                $variant = $validated['variant_id'] ?? null
                    ? $variantQuery->where('id', $validated['variant_id'])->lockForUpdate()->first()
                    : $variantQuery->orderByDesc('is_default')->orderBy('price')->lockForUpdate()->first();

                if (! $variant) {
                    return ['success' => false, 'message' => 'This product has no available variant.', 'status' => 422];
                }

                $existingItem = CartItem::where($owner)
                    ->where('status', 'active')
                    ->where('product_weight_id', $variant->id)
                    ->lockForUpdate()
                    ->first();

                if (! $existingItem && CartItem::where($owner)->active()->count() >= self::MAX_CART_ITEMS) {
                    return ['success' => false, 'message' => 'Cart limit reached.', 'status' => 422];
                }

                $newQty = ($existingItem->quantity ?? 0) + $requestedQty;

                if ($newQty > self::MAX_QTY_PER_ITEM) {
                    return ['success' => false, 'message' => 'Maximum quantity per item is ' . self::MAX_QTY_PER_ITEM . '.', 'status' => 422];
                }

                if ($variant->stock !== null && $newQty > $variant->stock) {
                    $available = max(0, $variant->stock - ($existingItem->quantity ?? 0));
                    return [
                        'success' => false,
                        'message' => $available > 0 ? "Only {$available} more unit(s) available." : 'This item is out of stock.',
                        'status' => 422,
                    ];
                }

                CartItem::updateOrCreate(
                    array_merge($owner, ['status' => 'active', 'product_weight_id' => $variant->id]),
                    ['product_id' => $product->id, 'quantity' => $newQty]
                );

                return ['success' => true, 'product_name' => $product->name];
            });
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'Something went wrong, please try again.'], 500);
        }

        if (! $result['success']) {
            return response()->json($result, $result['status']);
        }

        return response()->json([
            'success' => true,
            'message' => $result['product_name'] . ' added to cart.',
            'cart_count' => $this->cartCount($request),
        ]);
    }

    public function addAddon(Request $request)
    {
        $validated = $request->validate([
            'addon_id' => ['required', 'integer', 'exists:addons,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:' . self::MAX_QTY_PER_ITEM],
        ]);

        $addon = Addon::where('id', $validated['addon_id'])->where('is_active', true)->first();

        if (! $addon) {
            return response()->json(['success' => false, 'message' => 'This add-on is not available.'], 404);
        }

        $owner = $this->cartOwnerQuery($request);
        $requestedQty = $validated['quantity'] ?? 1;

        try {
            $result = DB::transaction(function () use ($addon, $requestedQty, $owner) {
                $existingItem = CartItem::where($owner)
                    ->where('status', 'active')
                    ->where('addon_id', $addon->id)
                    ->lockForUpdate()
                    ->first();

                if (! $existingItem && CartItem::where($owner)->where('status', 'active')->count() >= self::MAX_CART_ITEMS) {
                    return ['success' => false, 'message' => 'Cart limit reached.', 'status' => 422];
                }

                $newQty = ($existingItem->quantity ?? 0) + $requestedQty;

                if ($newQty > self::MAX_QTY_PER_ITEM) {
                    return ['success' => false, 'message' => 'Maximum quantity per item is ' . self::MAX_QTY_PER_ITEM . '.', 'status' => 422];
                }

                if ($addon->stock !== null && $newQty > $addon->stock) {
                    $available = max(0, $addon->stock - ($existingItem->quantity ?? 0));
                    return [
                        'success' => false,
                        'message' => $available > 0 ? "Only {$available} more unit(s) available." : 'This item is out of stock.',
                        'status' => 422,
                    ];
                }

                if ($existingItem) {
                    $existingItem->update(['quantity' => $newQty]);
                } else {
                    $cartItem = new CartItem();
                    foreach ($owner as $key => $value) {
                        $cartItem->{$key} = $value;
                    }
                    $cartItem->addon_id = $addon->id;
                    $cartItem->quantity = $requestedQty;
                    $cartItem->status = 'active';
                    $cartItem->product_id = null;
                    $cartItem->product_weight_id = null;
                    $cartItem->save();
                }

                return ['success' => true, 'addon_name' => $addon->name];
            });
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'Something went wrong, please try again.'], 500);
        }

        if (! $result['success']) {
            return response()->json($result, $result['status']);
        }

        return response()->json([
            'success' => true,
            'message' => $result['addon_name'] . ' added to cart.',
            'cart_count' => $this->cartCount($request),
        ]);
    }

    public function updateQuantity(Request $request)
    {
        $validated = $request->validate([
            'item_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:' . self::MAX_QTY_PER_ITEM],
        ]);

        $owner = $this->cartOwnerQuery($request);

        try {
            $result = DB::transaction(function () use ($validated, $owner) {
                $item = CartItem::where('id', $validated['item_id'])
                    ->where($owner)
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->first();

                if (! $item) {
                    return ['success' => false, 'message' => 'Item not found in your cart.', 'status' => 404];
                }

                // Addon-only row stock check
                if ($item->addon_id) {
                    $addon = Addon::where('id', $item->addon_id)->lockForUpdate()->first();
                    if ($addon && $addon->stock !== null && $addon->stock < $validated['quantity']) {
                        return ['success' => false, 'message' => "Only {$addon->stock} unit(s) available.", 'status' => 422];
                    }
                } else {
                    $variant = ProductWeight::where('id', $item->product_weight_id)->lockForUpdate()->first();
                    if ($variant && $variant->stock !== null && $variant->stock < $validated['quantity']) {
                        return ['success' => false, 'message' => "Only {$variant->stock} unit(s) available.", 'status' => 422];
                    }
                }

                $item->update(['quantity' => $validated['quantity']]);

                return ['success' => true];
            });
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'Something went wrong, please try again.'], 500);
        }

        if (! $result['success']) {
            return response()->json($result, $result['status']);
        }

        [$lineItems, $subtotal] = $this->buildLineItems($request);
        $updatedItem = collect($lineItems)->firstWhere('item_id', (int) $validated['item_id']);

        return response()->json([
            'success' => true,
            'cart_count' => $this->cartCount($request),
            'subtotal' => $subtotal,
            'line_total' => $updatedItem['line_total'] ?? 0,
        ]);
    }

    public function remove(Request $request)
    {
        $validated = $request->validate(['item_id' => ['required', 'integer']]);
        $owner = $this->cartOwnerQuery($request);

        CartItem::where('id', $validated['item_id'])->where($owner)->where('status', 'active')->delete();

        return response()->json(['success' => true, 'cart_count' => $this->cartCount($request)]);
    }

    public function count(Request $request)
    {
        return response()->json(['cart_count' => $this->cartCount($request)]);
    }

    public function view(Request $request)
    {
        [$lineItems, $subtotal] = $this->buildLineItems($request);

        $owner = $this->cartOwnerQuery($request);
        $cartItems = CartItem::where($owner)->where('status', 'active')->with('product')->get();

        $pincode = session('selected_pincode', '');
        $defaultDeliveryOption = DeliveryOption::where('is_active', true)->where('slug', 'standard')->first()
            ?? DeliveryOption::where('is_active', true)->first();

        $deliveryCharge = ($defaultDeliveryOption && count($lineItems) > 0)
            ? DeliveryChargeCalculator::calculate($subtotal, $pincode, $defaultDeliveryOption, $cartItems)
            : 0;

        $settings = SiteSetting::current();
        $amountToFreeDelivery = max(0, (float) $settings->free_delivery_threshold - $subtotal);
        $grandTotal = $subtotal + $deliveryCharge;

        $crossSellAddons = Addon::where('is_active', true)->inRandomOrder()->take(6)->get();

        return view('cart', compact('lineItems', 'subtotal', 'deliveryCharge', 'amountToFreeDelivery', 'grandTotal', 'crossSellAddons'));
    }

    /**
     * Rebuilds every line item's price, name, image, and availability FRESH
     * from the database every time. cart_items only ever stores
     * product_id + product_weight_id/addon_id + quantity — never a price.
     */
    private function buildLineItems(Request $request): array
    {
        $owner = $this->cartOwnerQuery($request);

        $items = CartItem::where($owner)
            ->where('status', 'active')
            ->with(['product.primaryImage', 'product.images', 'productWeight.weight', 'addon'])
            ->get();

        $lineItems = [];
        $subtotal = 0;

        foreach ($items as $item) {
            // Addon-only row
            if ($item->addon_id) {
                $addon = $item->addon;

                if (! $addon || ! $addon->is_active) {
                    $item->delete();
                    continue;
                }

                $quantity = max(1, min($item->quantity, self::MAX_QTY_PER_ITEM));

                if ($addon->stock !== null && $quantity > $addon->stock) {
                    $quantity = max(0, $addon->stock);
                    if ($quantity === 0) {
                        $item->delete();
                        continue;
                    }
                    $item->update(['quantity' => $quantity]);
                }

                $unitPrice = (float) $addon->price;
                $lineTotal = round($unitPrice * $quantity, 2);
                $subtotal += $lineTotal;

                $lineItems[] = [
                    'item_id' => $item->id,
                    'is_addon' => true,
                    'product_id' => null,
                    'variant_id' => null,
                    'name' => $addon->name,
                    'slug' => null,
                    'weight_label' => null,
                    'egg_type' => null,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                    'image' => $addon->image ? asset($addon->image) : null,
                    'max_qty' => min(self::MAX_QTY_PER_ITEM, $addon->stock ?? self::MAX_QTY_PER_ITEM),
                ];

                continue;
            }

            // Regular product row
            $product = $item->product;
            $variant = $item->productWeight;

            if (! $product || $product->status !== 'active' || ! $variant || ! $variant->is_active) {
                $item->delete();
                continue;
            }

            $quantity = max(1, min($item->quantity, self::MAX_QTY_PER_ITEM));

            if ($variant->stock !== null && $quantity > $variant->stock) {
                $quantity = max(0, $variant->stock);
                if ($quantity === 0) {
                    $item->delete();
                    continue;
                }
                $item->update(['quantity' => $quantity]);
            }

            $unitPrice = (float) ($variant->discount_price ?? $variant->price);
            $lineTotal = round($unitPrice * $quantity, 2);
            $subtotal += $lineTotal;

            $image = $product->primaryImage ?? $product->images->first();

            $lineItems[] = [
                'item_id' => $item->id,
                'is_addon' => false,
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'weight_label' => $variant->weight->label ?? null,
                'egg_type' => $variant->egg_type,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'line_total' => $lineTotal,
                'image' => $image ? asset($image->image_path) : null,
                'max_qty' => min(self::MAX_QTY_PER_ITEM, $variant->stock ?? self::MAX_QTY_PER_ITEM),
            ];
        }

        return [$lineItems, round($subtotal, 2)];
    }

    private function cartCount(Request $request): int
    {
        $owner = $this->cartOwnerQuery($request);
        return (int) CartItem::where($owner)->where('status', 'active')->sum('quantity');
    }
}