<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\PadiCacheService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RealtimeStreamController extends Controller
{
    /**
     * Real-time Server-Sent Events (SSE) Live Stream.
     * Supports persistent real-time streaming to Flutter & Web Service Workers.
     */
    public function stream(Request $request): StreamedResponse
    {
        $user = $request->user();
        $lastEventId = (int) $request->header('Last-Event-ID', 0);

        return response()->stream(function () use ($user, $lastEventId) {
            // Set execution timeout to infinite for streaming loop
            set_time_limit(0);
            ob_implicit_flush(1);

            $lastNotificationId = $lastEventId;
            $iterations = 0;
            $maxIterations = 20; // 20 cycles (~20 seconds) before graceful reconnect to prevent socket leaks

            while ($iterations < $maxIterations) {
                $hasData = false;

                // 1. Check for new notifications for this user / role
                $notifQuery = Notification::query()
                    ->where('id', '>', $lastNotificationId);

                if ($user) {
                    $notifQuery->where(function ($q) use ($user) {
                        $q->where('user_id', $user->id)
                          ->orWhereNull('user_id');
                    });
                }

                $newNotifications = $notifQuery->orderBy('id', 'asc')->limit(5)->get();

                foreach ($newNotifications as $notif) {
                    $lastNotificationId = $notif->id;
                    $payload = json_encode([
                        'id' => $notif->id,
                        'type' => $notif->type,
                        'title' => $notif->title,
                        'body' => $notif->body,
                        'data' => $notif->data ?? [],
                        'created_at' => $notif->created_at?->toIso8601String(),
                    ]);

                    echo "id: {$notif->id}\n";
                    echo "event: notification\n";
                    echo "data: {$payload}\n\n";
                    $hasData = true;
                }

                // 2. Stream Realtime Market Price Ticker every 5 iterations
                if ($iterations % 5 === 0) {
                    $ticker = PadiCacheService::getMarketPriceTicker();
                    $tickerJson = json_encode($ticker);
                    echo "event: price_ticker\n";
                    echo "data: {$tickerJson}\n\n";
                    $hasData = true;
                }

                // 3. Heartbeat ping to keep WebSocket / SSE connection alive
                if (!$hasData) {
                    echo ": heartbeat " . time() . "\n\n";
                }

                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();

                // Check if connection is aborted
                if (connection_aborted()) {
                    break;
                }

                sleep(1);
                $iterations++;
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Cache-Control, Content-Type, Authorization, X-Requested-With',
        ]);
    }
}
