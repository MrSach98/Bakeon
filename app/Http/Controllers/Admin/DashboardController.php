<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\DeliveryOption;
use App\Models\Flavor;
use App\Models\Occasion;
use App\Models\Product;
use App\Models\ServiceablePincode;
use App\Models\Weight;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_products' => Product::count(),
            'active_products' => Product::where('status', 'active')->count(),
            'draft_products' => Product::where('status', 'draft')->count(),
            'total_categories' => Category::whereNull('parent_id')->count(),
            'total_subcategories' => Category::whereNotNull('parent_id')->count(),
            'total_flavors' => Flavor::count(),
            'total_weights' => Weight::count(),
            'total_addons' => Addon::count(),
            'total_occasions' => Occasion::count(),
            'total_delivery_options' => DeliveryOption::count(),
            'total_pincodes' => ServiceablePincode::count(),
            'active_pincodes' => ServiceablePincode::where('is_active', true)->count(),
            'total_coupons' => Coupon::count(),
            'active_coupons' => Coupon::where('is_active', true)->count(),
            'featured_products' => Product::where('is_featured', true)->count(),
            'bestseller_products' => Product::where('is_bestseller', true)->count(),
        ];

        $recentProducts = Product::with('category')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentProducts'));
    }
}