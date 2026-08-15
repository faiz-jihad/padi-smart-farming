import './echo';

const adminMeta = document.querySelector('meta[name="admin-user-id"]');
const notificationList = document.getElementById('adminNotificationList');
const notificationBadge = document.getElementById('adminNotificationBadge');
const notificationCountText = document.querySelectorAll('[data-admin-notification-count]');

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
    emptyState?.remove();

    const item = document.createElement('div');
    item.className = 'admin-navbar__notification-item is-unread';

    const icon = document.createElement('div');
    icon.className = 'admin-navbar__notification-icon';
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

if (adminMeta?.content && window.Echo) {
    window.Echo
        .private(`admin.notifications.${adminMeta.content}`)
        .listen('.admin.notification.created', (event) => {
            updateNotificationCount();
            prependNotification(event);
        });
}
