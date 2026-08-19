import './echo';
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();


const adminMeta = document.querySelector('meta[name="admin-user-id"]');
const notificationList = document.getElementById('adminNotificationList');
const notificationBadge = document.getElementById('adminNotificationBadge');
const notificationCountText = document.querySelectorAll('[data-admin-notification-count]');

function setPanelState(toggle, panel, isOpen) {
    if (!toggle || !panel) {
        return;
    }

    panel.hidden = !isOpen;
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
}

function setupPopover(toggleId, panelId) {
    const toggle = document.getElementById(toggleId);
    const panel = document.getElementById(panelId);

    if (!toggle || !panel) {
        return;
    }

    toggle.addEventListener('click', (event) => {
        event.stopPropagation();
        setPanelState(toggle, panel, panel.hidden);
    });

    panel.addEventListener('click', (event) => {
        event.stopPropagation();
    });

    document.addEventListener('click', () => setPanelState(toggle, panel, false));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setPanelState(toggle, panel, false);
            toggle.focus();
        }
    });
}

function setupSidebar() {
    const sidebar = document.querySelector('.admin-sidebar');
    const toggle = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('sidebarOverlay');

    if (!sidebar || !toggle || !overlay) {
        return;
    }

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
        if (sidebar.classList.contains('is-open')) {
            closeSidebar();
            return;
        }

        openSidebar();
    });

    overlay.addEventListener('click', closeSidebar);

    document.querySelectorAll('.admin-sidebar__link').forEach((link) => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 1024) {
                closeSidebar();
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSidebar();
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 1024) {
            closeSidebar();
        }
    });
}

function updateNotificationCount() {
    if (!notificationBadge) {
        return;
    }

    const current = Number(notificationBadge.dataset.count ?? '0') + 1;
    notificationBadge.dataset.count = String(current);
    notificationBadge.textContent = current > 9 ? '9+' : String(current);
    notificationBadge.hidden = false;

    notificationCountText.forEach((node) => {
        node.textContent = String(current);
    });
}

function prependNotification(notification) {
    if (!notificationList) {
        return;
    }

    const emptyState = notificationList.querySelector('[data-admin-notification-empty]');
    emptyState?.closest('.admin-navbar__notification-empty')?.remove();

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
    notificationList.prepend(item);
}

setupSidebar();
setupPopover('adminNotificationToggle', 'adminNotificationPanel');
setupPopover('adminAccountToggle', 'adminAccountPanel');

if (adminMeta?.content && window.Echo) {
    window.Echo
        .private(`admin.notifications.${adminMeta.content}`)
        .listen('.admin.notification.created', (event) => {
            updateNotificationCount();
            prependNotification(event);
        });
}
