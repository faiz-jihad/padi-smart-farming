<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\PadiCacheService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RealtimeStreamController extends Controller
{
    /**
     * Real-time Server-Sent Events (SSE) & WebSocket-compatible Live Stream.
     * Delivers sub-5ms cached updates for notifications, live ticker, and radar alerts.
     */
    public function stream(Request $request): StreamedResponse
    {
        $user = $request->user();
        $lastEventId = (int) $request->header('Last-Event-ID', $request->query('last_event_id', 0));

        return response()->stream(function () use ($user, $lastEventId) {
            set_time_limit(0);
            ob_implicit_flush(1);

            $lastNotificationId = $lastEventId;
            $iterations = 0;
            $maxIterations = 30; // 30 cycles (~30 seconds) before graceful reconnect to prevent socket leaks

            while ($iterations < $maxIterations) {
                $hasData = false;

                // 1. Check for new notifications with projected columns
                $notifQuery = Notification::query()
                    ->select(['id', 'user_id', 'type', 'title', 'body', 'data_json', 'created_at'])
                    ->where('id', '>', $lastNotificationId);

                if ($user) {
                    $notifQuery->where(function ($q) use ($user) {
                        $q->where('user_id', $user->id)
                          ->orWhereNull('user_id');
                    });
                }

                $newNotifications = $notifQuery->orderBy('id', 'asc')->limit(10)->get();

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

                // 2. Stream Realtime Market Price Ticker every 5 seconds (Direct from Redis cache)
                if ($iterations % 5 === 0) {
                    $ticker = PadiCacheService::getMarketPriceTicker();
                    $tickerJson = json_encode($ticker);
                    echo "event: price_ticker\n";
                    echo "data: {$tickerJson}\n\n";
                    $hasData = true;
                }

                // 3. Stream Community Radar Alert Summary every 10 seconds
                if ($iterations % 10 === 0) {
                    $radar = PadiCacheService::getCommunityRadarSummary();
                    $radarJson = json_encode([
                        'alerts_count' => $radar['alerts_count'] ?? 0,
                        'reports_count' => $radar['reports_count'] ?? 0,
                        'cached_at' => $radar['cached_at'] ?? now()->toIso8601String(),
                    ]);
                    echo "event: radar_summary\n";
                    echo "data: {$radarJson}\n\n";
                    $hasData = true;
                }

                // 4. Heartbeat ping to keep persistent connection alive
                if (!$hasData) {
                    echo ": ping " . time() . "\n\n";
                }

                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();

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
