<?php

namespace App\Services\Government;

use App\Models\GovernmentPayment;
use App\Models\GovernmentSubscription;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GovernmentSubscriptionService
{
    /**
     * Create a new subscription registration
     *
     * @param array<string, mixed> $data
     */
    public function registerSubscription(array $data): GovernmentSubscription
    {
        $planDays = (int) ($data['plan_days'] ?? config('b2g.plan_days', 30));
        $planPrice = (float) ($data['amount'] ?? config('b2g.plan_price', 20000));
        $planName = (string) ($data['plan_name'] ?? config('b2g.plan_name', 'Government Data Access'));

        $subscription = GovernmentSubscription::create([
            'agency_name'  => $data['agency_name'],
            'agency_email' => $data['agency_email'],
            'pic_name'     => $data['pic_name'],
            'pic_phone'    => $data['pic_phone'],
            'purpose'      => $data['purpose'] ?? null,
            'plan_name'    => $planName,
            'plan_days'    => $planDays,
            'amount'       => $planPrice,
            'status'       => GovernmentSubscription::STATUS_PENDING_PAYMENT,
        ]);

        $subscription->payments()->create([
            'order_id'           => 'B2G-WA-' . $subscription->id . '-' . strtoupper(Str::random(6)),
            'amount'             => $planPrice,
            'transaction_status' => 'pending',
            'payment_method'     => 'whatsapp_manual',
        ]);

        return $subscription;
    }

    /**
     * Get paginated subscriptions for Admin with filters
     */
    public function getAdminSubscriptions(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));

        return GovernmentSubscription::query()
            ->with('latestPayment')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $q) use ($search): void {
                    $q->where('agency_name', 'like', "%{$search}%")
                        ->orWhere('agency_email', 'like', "%{$search}%")
                        ->orWhere('pic_name', 'like', "%{$search}%")
                        ->orWhere('pic_phone', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', function (Builder $query) use ($status): void {
                $query->where('status', $status);
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Confirm manual WhatsApp payment for subscription
     */
    public function confirmPayment(GovernmentSubscription $subscription): GovernmentSubscription
    {
        $subscription->update([
            'status' => GovernmentSubscription::STATUS_PENDING_APPROVAL,
        ]);

        $payment = $subscription->latestPayment;
        if ($payment) {
            $payment->update([
                'transaction_status' => 'settlement',
                'payment_method'     => $payment->payment_method ?: 'whatsapp_manual',
                'paid_at'            => Carbon::now(),
            ]);
        } else {
            $subscription->payments()->create([
                'order_id'           => 'B2G-MANUAL-' . $subscription->id . '-' . strtoupper(Str::random(6)),
                'amount'             => $subscription->amount,
                'transaction_status' => 'settlement',
                'payment_method'     => 'whatsapp_manual',
                'paid_at'            => Carbon::now(),
            ]);
        }

        return $subscription;
    }

    /**
     * Approve subscription and generate 6-digit access token
     *
     * @return array{subscription: GovernmentSubscription, raw_token: string}
     */
    public function approveAndGenerateToken(GovernmentSubscription $subscription): array
    {
        $rawToken = $subscription->generateSixDigitToken();

        return [
            'subscription' => $subscription,
            'raw_token'    => $rawToken,
        ];
    }

    /**
     * Revoke an active subscription token
     */
    public function revokeSubscription(GovernmentSubscription $subscription): GovernmentSubscription
    {
        $subscription->revoke();

        return $subscription;
    }

    /**
     * Reject a pending subscription
     */
    public function rejectSubscription(GovernmentSubscription $subscription): GovernmentSubscription
    {
        $subscription->reject();

        return $subscription;
    }

    /**
     * Calculate KPI metrics for Government subscriptions
     *
     * @return array<string, mixed>
     */
    public function getKpiMetrics(): array
    {
        $totalSubscriptions = GovernmentSubscription::count();
        $activeCount = GovernmentSubscription::where('status', GovernmentSubscription::STATUS_ACTIVE)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', Carbon::now())
            ->count();
        $pendingPaymentCount = GovernmentSubscription::where('status', GovernmentSubscription::STATUS_PENDING_PAYMENT)->count();
        $pendingApprovalCount = GovernmentSubscription::where('status', GovernmentSubscription::STATUS_PENDING_APPROVAL)->count();
        $expiredCount = GovernmentSubscription::where(function (Builder $q): void {
            $q->where('status', GovernmentSubscription::STATUS_EXPIRED)
                ->orWhere(function (Builder $sub): void {
                    $sub->where('status', GovernmentSubscription::STATUS_ACTIVE)
                        ->where('expires_at', '<=', Carbon::now());
                });
        })->count();

        $totalRevenue = (float) GovernmentPayment::where('transaction_status', 'settlement')->sum('amount');

        return [
            'total_subscriptions' => $totalSubscriptions,
            'active'              => $activeCount,
            'pending_payment'     => $pendingPaymentCount,
            'pending_approval'    => $pendingApprovalCount,
            'expired'             => $expiredCount,
            'total_revenue'       => $totalRevenue,
        ];
    }
}
