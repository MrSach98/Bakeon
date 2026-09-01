<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\WebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    /**
     * Razorpay hits this URL directly (server-to-server) whenever a
     * payment event happens. This is NOT tied to the customer's browser —
     * it's the safety net if verifyAndPlaceOrder never got called
     * (e.g. customer closed the tab right after paying).
     */
    public function handle(Request $request)
    {
        $webhookSecret = config('services.razorpay.webhook_secret');
        $signature = $request->header('X-Razorpay-Signature');
        $payload = $request->getContent();

        // Verify this request genuinely came from Razorpay, not a fake caller
        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        if (! hash_equals($expectedSignature, (string) $signature)) {
            Log::warning('Razorpay webhook: invalid signature');
            return response()->json(['status' => 'invalid signature'], 400);
        }

        $data = json_decode($payload, true);
        $eventId = $request->header('X-Razorpay-Event-Id') ?? ($data['id'] ?? null);

        // Idempotency: never process the same webhook event twice
        if ($eventId && WebhookEvent::where('razorpay_event_id', $eventId)->exists()) {
            return response()->json(['status' => 'already processed']);
        }

        if ($eventId) {
            WebhookEvent::create([
                'razorpay_event_id' => $eventId,
                'event_type' => $data['event'] ?? 'unknown',
                'payload' => $data,
            ]);
        }

        $event = $data['event'] ?? null;

        if ($event === 'payment.captured') {
            $this->handlePaymentCaptured($data);
        }

        if ($event === 'payment.failed') {
            $this->handlePaymentFailed($data);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * If our own verifyAndPlaceOrder flow already created the order and
     * marked payment as 'paid', this is a no-op — we just confirm it's
     * already correctly recorded. If for some reason it's still 'created'
     * (browser closed before verify call reached us), we flag it here
     * for manual review rather than silently losing the payment.
     */
    private function handlePaymentCaptured(array $data): void
    {
        $razorpayPaymentId = $data['payload']['payment']['entity']['id'] ?? null;
        $razorpayOrderId = $data['payload']['payment']['entity']['order_id'] ?? null;

        if (! $razorpayOrderId) {
            return;
        }

        $payment = Payment::where('razorpay_order_id', $razorpayOrderId)->first();

        if (! $payment) {
            Log::warning("Razorpay webhook: payment.captured for unknown order {$razorpayOrderId}");
            return;
        }

        if ($payment->status === 'paid') {
            // Already handled correctly by the normal verify flow — nothing to do
            return;
        }

        // Payment succeeded at Razorpay's end, but our order was never created
        // (customer likely closed the browser mid-flow). Flag this loudly so
        // admin can manually follow up rather than the customer losing money
        // with no order to show for it.
        $payment->update([
            'razorpay_payment_id' => $razorpayPaymentId,
            'status' => 'paid',
            'meta' => array_merge($payment->meta ?? [], ['needs_manual_order_creation' => true]),
        ]);

        Log::critical("Razorpay payment {$razorpayPaymentId} captured but no order was created — needs manual review. Order ID: {$razorpayOrderId}, Amount: {$payment->amount}");

        // Optional: email admin immediately about this
        // Mail::to(config('mail.admin_email'))->send(new UnfulfilledPaymentAlert($payment));
    }

    private function handlePaymentFailed(array $data): void
    {
        $razorpayOrderId = $data['payload']['payment']['entity']['order_id'] ?? null;

        if (! $razorpayOrderId) {
            return;
        }

        Payment::where('razorpay_order_id', $razorpayOrderId)
            ->where('status', 'created')
            ->update(['status' => 'failed']);
    }
}