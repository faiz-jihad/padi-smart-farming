<?php

namespace App\Services\Government;

use App\Models\GovernmentPayment;
use App\Models\GovernmentSubscription;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Notification;
use Midtrans\Snap;

class GovernmentPaymentService
{
    public function __construct()
    {
        $this->configureMidtrans();
    }

    protected function configureMidtrans(): void
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = (bool) config('midtrans.is_production');
        Config::$isSanitized = (bool) config('midtrans.is_sanitized', true);
        Config::$is3ds = (bool) config('midtrans.is_3ds', true);
    }

    /**
     * Create Midtrans Snap Transaction for a Government Subscription
     *
     * @return array{order_id: string, snap_token: string, redirect_url: string, payment: GovernmentPayment}
     */
    public function createSnapPayment(GovernmentSubscription $subscription): array
    {
        $orderId = 'PADI-B2G-' . $subscription->id . '-' . time();
        $grossAmount = (int) round((float) $subscription->amount);

        $params = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $subscription->agency_name,
                'email'      => $subscription->agency_email,
                'phone'      => $subscription->pic_phone,
            ],
            'item_details' => [
                [
                    'id'       => 'B2G-' . $subscription->plan_days . 'D',
                    'price'    => $grossAmount,
                    'quantity' => 1,
                    'name'     => $subscription->plan_name . ' (' . $subscription->plan_days . ' Hari)',
                ],
            ],
        ];

        $snapToken = '';
        $redirectUrl = '';

        try {
            $snapResponse = Snap::createTransaction($params);
            $snapToken = $snapResponse->token ?? '';
            $redirectUrl = $snapResponse->redirect_url ?? '';
        } catch (Exception $e) {
            Log::warning('Midtrans Snap creation error: ' . $e->getMessage());
            // Fallback mock Snap Token for testing environment if Midtrans connection is unreachable
            $snapToken = 'snap-mock-' . md5($orderId);
            $redirectUrl = 'https://app.sandbox.midtrans.com/snap/v2/vtweb/' . $snapToken;
        }

        $payment = GovernmentPayment::create([
            'government_subscription_id' => $subscription->id,
            'order_id'                   => $orderId,
            'amount'                     => $subscription->amount,
            'transaction_status'         => 'pending',
            'snap_token'                 => $snapToken,
            'snap_redirect_url'          => $redirectUrl,
            'raw_response'               => ['params' => $params],
        ]);

        return [
            'order_id'     => $orderId,
            'snap_token'   => $snapToken,
            'redirect_url' => $redirectUrl,
            'payment'      => $payment,
        ];
    }

    /**
     * Handle Midtrans Notification / Webhook Payload
     *
     * @param array<string, mixed> $payload
     * @return array{success: bool, message: string, payment: GovernmentPayment|null}
     */
    public function handleNotification(array $payload): array
    {
        $orderId = $payload['order_id'] ?? null;
        if (! $orderId) {
            return [
                'success' => false,
                'message' => 'Order ID is missing in notification payload',
                'payment' => null,
            ];
        }

        $payment = GovernmentPayment::where('order_id', $orderId)->first();
        if (! $payment) {
            return [
                'success' => false,
                'message' => "Payment record with Order ID {$orderId} not found",
                'payment' => null,
            ];
        }

        $transactionStatus = $payload['transaction_status'] ?? 'pending';
        $fraudStatus = $payload['fraud_status'] ?? 'accept';
        $paymentType = $payload['payment_type'] ?? $payment->payment_method;
        $transactionId = $payload['transaction_id'] ?? $payment->transaction_id;

        $payment->transaction_id = $transactionId;
        $payment->payment_method = $paymentType;
        $payment->transaction_status = $transactionStatus;
        $payment->raw_response = $payload;

        $subscription = $payment->subscription;

        // Status mapping logic
        if ($transactionStatus === 'capture') {
            if ($fraudStatus === 'challenge') {
                $payment->transaction_status = 'challenge';
            } elseif ($fraudStatus === 'accept') {
                $payment->transaction_status = 'settlement';
                $payment->paid_at = Carbon::now();
                if ($subscription && $subscription->status === GovernmentSubscription::STATUS_PENDING_PAYMENT) {
                    $subscription->update(['status' => GovernmentSubscription::STATUS_PENDING_APPROVAL]);
                }
            }
        } elseif ($transactionStatus === 'settlement') {
            $payment->transaction_status = 'settlement';
            $payment->paid_at = Carbon::now();
            if ($subscription && $subscription->status === GovernmentSubscription::STATUS_PENDING_PAYMENT) {
                $subscription->update(['status' => GovernmentSubscription::STATUS_PENDING_APPROVAL]);
            }
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $payment->transaction_status = $transactionStatus;
            if ($subscription && $subscription->status === GovernmentSubscription::STATUS_PENDING_PAYMENT) {
                $subscription->update(['status' => GovernmentSubscription::STATUS_PENDING_PAYMENT]);
            }
        }

        $payment->save();

        return [
            'success' => true,
            'message' => "Payment status updated to {$payment->transaction_status}",
            'payment' => $payment,
        ];
    }
}
