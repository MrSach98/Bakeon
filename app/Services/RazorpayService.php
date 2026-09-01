<?php

namespace App\Services;

use Razorpay\Api\Api;

class RazorpayService
{
    private Api $api;

    public function __construct()
    {
        $this->api = new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );
    }

    /**
     * Creates a Razorpay order server-to-server. The amount comes ONLY
     * from server-side calculation — never from client input.
     */
    public function createOrder(float $amountInRupees, string $receipt): array
    {
        $amountInPaise = (int) round($amountInRupees * 100);

        $order = $this->api->order->create([
            'receipt' => $receipt,
            'amount' => $amountInPaise,
            'currency' => 'INR',
            'payment_capture' => 1,
        ]);

        return [
            'id' => $order->id,
            'amount' => $order->amount,
        ];
    }

    /**
     * Verifies the payment signature using HMAC SHA256 with the secret key.
     * This is the only proof that a payment is genuine and untampered.
     */
    public function verifySignature(string $razorpayOrderId, string $razorpayPaymentId, string $razorpaySignature): bool
    {
        try {
            $this->api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature' => $razorpaySignature,
            ]);

            return true;
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }
}