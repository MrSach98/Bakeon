<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; background:#f7f2ec; padding:20px; margin:0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; margin:0 auto; background:#fff; border-radius:12px; overflow:hidden;">
        <tr>
            <td style="background:#A31E42; padding:24px; text-align:center; color:#fff; font-size:1.3rem; font-weight:bold;">
                🎂 {{ config('app.name') }}
            </td>
        </tr>

        <tr>
            <td style="padding:24px 30px;">
                <h2 style="color:#2B2B2B; margin-top:0;">Thank you, {{ $order->customer_name }}!</h2>
                <p style="color:#555;">Your order has been placed successfully.</p>

                <table width="100%" style="margin:16px 0; background:#FFF8F0; border-radius:8px; padding:12px;">
                    <tr>
                        <td style="padding:8px; color:#888; font-size:0.85rem;">Order Number</td>
                        <td style="padding:8px; text-align:right; font-weight:bold;">{{ $order->order_number }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px; color:#888; font-size:0.85rem;">Order Date</td>
                        <td style="padding:8px; text-align:right;">{{ $order->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px; color:#888; font-size:0.85rem;">Payment Method</td>
                        <td style="padding:8px; text-align:right;">{{ $order->payment_method === 'online' ? 'Paid Online' : 'Cash on Delivery' }}</td>
                    </tr>
                </table>

                <h3 style="color:#2B2B2B; font-size:1rem; border-bottom:1px solid #eee; padding-bottom:8px;">Order Items</h3>

                <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;">
                    @foreach ($order->items as $item)
                        <tr style="border-bottom:1px solid #f0f0f0;">
                            <td style="font-size:0.9rem;">
                                <strong>{{ $item->product_name }}</strong><br>
                                <span style="color:#888; font-size:0.8rem;">
                                    {{ $item->weight_label }}
                                    @if ($item->egg_type) &middot; {{ ucfirst($item->egg_type) }} @endif
                                    &middot; Qty: {{ $item->quantity }}
                                </span>
                            </td>
                            <td style="text-align:right; font-weight:bold; font-size:0.9rem;">₹{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </table>

                <table width="100%" style="margin-top:16px;">
                    <tr>
                        <td style="color:#888; padding:4px 0;">Subtotal</td>
                        <td style="text-align:right; padding:4px 0;">₹{{ number_format($order->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="color:#888; padding:4px 0;">Delivery Charge</td>
                        <td style="text-align:right; padding:4px 0;">₹{{ number_format($order->delivery_charge, 2) }}</td>
                    </tr>
                    @if ($order->discount_amount > 0)
                        <tr>
                            <td style="color:#1a7a3c; padding:4px 0;">Discount ({{ $order->coupon_code }})</td>
                            <td style="text-align:right; color:#1a7a3c; padding:4px 0;">-₹{{ number_format($order->discount_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr style="border-top:2px solid #A31E42;">
                        <td style="font-weight:bold; font-size:1.1rem; padding-top:8px;">Total</td>
                        <td style="text-align:right; font-weight:bold; font-size:1.1rem; color:#A31E42; padding-top:8px;">₹{{ number_format($order->total_amount, 2) }}</td>
                    </tr>
                </table>

                <h3 style="color:#2B2B2B; font-size:1rem; border-bottom:1px solid #eee; padding-bottom:8px; margin-top:24px;">Delivery Details</h3>
                <p style="color:#555; font-size:0.9rem; margin:4px 0;">
                    <strong>{{ $order->receiver_name }}</strong> &middot; {{ $order->receiver_phone }}<br>
                    {{ $order->delivery_address }}<br>
                    Delivery Date: {{ $order->delivery_date?->format('d M Y') }}
                    @if ($order->delivery_time_slot) ({{ $order->delivery_time_slot }}) @endif
                </p>

                <div style="text-align:center; margin-top:30px;">
                    <a href="{{ url('/order-confirmation/' . $order->order_number) }}" style="background:#A31E42; color:#fff; padding:12px 28px; border-radius:6px; text-decoration:none; font-weight:bold;">
                        Track Your Order
                    </a>
                </div>
            </td>
        </tr>

        <tr>
            <td style="background:#f7f2ec; padding:16px; text-align:center; color:#999; font-size:0.75rem;">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </td>
        </tr>
    </table>
</body>
</html>