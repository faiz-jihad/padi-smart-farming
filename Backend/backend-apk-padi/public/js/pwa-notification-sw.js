/**
 * P.A.D.I. Smart Farming - Real-Time Web Push & Native Notification Engine
 * Handles service worker lifecycle, native OS notifications, in-app live toasts, and automatic badge sync.
 */

(function () {
    'use strict';

    const SW_PATH = '/sw.js';
    const API_LATEST_NOTIFS = '/admin/notifications/latest';
    const API_DEVICE_TOKENS = '/api/v1/device-tokens';

    let lastKnownUnreadCount = 0;
    let knownNotifIds = new Set();
    let isInitialFetch = true;

    window.PadiPush = {
        swRegistration: null,
        isSupported: ('Notification' in window),

        // Initialize notification engine & background sync
        async init() {
            // Register Service Worker if available
            if ('serviceWorker' in navigator) {
                try {
                    this.swRegistration = await navigator.serviceWorker.register(SW_PATH, { scope: '/' });
                    console.log('[PADI Push] Service Worker registered with scope:', this.swRegistration.scope);
                } catch (err) {
                    console.warn('[PADI Push] Service Worker registration skipped:', err);
                }
            }

            // Check and inject soft permission prompt if needed
            this.checkPermissionPrompt();

            // Start live notification polling
            this.startLivePolling();
        },

        // Request native notification permission
        async requestPermission() {
            if (!this.isSupported) {
                this.showInAppToast('Browser ini belum mendukung fitur Notifikasi Web.');
                return false;
            }

            try {
                const permission = await Notification.requestPermission();
                const promptEl = document.getElementById('padiNotifPromptBanner');
                if (promptEl) promptEl.remove();

                if (permission === 'granted') {
                    console.log('[PADI Push] Notification permission granted.');
                    this.syncDeviceToken();
                    this.showLocalNotification({
                        title: '🌾 Notifikasi Web P.A.D.I. Aktif',
                        body: 'Perangkat ini sekarang terhubung untuk menerima peringatan dini cuaca, hama, dan telemetri sawah secara real-time.',
                        url: window.location.href
                    });
                    this.showInAppToast('Notifikasi Web Berhasil Diaktifkan!');
                    return true;
                } else {
                    console.warn('[PADI Push] Permission denied or dismissed.');
                    return false;
                }
            } catch (err) {
                console.error('[PADI Push] Error requesting permission:', err);
                return false;
            }
        },

        // Soft banner asking user to enable notifications if in default state
        checkPermissionPrompt() {
            if (!this.isSupported) return;
            if (Notification.permission !== 'default') return;
            if (sessionStorage.getItem('padi_notif_dismissed') === 'true') return;

            const existing = document.getElementById('padiNotifPromptBanner');
            if (existing) return;

            const banner = document.createElement('div');
            banner.id = 'padiNotifPromptBanner';
            banner.className = 'padi-notif-prompt-banner';
            banner.innerHTML = `
                <div class="padi-notif-prompt-content">
                    <span class="padi-notif-prompt-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    </span>
                    <div>
                        <strong>Aktifkan Notifikasi Web P.A.D.I.</strong>
                        <p>Dapatkan peringatan dini cuaca ekstrem, serangan hama, dan telemetri sawah langsung di layar perangkat Anda.</p>
                    </div>
                </div>
                <div class="padi-notif-prompt-btns">
                    <button type="button" class="padi-notif-btn-allow" onclick="window.PadiPush.requestPermission()">Izinkan Notifikasi</button>
                    <button type="button" class="padi-notif-btn-dismiss" onclick="window.PadiPush.dismissPrompt()">Nanti Saja</button>
                </div>
            `;

            document.body.appendChild(banner);
        },

        dismissPrompt() {
            sessionStorage.setItem('padi_notif_dismissed', 'true');
            const promptEl = document.getElementById('padiNotifPromptBanner');
            if (promptEl) promptEl.remove();
        },

        // Display Native OS Popup + In-App Toast
        showLocalNotification(payload) {
            // 1. Play subtle chime sound
            this.playNotificationChime();

            // 2. Display In-App Floating Toast
            this.showInAppToast(payload.title, payload.body, payload.url);

            // 3. Display Native OS Notification if permitted
            if (this.isSupported && Notification.permission === 'granted') {
                try {
                    if (this.swRegistration && 'showNotification' in this.swRegistration) {
                        this.swRegistration.showNotification(payload.title || 'P.A.D.I. Notifikasi', {
                            body: payload.body || '',
                            icon: payload.icon || '/images/padi-logo.png',
                            badge: '/images/padi-logo.png',
                            tag: 'padi-notif-' + (payload.id || Date.now()),
                            data: { url: payload.url || '/admin/notifications' }
                        });
                    } else {
                        const nativeNotif = new Notification(payload.title || 'P.A.D.I. Notifikasi', {
                            body: payload.body || '',
                            icon: payload.icon || '/images/padi-logo.png',
                        });
                        nativeNotif.onclick = function () {
                            window.focus();
                            if (payload.url) window.location.href = payload.url;
                            nativeNotif.close();
                        };
                    }
                } catch (e) {
                    console.warn('[PADI Push] Native notification fallback:', e);
                }
            }
        },

        // In-App Toast banner with slide animation
        showInAppToast(title, body = '', url = '/admin/notifications') {
            let container = document.getElementById('padiToastContainer');
            if (!container) {
                container = document.createElement('div');
                container.id = 'padiToastContainer';
                container.className = 'padi-toast-container';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            toast.className = 'padi-live-toast';
            toast.innerHTML = `
                <div class="padi-live-toast__icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                </div>
                <div class="padi-live-toast__body">
                    <strong>${this.escapeHtml(title)}</strong>
                    ${body ? `<p>${this.escapeHtml(body)}</p>` : ''}
                </div>
                <button type="button" class="padi-live-toast__close" aria-label="Tutup">&times;</button>
            `;

            toast.addEventListener('click', (e) => {
                if (e.target.classList.contains('padi-live-toast__close')) {
                    toast.remove();
                    return;
                }
                window.location.href = url;
            });

            container.appendChild(toast);

            // Auto dismiss after 6 seconds
            setTimeout(() => {
                toast.classList.add('is-hiding');
                setTimeout(() => toast.remove(), 300);
            }, 6000);
        },

        // Pleasant subtle Web Audio sound cue
        playNotificationChime() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                osc.type = 'sine';
                osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5 note
                osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.15); // A5 note

                gain.gain.setValueAtTime(0.12, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start();
                osc.stop(ctx.currentTime + 0.35);
            } catch (_) {}
        },

        // Poll for latest notifications & update navbar badge dynamically
        startLivePolling() {
            const check = async () => {
                try {
                    const res = await fetch(API_LATEST_NOTIFS, {
                        headers: { 'Accept': 'application/json' }
                    });

                    if (!res.ok) return;

                    const data = await res.json();
                    if (data && data.status === 'success') {
                        const unread = data.unread_count || 0;
                        const items = data.items || [];

                        // Update navbar badge
                        this.updateNavbarBadge(unread);

                        // If not initial fetch and there are new unread items
                        if (!isInitialFetch && items.length > 0) {
                            for (const item of items) {
                                if (!item.is_read && !knownNotifIds.has(item.id)) {
                                    knownNotifIds.add(item.id);
                                    this.showLocalNotification({
                                        id: item.id,
                                        title: item.title,
                                        body: item.body,
                                        url: '/admin/notifications'
                                    });
                                }
                            }
                        }

                        // Save known IDs
                        items.forEach(i => knownNotifIds.add(i.id));
                        lastKnownUnreadCount = unread;
                        isInitialFetch = false;
                    }
                } catch (err) {
                    // Silently ignore network failures
                }
            };

            check();
            setInterval(check, 15000); // Check every 15s
        },

        // Update badge count in navbar without page refresh
        updateNavbarBadge(count) {
            const badge = document.getElementById('adminNotificationBadge');
            const countText = document.querySelector('[data-admin-notification-count]');

            if (countText) countText.textContent = count;

            if (badge) {
                if (count > 0) {
                    badge.hidden = false;
                    badge.textContent = count > 9 ? '9+' : count;
                } else {
                    badge.hidden = true;
                }
            }
        },

        // Sync client push token
        async syncDeviceToken() {
            try {
                let token = localStorage.getItem('padi_device_token');
                if (!token) {
                    token = 'web-client-' + Math.random().toString(36).substring(2) + '-' + Date.now();
                    localStorage.setItem('padi_device_token', token);
                }

                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                await fetch(API_DEVICE_TOKENS, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {})
                    },
                    body: JSON.stringify({ token: token, platform: 'web' })
                });
            } catch (_) {}
        },

        escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>"']/g, function (m) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
            });
        }
    };

    // Auto initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => window.PadiPush.init());
    } else {
        window.PadiPush.init();
    }
})();
