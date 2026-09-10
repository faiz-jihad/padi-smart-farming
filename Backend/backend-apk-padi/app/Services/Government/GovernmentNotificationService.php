<?php

namespace App\Services\Government;

use App\Models\GovernmentSubscription;
use Illuminate\Support\Facades\Route;

class GovernmentNotificationService
{
    /**
     * Normalize PIC phone number to international WhatsApp format (62xxxxxx)
     *
     * Rules:
     * - Strip all non-numeric characters (spaces, +, -, (), etc.)
     * - If number starts with 0, change to 62
     * - If number starts with 8, prefix with 62
     * - If number already starts with 62, do not duplicate
     * - Return null if empty or invalid
     */
    public function normalizePhoneNumber(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        if ($cleaned === '' || $cleaned === null) {
            return null;
        }

        if (str_starts_with($cleaned, '0')) {
            $cleaned = '62' . substr($cleaned, 1);
        } elseif (str_starts_with($cleaned, '8')) {
            $cleaned = '62' . $cleaned;
        }

        // Must start with 62 and have a sensible length (at least 8 digits)
        if (! str_starts_with($cleaned, '62') || strlen($cleaned) < 8) {
            return null;
        }

        return $cleaned;
    }

    /**
     * Build the official WhatsApp credential message for government subscription
     */
    public function buildCredentialMessage(GovernmentSubscription $subscription, string $rawToken): string
    {
        $picName = trim((string) ($subscription->pic_name ?: 'Bapak/Ibu'));
        $agencyName = trim((string) ($subscription->agency_name ?: 'Instansi'));
        $planName = (string) ($subscription->plan_name ?: config('b2g.plan_name', 'Government Data Access'));
        $planDays = (int) ($subscription->plan_days ?: config('b2g.plan_days', 30));

        $expiresAt = $subscription->expires_at
            ? $subscription->expires_at->isoFormat('D MMMM Y')
            : '-';

        $baseApiUrl = config('b2g.base_api_url') ?: url('/api/v1/government');
        if (str_starts_with($baseApiUrl, 'http://localhost')) {
            $baseApiUrl = str_replace('http://localhost', 'http://127.0.0.1:8000', $baseApiUrl);
        }
        $baseApiUrl = rtrim((string) $baseApiUrl, '/');

        $documentationUrl = config('b2g.documentation_url');
        if (! $documentationUrl) {
            $documentationUrl = Route::has('government.docs')
                ? route('government.docs')
                : url('/government/docs');
        }
        if (str_starts_with($documentationUrl, 'http://localhost')) {
            $documentationUrl = str_replace('http://localhost', 'http://127.0.0.1:8000', $documentationUrl);
        }

        $portalUrl = Route::has('government.portal.index')
            ? route('government.portal.index')
            : url('/government/portal');
        if (str_starts_with($portalUrl, 'http://localhost')) {
            $portalUrl = str_replace('http://localhost', 'http://127.0.0.1:8000', $portalUrl);
        }

        return "Halo {$picName},\n\n"
            . "Pendaftaran akses data Government B2G P.A.D.I. telah disetujui.\n\n"
            . "Detail akses:\n"
            . "- Instansi: {$agencyName}\n"
            . "- Paket: {$planName}\n"
            . "- Durasi: {$planDays} Hari\n"
            . "- Status: AKTIF\n"
            . "- Berlaku sampai: {$expiresAt}\n\n"
            . "Credential:\n"
            . "- Token API: {$rawToken}\n\n"
            . "Portal Data Pemerintah:\n"
            . "{$portalUrl}\n\n"
            . "Dokumentasi API:\n"
            . "{$documentationUrl}\n\n"
            . "Base API:\n"
            . "{$baseApiUrl}\n\n"
            . "Token digunakan untuk autentikasi akses data.\n\n"
            . "Mohon simpan credential ini dengan aman.\n\n"
            . "Terima kasih.\n"
            . "P.A.D.I. Smart Farming";
    }

    /**
     * Generate WhatsApp Click-to-Chat (wa.me) URL with prefilled credential message
     */
    public function generateWhatsAppUrl(GovernmentSubscription $subscription, string $rawToken): ?string
    {
        $phone = $this->normalizePhoneNumber($subscription->pic_phone);

        if (! $phone) {
            return null;
        }

        $message = $this->buildCredentialMessage($subscription, $rawToken);

        return "https://wa.me/{$phone}?text=" . rawurlencode($message);
    }

    /**
     * PRODUCTION EXTENSION HOOK:
     *
     * In development/demo, we use WhatsApp Click-to-Chat with prefilled messages via wa.me.
     * To migrate to official WhatsApp Business API (Twilio, Meta Cloud API, Fonnte, Qiscus, etc.)
     * implement the provider call here without touching the approval controller flow:
     *
     * public function sendAutomatedWhatsApp(GovernmentSubscription $subscription, string $rawToken): bool
     * {
     *     // Call external WhatsApp provider API
     *     // IMPORTANT: Do NOT log the raw token in application logs for security reasons.
     *     return true;
     * }
     */
}
