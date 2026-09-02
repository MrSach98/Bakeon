<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Review;
use App\Models\ReviewImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ReviewController extends StorefrontController
{
    private const MAX_IMAGES = 5;

    /**
     * Returns products the logged-in user can review right now —
     * only from DELIVERED orders, and only if not already reviewed.
     * This is what genuinely prevents fake reviews.
     */
    public function reviewableItems(Request $request, int $productId)
    {
        if (! auth()->check()) {
            return response()->json(['items' => []]);
        }

        $items = OrderItem::with('order')
            ->where('product_id', $productId)
            ->whereHas('order', function ($q) {
                $q->where('user_id', auth()->id())
                  ->where('status', 'delivered');
            })
            ->whereDoesntHave('review')
            ->get(['id', 'order_id', 'product_name', 'weight_label', 'quantity']);

        return response()->json(['items' => $items]);
    }

    public function store(Request $request)
    {
        if (! auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Please login to submit a review.'], 401);
        }

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'order_item_id' => ['required', 'integer', 'exists:order_items,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:150'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'images' => ['nullable', 'array', 'max:' . self::MAX_IMAGES],
            'images.*' => ['image', 'max:2048'],
        ]);

        // Server-side proof: this order_item must genuinely belong to this
        // user, be for this exact product, and come from a delivered order.
        $orderItem = OrderItem::with('order')
            ->where('id', $validated['order_item_id'])
            ->where('product_id', $validated['product_id'])
            ->whereHas('order', function ($q) {
                $q->where('user_id', auth()->id())
                  ->where('status', 'delivered');
            })
            ->first();

        if (! $orderItem) {
            return response()->json(['success' => false, 'message' => 'You can only review products from your delivered orders.'], 403);
        }

        $alreadyReviewed = Review::where('user_id', auth()->id())
            ->where('order_item_id', $orderItem->id)
            ->exists();

        if ($alreadyReviewed) {
            return response()->json(['success' => false, 'message' => 'You have already reviewed this item.'], 422);
        }

        $review = Review::create([
            'product_id' => $validated['product_id'],
            'user_id' => auth()->id(),
            'order_item_id' => $orderItem->id,
            'rating' => $validated['rating'],
            'title' => $validated['title'] ?? null,
            'comment' => $validated['comment'] ?? null,
            'status' => 'pending', // admin approve karega
        ]);

        if ($request->hasFile('images')) {
            $destination = public_path('userassets/reviews');
            if (! File::exists($destination)) {
                File::makeDirectory($destination, 0755, true);
            }

            foreach ($request->file('images') as $image) {
                $filename = time() . '_' . Str::random(8) . '.' . $image->getClientOriginalExtension();
                $image->move($destination, $filename);

                ReviewImage::create([
                    'review_id' => $review->id,
                    'image_path' => 'userassets/reviews/' . $filename,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Thank you! Your review has been submitted and will appear after approval.',
        ]);
    }
}