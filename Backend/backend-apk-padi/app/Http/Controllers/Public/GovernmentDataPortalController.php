<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\GovernmentSubscription;
use App\Services\Government\GovernmentDataService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GovernmentDataPortalController extends Controller
{
    public function __construct(
        protected GovernmentDataService $dataService
    ) {}

    /**
     * Helper to retrieve currently authenticated active subscription from session
     */
    protected function getAuthenticatedSubscription(Request $request): ?GovernmentSubscription
    {
        $subscriptionId = session('b2g_portal_subscription_id');
        $tokenHash = session('b2g_portal_token_hash');

        if (! $subscriptionId || ! $tokenHash) {
            return null;
        }

        $subscription = GovernmentSubscription::find($subscriptionId);

        if (! $subscription) {
            session()->forget(['b2g_portal_subscription_id', 'b2g_portal_token_hash']);
            return null;
        }

        // Verify token hash consistency
        if ($subscription->token_hash !== $tokenHash) {
            session()->forget(['b2g_portal_subscription_id', 'b2g_portal_token_hash']);
            return null;
        }

        // Check if revoked
        if ($subscription->revoked_at !== null || $subscription->status === GovernmentSubscription::STATUS_REVOKED) {
            session()->forget(['b2g_portal_subscription_id', 'b2g_portal_token_hash']);
            return null;
        }

        // Check if expired
        if ($subscription->expires_at !== null && $subscription->expires_at->isPast()) {
            session()->forget(['b2g_portal_subscription_id', 'b2g_portal_token_hash']);
            return null;
        }

        // Check if active
        if ($subscription->status !== GovernmentSubscription::STATUS_ACTIVE) {
            session()->forget(['b2g_portal_subscription_id', 'b2g_portal_token_hash']);
            return null;
        }

        return $subscription;
    }

    /**
     * Government Portal Authentication Entry Page
     */
    public function portal(Request $request): View|RedirectResponse
    {
        if ($this->getAuthenticatedSubscription($request)) {
            return redirect()->route('government.portal.dashboard');
        }

        return view('public.government.portal.access', [
            'title' => 'Akses Government Data Portal (B2G) - P.A.D.I. Smart Farming',
        ]);
    }

    /**
     * Authenticate Government Portal using 6-Digit Token
     */
    public function access(Request $request): RedirectResponse
    {
        $rawToken = trim((string) $request->input('token', ''));
        $cleanToken = preg_replace('/[^0-9]/', '', $rawToken);

        if (strlen($cleanToken) !== 6) {
            return back()
                ->withInput()
                ->with('error', 'Format token tidak valid. Masukkan 6-digit token angka resmi yang Anda terima.');
        }

        $tokenHash = hash('sha256', $cleanToken);
        $subscription = GovernmentSubscription::where('token_hash', $tokenHash)->first();

        if (! $subscription) {
            return back()
                ->withInput()
                ->with('error', 'Token akses kedinasan tidak ditemukan atau tidak valid.');
        }

        if ($subscription->revoked_at !== null || $subscription->status === GovernmentSubscription::STATUS_REVOKED) {
            return back()
                ->withInput()
                ->with('error', 'Akses ditolak. Token akses subscription instansi telah dicabut (revoked).');
        }

        if ($subscription->expires_at !== null && $subscription->expires_at->isPast()) {
            $expiredDate = $subscription->expires_at->isoFormat('D MMMM Y');
            return back()
                ->withInput()
                ->with('error', "Akses ditolak. Masa berlaku langganan telah berakhir pada {$expiredDate}.");
        }

        if ($subscription->status !== GovernmentSubscription::STATUS_ACTIVE) {
            return back()
                ->withInput()
                ->with('error', "Akses ditolak. Status langganan instansi saat ini tidak aktif ({$subscription->status}).");
        }

        // Store secure session state (NO raw token in session or URL!)
        session([
            'b2g_portal_subscription_id' => $subscription->id,
            'b2g_portal_token_hash'       => $subscription->token_hash,
        ]);

        return redirect()->route('government.portal.dashboard')
            ->with('status', "Selamat datang di Government Data Portal, {$subscription->agency_name}.");
    }

    /**
     * Government Data Portal Dashboard
     */
    public function dashboard(Request $request): View|RedirectResponse
    {
        $subscription = $this->getAuthenticatedSubscription($request);

        if (! $subscription) {
            return redirect()->route('government.portal.index')
                ->with('error', 'Silakan masukkan 6-digit token akses resmi instansi Anda untuk membuka portal data pemerintah.');
        }

        // Reuse verified dataset from GovernmentDataService
        $overview = $this->dataService->getOverviewData();
        $activities = $this->dataService->getActivitiesData($request, 10);
        $diseases = $this->dataService->getDiseasesData($request, 10);
        $productivity = $this->dataService->getProductivityData($request, 10);
        $insights = $this->dataService->getInsightsData();

        return view('public.government.portal.dashboard', [
            'title'        => 'Government Data Portal - ' . $subscription->agency_name,
            'subscription' => $subscription,
            'overview'     => $overview,
            'activities'   => $activities,
            'diseases'     => $diseases,
            'productivity' => $productivity,
            'insights'     => $insights,
            'filters'      => [
                'start_date'    => $request->query('start_date', ''),
                'end_date'      => $request->query('end_date', ''),
                'region'        => $request->query('region', ''),
                'commodity'     => $request->query('commodity', ''),
                'activity_type' => $request->query('activity_type', ''),
                'disease'       => $request->query('disease', ''),
                'tab'           => $request->query('tab', 'farms'),
            ],
        ]);
    }

    /**
     * Printable Official Government Report (A4 Print / PDF View)
     */
    public function report(Request $request): View|RedirectResponse
    {
        $subscription = $this->getAuthenticatedSubscription($request);

        if (! $subscription) {
            return redirect()->route('government.portal.index')
                ->with('error', 'Sesi portal telah berakhir. Silakan autentikasi kembali dengan token akses.');
        }

        // Fetch larger dataset for print report
        $overview = $this->dataService->getOverviewData();
        $productivity = $this->dataService->getProductivityData($request, 50);
        $activities = $this->dataService->getActivitiesData($request, 50);
        $diseases = $this->dataService->getDiseasesData($request, 50);
        $insights = $this->dataService->getInsightsData();

        return view('public.government.portal.report', [
            'title'        => 'Laporan Data Pertanian Terpadu B2G - ' . $subscription->agency_name,
            'subscription' => $subscription,
            'overview'     => $overview,
            'productivity' => $productivity,
            'activities'   => $activities,
            'diseases'     => $diseases,
            'insights'     => $insights,
            'generatedAt'  => Carbon::now(),
            'filters'      => [
                'start_date'    => $request->query('start_date', ''),
                'end_date'      => $request->query('end_date', ''),
                'region'        => $request->query('region', ''),
                'commodity'     => $request->query('commodity', ''),
                'activity_type' => $request->query('activity_type', ''),
                'disease'       => $request->query('disease', ''),
            ],
        ]);
    }

    /**
     * Logout from Government Portal
     */
    public function logout(Request $request): RedirectResponse
    {
        session()->forget(['b2g_portal_subscription_id', 'b2g_portal_token_hash']);

        return redirect()->route('government.portal.index')
            ->with('status', 'Anda telah berhasil keluar dari sesi Government Data Portal.');
    }
}
