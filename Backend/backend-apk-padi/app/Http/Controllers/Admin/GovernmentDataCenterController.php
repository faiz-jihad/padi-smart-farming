<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GovernmentSubscription;
use App\Services\Government\GovernmentDataService;
use App\Services\Government\GovernmentSubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GovernmentDataCenterController extends Controller
{
    public function __construct(
        protected GovernmentSubscriptionService $subscriptionService,
        protected GovernmentDataService $dataService
    ) {}

    public function index(Request $request): View
    {
        $kpis = $this->subscriptionService->getKpiMetrics();
        $subscriptions = $this->subscriptionService->getAdminSubscriptions($request);
        $overview = $this->dataService->getOverviewData();
        $activities = $this->dataService->getActivitiesData($request, 10);
        $diseases = $this->dataService->getDiseasesData($request, 10);
        $productivity = $this->dataService->getProductivityData($request, 10);
        $insights = $this->dataService->getInsightsData();

        return view('admin.government-data.index', [
            'title'         => 'Government Data Center (B2G)',
            'kpis'          => $kpis,
            'subscriptions' => $subscriptions,
            'overview'      => $overview,
            'activities'    => $activities,
            'diseases'      => $diseases,
            'productivity'  => $productivity,
            'insights'      => $insights,
            'filters'       => [
                'search'        => $request->query('search', ''),
                'status'        => $request->query('status', ''),
                'start_date'    => $request->query('start_date', ''),
                'end_date'      => $request->query('end_date', ''),
                'region'        => $request->query('region', ''),
                'commodity'     => $request->query('commodity', ''),
                'activity_type' => $request->query('activity_type', ''),
                'disease'       => $request->query('disease', ''),
                'tab'           => $request->query('tab', 'subscriptions'),
            ],
        ]);
    }

    public function show(GovernmentSubscription $subscription): View
    {
        $subscription->load(['payments', 'latestPayment']);

        return view('admin.government-data.show', [
            'title'        => 'Detail Subscription Government - ' . $subscription->agency_name,
            'subscription' => $subscription,
        ]);
    }

    public function confirmPayment(Request $request, GovernmentSubscription $subscription): RedirectResponse
    {
        $this->subscriptionService->confirmPayment($subscription);

        return back()->with('status', "Pembayaran untuk instansi {$subscription->agency_name} berhasil dikonfirmasi secara manual. Status kini Menunggu Approval.");
    }

    public function approve(Request $request, GovernmentSubscription $subscription): RedirectResponse
    {
        $result = $this->subscriptionService->approveAndGenerateToken($subscription);
        $rawToken = $result['raw_token'];

        return back()
            ->with('status', "Subscription instansi {$subscription->agency_name} berhasil disetujui & diaktifkan selama {$subscription->plan_days} hari.")
            ->with('generated_token', $rawToken)
            ->with('generated_agency', $subscription->agency_name)
            ->with('generated_expiry', $subscription->expires_at?->isoFormat('D MMMM Y'));
    }

    public function revoke(Request $request, GovernmentSubscription $subscription): RedirectResponse
    {
        $this->subscriptionService->revokeSubscription($subscription);

        return back()->with('status', "Token akses subscription instansi {$subscription->agency_name} berhasil dicabut (revoked).");
    }

    public function reject(Request $request, GovernmentSubscription $subscription): RedirectResponse
    {
        $this->subscriptionService->rejectSubscription($subscription);

        return back()->with('status', "Pendaftaran subscription instansi {$subscription->agency_name} ditolak.");
    }
}
