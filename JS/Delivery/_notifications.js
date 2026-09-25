(function() {
    'use strict';

    // ⚠️ FIX: Delivery/ folder එකතු කරා
    const CONTROLLER_URL = '../../Controller/Delivery/NotificationController.php';

    const body = document.getElementById('notifBody');
    if (!body) return;

    // ---------- MARK INDIVIDUAL AS READ ----------
    body.querySelectorAll('.mark-read-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const notifId = row.dataset.notifId;
            const self = this;

            const formData = new FormData();
            formData.append('action', 'markRead');
            formData.append('notification_id', notifId);

            fetch(CONTROLLER_URL, { method: 'POST', body: formData })
                .then(r => r.text())
                .then(text => {
                    console.log('Mark Read response:', text);

                    let data;
                    try { data = JSON.parse(text); }
                    catch (e) {
                        alert('Server error. Check console.');
                        return;
                    }

                    if (!data.success) {
                        alert('Failed to mark as read.');
                        return;
                    }

                    // Update UI
                    row.classList.remove('unread-row');
                    const b = row.querySelector('.notif-status');
                    if (b) {
                        b.textContent = 'Read';
                        b.className = 'status-badge completed notif-status';
                    }
                    self.disabled = true;
                    self.textContent = 'Read';

                    // Update count
                    updateUnreadDisplay();
                })
                .catch(err => {
                    alert('Network error');
                    console.error(err);
                });
        });
    });

    // ---------- MARK ALL AS READ ----------
    document.getElementById('markAllReadBtn')?.addEventListener('click', function() {
        const formData = new FormData();
        formData.append('action', 'markAllRead');

        fetch(CONTROLLER_URL, { method: 'POST', body: formData })
            .then(r => r.text())
            .then(text => {
                console.log('Mark All response:', text);

                let data;
                try { data = JSON.parse(text); }
                catch (e) {
                    alert('Server error. Check console.');
                    return;
                }

                if (!data.success) return;

                body.querySelectorAll('.unread-row').forEach(row => {
                    row.classList.remove('unread-row');
                    const b = row.querySelector('.notif-status');
                    if (b) {
                        b.textContent = 'Read';
                        b.className = 'status-badge completed notif-status';
                    }
                    const btn = row.querySelector('.mark-read-btn');
                    if (btn) { btn.disabled = true; btn.textContent = 'Read'; }
                });

                updateUnreadDisplay(0);
            })
            .catch(err => {
                alert('Network error');
                console.error(err);
            });
    });

    // ---------- UPDATE UNREAD DISPLAY ----------
    function updateUnreadDisplay(forceCount) {
        const unreadEl = document.getElementById('unreadCount');
        if (unreadEl) {
            const count = forceCount !== undefined
                ? forceCount
                : body.querySelectorAll('.unread-row').length;
            unreadEl.textContent = count;

            // Sidebar badge
            const sidebarBadge = document.querySelector('.sidebar-nav a[href*="notifications"] .badge');
            if (sidebarBadge) {
                sidebarBadge.textContent = count;
                sidebarBadge.style.display = count > 0 ? 'inline-block' : 'none';
            }

            // Topbar bell badge
            const topBadge = document.querySelector('.notif-btn .badge');
            if (topBadge) {
                topBadge.textContent = count;
                topBadge.style.display = count > 0 ? 'inline-block' : 'none';
            }
        }
    }

    console.log('Notifications module loaded');
})();