<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Occasion;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

abstract class StorefrontController extends Controller
{
    public function __construct(Request $request)
    {
        $siteSettings = SiteSetting::current();

        $headerCategories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => function ($q) {
                $q->where('is_active', true)->with(['children' => function ($cq) {
                    $cq->where('is_active', true);
                }]);
            }])
            ->orderBy('sort_order')
            ->take(8)
            ->get();

        $footerCategories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->take(6)
            ->get();

        $footerOccasions = Occasion::where('is_active', true)
            ->orderBy('name')
            ->take(6)
            ->get();

        $selectedCity = session('selected_city');

        $cartOwner = auth()->check()
            ? ['user_id' => auth()->id()]
            : ($request->cookie('guest_token') ? ['guest_token' => $request->cookie('guest_token')] : null);

        $cartCount = $cartOwner
            ? (int) CartItem::where($cartOwner)->where('status', 'active')->sum('quantity')
            : 0;

        $wishlistCount = auth()->check()? \App\Models\Wishlist::where('user_id', auth()->id())->count(): 0;

        View::share([
            'siteSettings' => $siteSettings,
            'headerCategories' => $headerCategories,
            'footerCategories' => $footerCategories,
            'footerOccasions' => $footerOccasions,
            'selectedCity' => $selectedCity,
            'cartCount' => $cartCount,
            'wishlistCount' => $wishlistCount,
            'paymentEnabled' => (bool) $siteSettings->online_payment_enabled,
        ]);
    }
}