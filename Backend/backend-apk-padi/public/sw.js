/**
 * P.A.D.I. Smart Farming - Unified Push Notification Service Worker
 * Handles native system and device push notifications via Web Push API.
 */

const CACHE_NAME = 'padi-sw-v1';
const DEFAULT_ICON = '/images/padi-logo.png';
const DEFAULT_BADGE = '/images/padi-logo.png';

// ─── Lifecycle Events ────────────────────────────────────────────────────────

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        Promise.all([
            self.clients.claim(),
            // Clean old caches if necessary
            caches.keys().then((keys) => {
                return Promise.all(
                    keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
                );
            })
        ])
    );
});

// ─── Push Event (Receive Notification from API / Server) ─────────────────────

self.addEventListener('push', (event) => {
    let payload = {
        title: 'P.A.D.I. Notifikasi',
        body: 'Anda memiliki pesan / pembaruan sistem pertanian baru.',
        icon: DEFAULT_ICON,
        badge: DEFAULT_BADGE,
        url: '/',
        tag: 'padi-push-' + Date.now(),
        data: {}
    };

    if (event.data) {
        try {
            const data = event.data.json();
            payload.title = data.title || payload.title;
            payload.body = data.body || payload.body;
            payload.icon = data.icon || payload.icon;
            payload.badge = data.badge || payload.badge;
            payload.image = data.image || null;
            payload.url = data.url || (data.data && data.data.url) || payload.url;
            payload.tag = data.tag || (data.data && data.data.type) || payload.tag;
            payload.data = data.data || {};
            payload.data.url = payload.url;
        } catch (e) {
            // Text payload fallback
            payload.body = event.data.text();
        }
    }

    const notificationOptions = {
        body: payload.body,
        icon: payload.icon || DEFAULT_ICON,
        badge: payload.badge || DEFAULT_BADGE,
        image: payload.image || undefined,
        tag: payload.tag,
        renotify: true,
        vibrate: [200, 100, 200],
        requireInteraction: false,
        data: Object.assign({}, payload.data, {
            url: payload.url,
            time: Date.now()
        }),
        actions: [
            {
                action: 'open_url',
                title: 'Buka'
            },
            {
                action: 'dismiss',
                title: 'Tutup'
            }
        ]
    };

    event.waitUntil(
        self.registration.showNotification(payload.title, notificationOptions)
    );
});

// ─── Notification Click Handler ──────────────────────────────────────────────

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    if (event.action === 'dismiss') {
        return;
    }

    const targetUrl = (event.notification.data && event.notification.data.url) ? event.notification.data.url : '/';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            // Check if window is already open with the target URL
            for (let i = 0; i < windowClients.length; i++) {
                const client = windowClients[i];
                if (client.url === targetUrl && 'focus' in client) {
                    return client.focus();
                }
            }
            // If not found, open a new window/tab
            if (self.clients.openWindow) {
                return self.clients.openWindow(targetUrl);
            }
        })
    );
});

// ─── Push Subscription Change (Renew Token) ───────────────────────────────────

self.addEventListener('pushsubscriptionchange', (event) => {
    event.waitUntil(
        self.registration.pushManager.subscribe(event.oldSubscription.options).then((subscription) => {
            return fetch('/api/v1/device-tokens', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    token: JSON.stringify(subscription),
                    platform: 'web'
                })
            });
        })
    );
});

// ─── Direct In-App Message Handler ───────────────────────────────────────────

self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SHOW_LOCAL_NOTIFICATION') {
        const d = event.data.payload || {};
        self.registration.showNotification(d.title || 'P.A.D.I. Notifikasi', {
            body: d.body || '',
            icon: d.icon || DEFAULT_ICON,
            badge: DEFAULT_BADGE,
            data: { url: d.url || '/' },
            vibrate: [150, 80, 150]
        });
    }
});
