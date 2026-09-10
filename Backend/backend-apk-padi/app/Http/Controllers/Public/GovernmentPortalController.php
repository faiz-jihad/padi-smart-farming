<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\GovernmentSubscription;
use App\Services\Government\GovernmentDataService;
use App\Services\Government\GovernmentPaymentService;
use App\Services\Government\GovernmentSubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GovernmentPortalController extends Controller
{
    public function __construct(
        protected GovernmentSubscriptionService $subscriptionService,
        protected GovernmentPaymentService $paymentService,
        protected GovernmentDataService $dataService
    ) {}

    /**
     * Public B2G Landing & Subscription Portal
     */
    public function index(): View
    {
        $overview = $this->dataService->getOverviewData();
        $insights = $this->dataService->getInsightsData();

        return view('public.government.index', [
            'title'     => 'Akses Data Pertanian Pemerintah (B2G)',
            'overview'  => $overview,
            'insights'  => $insights,
            'planName'  => config('b2g.plan_name', 'Government Data Access'),
            'planDays'  => config('b2g.plan_days', 30),
            'planPrice' => config('b2g.plan_price', 2500000),
        ]);
    }

    /**
     * Submit subscription registration & redirect to WhatsApp Billing
     */
    public function subscribe(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'agency_name'  => ['required', 'string', 'max:255'],
            'agency_email' => ['required', 'email', 'max:255'],
            'pic_name'     => ['required', 'string', 'max:255'],
            'pic_phone'    => ['required', 'string', 'max:30'],
            'purpose'      => ['nullable', 'string', 'max:1000'],
        ]);

        $subscription = $this->subscriptionService->registerSubscription($validated);

        return redirect()->route('government.checkout', ['subscription' => $subscription->id])
            ->with('status', 'Pendaftaran data instansi berhasil disimpan. Silakan hubungi Admin via WhatsApp untuk konfirmasi dan instruksi pembayaran.');
    }

    /**
     * Billing / Checkout page with official WhatsApp Admin confirmation
     */
    public function checkout(GovernmentSubscription $subscription): View
    {
        $subscription->load(['latestPayment']);
        $latestPayment = $subscription->latestPayment;

        $adminPhone = preg_replace('/[^0-9]/', '', (string) config('b2g.whatsapp_number', '6281234567890'));
        $formattedAmount = 'Rp ' . number_format($subscription->amount, 0, ',', '.');

        $message = "Halo Admin P.A.D.I.,\n\n"
            . "Saya ingin melakukan konfirmasi pembayaran dan meminta instruksi pembayaran untuk langganan Government Data Center (B2G):\n\n"
            . "- Nama Instansi: {$subscription->agency_name}\n"
            . "- Nama PIC: {$subscription->pic_name}\n"
            . "- Email Resmi: {$subscription->agency_email}\n"
            . "- Nomor HP/WhatsApp: {$subscription->pic_phone}\n"
            . "- Paket: {$subscription->plan_name}\n"
            . "- Durasi: {$subscription->plan_days} Hari\n"
            . "- Nominal Tagihan: {$formattedAmount}\n"
            . "- ID Registrasi: #{$subscription->id}\n\n"
            . "Mohon instruksi nomor rekening atau metode pembayaran resmi untuk aktivasi akses data kedinasan kami. Terima kasih.";

        $whatsappUrl = "https://wa.me/{$adminPhone}?text=" . rawurlencode($message);

        return view('public.government.checkout', [
            'title'        => 'Billing & Konfirmasi Pembayaran B2G - P.A.D.I.',
            'subscription' => $subscription,
            'payment'      => $latestPayment,
            'whatsappUrl'  => $whatsappUrl,
            'adminPhone'   => $adminPhone,
        ]);
    }

    /**
     * Public Status Check for Subscription
     */
    public function status(GovernmentSubscription $subscription): View
    {
        $subscription->load(['latestPayment']);

        return view('public.government.status', [
            'title'        => 'Status Langganan Data Pemerintah - ' . $subscription->agency_name,
            'subscription' => $subscription,
        ]);
    }

    /**
     * Public Interactive API Documentation
     */
    public function docs(): View
    {
        return view('public.government.docs', [
            'title'      => 'Dokumentasi B2G API - P.A.D.I. Smart Farming',
            'baseApiUrl' => config('b2g.base_api_url', url('/api/v1/government')),
        ]);
    }
}
