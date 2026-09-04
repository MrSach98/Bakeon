<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class TrackOrderController extends StorefrontController
{
    public function show()
    {
        return view('track-order', ['order' => null]);
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string'],
            'phone' => ['required', 'string'],
        ]);

        $order = Order::with(['items', 'deliveryOption'])
            ->where('order_number', $validated['order_number'])
            ->where('customer_phone', $validated['phone'])
            ->first();

        if (! $order) {
            return back()->withErrors(['order_number' => 'No order found with this order number and phone number.']);
        }

        return view('track-order', compact('order'));
    }
}