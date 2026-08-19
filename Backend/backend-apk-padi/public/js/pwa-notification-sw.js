/**
 * P.A.D.I. Smart Farming - Client-Side Service Worker & Web Push Registration
 * Handles service worker lifecycle, notification permissions, and device token sync with API.
 */

(function () {
    'use strict';

    const SW_PATH = '/sw.js';
    const API_DEVICE_TOKENS = '/api/v1/device-tokens';
    const API_TEST_PUSH = '/api/v1/notifications/send-push';

    window.PadiPush = {
        swRegistration: null,
        isSupported: ('serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window),

        // Initialize and register service worker
        async init() {
            if (!this.isSupported) {
                console.warn('[PADI Push] Push Notifications are not supported in this browser environment.');
                return;
            }

            try {
                this.swRegistration = await navigator.serviceWorker.register(SW_PATH, { scope: '/' });
                console.log('[PADI Push] Service Worker successfully registered with scope:', this.swRegistration.scope);

                // Auto sync device token if permission is already granted
                if (Notification.permission === 'granted') {
                    await this.syncDeviceToken();
                }
            } catch (error) {
                console.error('[PADI Push] Service Worker registration failed:', error);
            }
        },

        // Request notification permission from user
        async requestPermission() {
            if (!this.isSupported) {
                alert('Browser Anda belum mendukung fitur notifikasi push.');
                return false;
            }

            try {
                const permission = await Notification.requestPermission();
                if (permission === 'granted') {
                    console.log('[PADI Push] Notification permission granted.');
                    await this.syncDeviceToken();
                    this.showLocalNotification({
                        title: '🌾 Notifikasi P.A.D.I. Aktif',
                        body: 'Perangkat ini sekarang terhubung untuk menerima notifikasi sistem & pembaruan pertanian.',
                        url: window.location.href
                    });
                    return true;
                } else {
                    console.warn('[PADI Push] Notification permission denied or dismissed.');
                    return false;
                }
            } catch (err) {
                console.error('[PADI Push] Error requesting notification permission:', err);
                return false;
            }
        },

        // Synchronize device push subscription / token with backend API
        async syncDeviceToken() {
            if (!this.swRegistration) return;

            try {
                let subscription = await this.swRegistration.pushManager.getSubscription();

                // If no push subscription yet, create a dummy or real push token
                let tokenString = '';
                if (subscription) {
                    tokenString = JSON.stringify(subscription);
                } else {
                    // Generate a persistent unique client device identifier for API
                    tokenString = localStorage.getItem('padi_device_token');
                    if (!tokenString) {
                        tokenString = 'web-client-' + Math.random().toString(36).substring(2) + '-' + Date.now();
                        localStorage.setItem('padi_device_token', tokenString);
                    }
                }

                // Send to backend API
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const headers = {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                };
                if (csrfToken) {
                    headers['X-CSRF-TOKEN'] = csrfToken;
                }

                const response = await fetch(API_DEVICE_TOKENS, {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({
                        token: tokenString,
                        platform: 'web'
                    })
                });

                if (response.ok) {
                    console.log('[PADI Push] Device token successfully registered to P.A.D.I. API.');
                }
            } catch (err) {
                console.error('[PADI Push] Failed to sync device token with API:', err);
            }
        },

        // Display local native notification via Service Worker
        showLocalNotification(payload) {
            if (!this.isSupported || Notification.permission !== 'granted') return;

            if (navigator.serviceWorker.controller) {
                navigator.serviceWorker.controller.postMessage({
                    type: 'SHOW_LOCAL_NOTIFICATION',
                    payload: payload
                });
            } else if (this.swRegistration) {
                this.swRegistration.showNotification(payload.title || 'P.A.D.I. Notifikasi', {
                    body: payload.body || '',
                    icon: payload.icon || '/images/padi-logo.png',
                    badge: '/images/padi-logo.png',
                    data: { url: payload.url || window.location.href },
                    vibrate: [200, 100, 200]
                });
            }
        },

        // Trigger a test push notification via API
        async sendTestPushViaApi(title, body, url) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const headers = {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                };
                if (csrfToken) {
                    headers['X-CSRF-TOKEN'] = csrfToken;
                }

                const response = await fetch(API_TEST_PUSH, {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({
                        title: title || 'Peringatan Sistem P.A.D.I.',
                        body: body || 'Uji coba notifikasi push ke perangkat berhasil.',
                        url: url || window.location.href
                    })
                });

                const result = await response.json();
                console.log('[PADI Push] API Push result:', result);
                return result;
            } catch (err) {
                console.error('[PADI Push] Failed to dispatch test push via API:', err);
            }
        }
    };

    // Auto initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => window.PadiPush.init());
    } else {
        window.PadiPush.init();
    }
})();
