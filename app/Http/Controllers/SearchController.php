<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends StorefrontController
{
    public function index(Request $request)
    {
        $query = trim($request->input('q', ''));

        $products = collect();

        if ($query !== '') {
            $products = Product::with(['primaryImage', 'images', 'weightVariants'])
                ->where('status', 'active')
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('short_description', 'like', "%{$query}%")
                      ->orWhere('meta_keywords', 'like', "%{$query}%");
                })
                ->paginate(12)
                ->withQueryString();
        }

        return view('search', compact('products', 'query'));
    }
}