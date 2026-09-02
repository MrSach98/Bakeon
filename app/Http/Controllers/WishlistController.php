<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends StorefrontController
{
    /**
     * Toggle a product in/out of the logged-in user's wishlist.
     * Guests are rejected here — this is enforced server-side,
     * not just hidden in the UI, so it can't be bypassed via
     * a direct AJAX call either.
     */
    public function toggle(Request $request)
    {
        if (! auth()->check()) {
            return response()->json([
                'success' => false,
                'requires_login' => true,
                'message' => 'Please login to add items to your wishlist.',
            ], 401);
        }

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $existing = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($existing) {
            $existing->delete();
            $added = false;
        } else {
            Wishlist::create([
                'user_id' => auth()->id(),
                'product_id' => $validated['product_id'],
            ]);
            $added = true;
        }

        $count = Wishlist::where('user_id', auth()->id())->count();

        return response()->json([
            'success' => true,
            'added' => $added,
            'wishlist_count' => $count,
        ]);
    }

    public function index()
    {
        if (! auth()->check()) {
            return redirect('/');
        }

        $products = Product::with(['primaryImage', 'images', 'weightVariants'])
            ->whereHas('wishlists', fn ($q) => $q->where('user_id', auth()->id()))
            ->where('status', 'active')
            ->get();

        return view('wishlist', compact('products'));
    }
}