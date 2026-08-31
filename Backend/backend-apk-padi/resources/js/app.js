import './echo';
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// ─── Helper: set visibilitas panel popover ────────────────────────────────────
function setPanelState(toggle, panel, isOpen) {
    if (!toggle || !panel) return;
    panel.hidden = !isOpen;
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
}

// ─── Setup satu pasang toggle + panel sebagai popover ────────────────────────
function setupPopover(toggleId, panelId) {
    const toggle = document.getElementById(toggleId);
    const panel  = document.getElementById(panelId);

    if (!toggle || !panel) return;
    if (toggle.dataset.popoverBound === 'true') return;

    toggle.dataset.popoverBound = 'true';

    toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        setPanelState(toggle, panel, panel.hidden);
    });

    panel.addEventListener('click', (e) => {
        e.stopPropagation();
    });

    document.addEventListener('click', () => {
        setPanelState(toggle, panel, false);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            setPanelState(toggle, panel, false);
            toggle.focus();
        }
    });
}

// ─── Setup sidebar mobile ─────────────────────────────────────────────────────
function setupSidebar() {
    const sidebar = document.querySelector('.admin-sidebar');
    const toggle  = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('sidebarOverlay');

    if (!sidebar || !toggle || !overlay) return;

    const closeSidebar = () => {
        sidebar.classList.remove('is-open');
        overlay.classList.remove('is-visible');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('sidebar-open');
    };

    const openSidebar = () => {
        sidebar.classList.add('is-open');
        overlay.classList.add('is-visible');
        toggle.setAttribute('aria-expanded', 'true');
        document.body.classList.add('sidebar-open');
        sidebar.querySelector('a, button')?.focus();
    };

    toggle.addEventListener('click', () => {
        sidebar.classList.contains('is-open') ? closeSidebar() : openSidebar();
    });

    overlay.addEventListener('click', closeSidebar);

    document.querySelectorAll('.admin-sidebar__link').forEach((link) => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 1024) closeSidebar();
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeSidebar();
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 1024) closeSidebar();
    });
}

// ─── Update badge notifikasi ──────────────────────────────────────────────────
function updateNotificationCount(badge, countNodes, dashboardBadge) {
    const current = Number(badge?.dataset.count ?? '0') + 1;

    if (badge) {
        badge.dataset.count = String(current);
        badge.textContent = current > 9 ? '9+' : String(current);
        badge.hidden = false;
    }

    countNodes.forEach((node) => {
        node.textContent = String(current);
    });

    if (dashboardBadge) {
        dashboardBadge.textContent =
            `${current.toLocaleString('id-ID')} belum dibaca`;
    }
}

// ─── Prepend item notifikasi baru ke list ────────────────────────────────────
function prependNotification(notification, list) {
    if (!list) return;

    list.querySelector('[data-admin-notification-empty]')
        ?.closest('.admin-navbar__notification-empty')
        ?.remove();

    const item = document.createElement('div');
    item.className = 'admin-navbar__notification-item is-unread';

    const icon = document.createElement('div');
    icon.className = 'admin-navbar__notification-icon';
    icon.setAttribute('aria-hidden', 'true');
    icon.textContent = String(notification.type ?? 'N').charAt(0).toUpperCase();

    const content = document.createElement('div');
    content.className = 'admin-navbar__notification-content';

    const title = document.createElement('p');
    title.textContent = notification.title ?? 'Notifikasi baru';

    const body = document.createElement('span');
    body.textContent = notification.body ?? '';

    const time = document.createElement('small');
    time.textContent = notification.created_at ?? 'Baru saja';

    content.append(title, body, time);
    item.append(icon, content);
    list.prepend(item);
}

// ─── Inisialisasi setelah DOM siap ───────────────────────────────────────────
// PENTING: app.js dimuat di <head> sehingga DOM belum tersedia saat modul
// dievaluasi. Semua getElementById / querySelector HARUS ada di DOMContentLoaded.
document.addEventListener('DOMContentLoaded', () => {
    console.log('[PADI Admin] DOM ready — initialising popovers & sidebar');

    const adminMeta             = document.querySelector('meta[name="admin-user-id"]');
    const notificationList      = document.getElementById('adminNotificationList');
    const notificationBadge     = document.getElementById('adminNotificationBadge');
    const dashboardNotificationBadge = document.getElementById('adminNotifBadge');
    const notificationCountText  = document.querySelectorAll('[data-admin-notification-count]');

    setupSidebar();
    setupPopover('adminNotificationToggle', 'adminNotificationPanel');
    setupPopover('adminAccountToggle',      'adminAccountPanel');

    if (adminMeta?.content && window.Echo) {
        window.Echo
            .private(`admin.notifications.${adminMeta.content}`)
            .listen('.admin.notification.created', (event) => {
                updateNotificationCount(
                    notificationBadge,
                    notificationCountText,
                    dashboardNotificationBadge
                );
                prependNotification(event, notificationList);
            });
    }
});
