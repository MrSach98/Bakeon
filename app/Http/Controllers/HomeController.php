<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Product;
use App\Models\ServiceablePincode;

class HomeController extends StorefrontController
{
    public function index()
    {
        $heroBanners = Banner::where('is_active', true)->where('type', 'hero')->orderBy('sort_order')->get();
        $promoStripBanner = Banner::where('is_active', true)->where('type', 'promo_strip')->orderBy('sort_order')->first();
        $occasionReminderBanner = Banner::where('is_active', true)->where('type', 'occasion_reminder')->first();
        $appDealBanner = Banner::where('is_active', true)->where('type', 'app_deal')->first();

        // Quick-filter "Menu" chip row reuses top-level categories
        $menuChips = \App\Models\Category::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->take(10)
            ->get();

        $bestsellers = Product::with(['primaryImage', 'images', 'weightVariants'])
            ->where('status', 'active')
            ->where('is_bestseller', true)
            ->latest()
            ->take(8)
            ->get();

        // Placeholder until Instagram auto-sync is built
        $instagramPosts = collect();

        $deliveryCities = ServiceablePincode::where('is_active', true)
            ->distinct()
            ->orderBy('city')
            ->pluck('city');
        $featured = Product::with(['primaryImage', 'images', 'weightVariants'])
           ->where('status', 'active')->where('is_featured', true)->latest()->take(8)->get();

        return view('index', compact(
            'heroBanners', 'promoStripBanner', 'occasionReminderBanner', 'appDealBanner',
            'menuChips', 'bestsellers', 'instagramPosts', 'deliveryCities', 'featured'
        ));
    }

    public function checkPincode(string $pincode)
    {
        $record = ServiceablePincode::where('pincode', $pincode)->where('is_active', true)->first();

        if (! $record) {
            return response()->json(['serviceable' => false]);
        }

        session(['selected_city' => $record->city, 'selected_pincode' => $record->pincode]);

        return response()->json([
            'serviceable' => true,
            'city' => $record->city,
            'same_day_available' => $record->same_day_available,
            'midnight_available' => $record->midnight_available,
            'express_available' => $record->express_available,
        ]);
    }
}