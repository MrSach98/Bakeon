<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Flavor;
use App\Models\Occasion;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryPageController extends StorefrontController
{
    public function show(string $slug, Request $request)
{
    // Slug aur name (slugified) dono se check karega
    $category = Category::where('is_active', true)
        ->where(function ($q) use ($slug) {
            $q->where('slug', $slug)
              ->orWhere('name', str_replace('-', ' ', $slug));
        })
        ->firstOrFail();

    $categoryIds = collect([$category->id]);
    foreach ($category->children as $sub) {
        $categoryIds->push($sub->id);
        foreach ($sub->children as $child) {
            $categoryIds->push($child->id);
        }
    }

        $query = Product::with(['primaryImage', 'images', 'weightVariants.weight', 'flavors', 'occasions'])
            ->where('status', 'active')
            ->where(function ($q) use ($categoryIds) {
                $q->whereIn('category_id', $categoryIds)
                  ->orWhereIn('subcategory_id', $categoryIds)
                  ->orWhereIn('child_category_id', $categoryIds);
            });

        if ($request->filled('flavor')) {
            $query->whereHas('flavors', fn ($q) => $q->whereIn('flavors.id', (array) $request->flavor));
        }

        if ($request->filled('egg_type')) {
            $query->where('egg_type', $request->egg_type);
        }

        if ($request->filled('occasion')) {
            $query->whereHas('occasions', fn ($q) => $q->whereIn('occasions.id', (array) $request->occasion));
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->whereHas('weightVariants', function ($q) use ($request) {
                if ($request->filled('min_price')) {
                    $q->where('price', '>=', $request->min_price);
                }
                if ($request->filled('max_price')) {
                    $q->where('price', '<=', $request->max_price);
                }
            });
        }

        match ($request->input('sort')) {
            'price_low' => $query->orderBy('base_price', 'asc'),
            'price_high' => $query->orderBy('base_price', 'desc'),
            'bestseller' => $query->orderByDesc('is_bestseller'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        $availableFlavors = Flavor::whereHas('products', function ($q) use ($categoryIds) {
            $q->whereIn('category_id', $categoryIds)
              ->orWhereIn('subcategory_id', $categoryIds)
              ->orWhereIn('child_category_id', $categoryIds);
        })->where('is_active', true)->orderBy('name')->get();

        $availableOccasions = Occasion::whereHas('products', function ($q) use ($categoryIds) {
            $q->whereIn('category_id', $categoryIds)
              ->orWhereIn('subcategory_id', $categoryIds)
              ->orWhereIn('child_category_id', $categoryIds);
        })->where('is_active', true)->orderBy('name')->get();

        return view('category', compact('category', 'products', 'availableFlavors', 'availableOccasions'));
    }
}