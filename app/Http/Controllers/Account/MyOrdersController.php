<?php

namespace App\Http\Controllers\Account;

use App\Models\Order;

class MyOrdersController extends AccountBaseController
{
    public function index()
    {
        $orders = Order::with(['items', 'deliveryOption'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('account.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        // Ownership check — koi bhi doosre user ka order URL se access nahi kar sakta
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['items.product', 'deliveryOption', 'coupon']);

        return view('account.orders.show', compact('order'));
    }
}