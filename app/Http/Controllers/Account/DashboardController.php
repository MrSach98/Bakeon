<?php

namespace App\Http\Controllers\Account;

use App\Models\Order;
use App\Models\Wishlist;
use App\Models\Review;
use App\Models\CartItem;

class DashboardController extends AccountBaseController
{
    public function index()
    {
        $userId = auth()->id();

        $stats = [
            'total_orders' => Order::where('user_id', $userId)->count(),
            'pending_orders' => Order::where('user_id', $userId)->whereNotIn('status', ['delivered', 'cancelled'])->count(),
            'delivered_orders' => Order::where('user_id', $userId)->where('status', 'delivered')->count(),
            'wishlist_count' => Wishlist::where('user_id', $userId)->count(),
            'reviews_count' => Review::where('user_id', $userId)->count(),
            'cart_count' => (int) CartItem::where('user_id', $userId)->where('status', 'active')->sum('quantity'),
        ];

        $recentOrders = Order::with('items')
            ->where('user_id', $userId)
            ->latest()
            ->take(5)
            ->get();

        return view('account.dashboard', compact('stats', 'recentOrders'));
    }
}