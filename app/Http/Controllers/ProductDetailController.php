<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductDetailController extends StorefrontController
{
    public function show(string $slug, Request $request)
    {
        $product = Product::with([
            'images', 'weightVariants.weight', 'flavors', 'addons',
            'occasions', 'deliveryOptions', 'category',
        ])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        // Recently Viewed — keep most-recent-first, exclude current product from the list shown,
        // but push current product to front for the NEXT page visit
        $previouslyViewedIds = collect(session('recently_viewed', []))
            ->reject(fn ($id) => $id === $product->id)
            ->values();

        $recentlyViewed = $previouslyViewedIds->isNotEmpty()
            ? Product::with(['primaryImage', 'images', 'weightVariants'])
                ->where('status', 'active')
                ->whereIn('id', $previouslyViewedIds)
                ->get()
                ->sortBy(fn ($p) => $previouslyViewedIds->search($p->id))
                ->values()
            : collect();

        session([
            'recently_viewed' => collect([$product->id])
                ->concat($previouslyViewedIds)
                ->unique()
                ->take(10)
                ->values()
                ->toArray(),
        ]);

        $relatedProducts = Product::with(['primaryImage', 'images', 'weightVariants'])
            ->where('status', 'active')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(8)
            ->get();

        // Group by weight_id, then by egg_type within that weight —
        // lets the UI show an egg/eggless toggle only when both exist for the selected weight
        $variantsByWeight = $product->weightVariants
            ->where('is_active', true)
            ->groupBy('weight_id');

        $defaultVariant = $product->defaultVariant();

        $servingInfo = $product->weightVariants
            ->where('is_active', true)
            ->pluck('weight')
            ->unique('id')
            ->sortBy('sort_order')
            ->values();

        return view('product-detail', compact(
            'product', 'relatedProducts', 'recentlyViewed',
            'variantsByWeight', 'defaultVariant', 'servingInfo'
        ));
    }
}