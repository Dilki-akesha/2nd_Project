'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const controllerUrl = '/Harvestly/Controller/Buyer/NotificationsController.php';
    const notificationCards = [...document.querySelectorAll('.notification-card')];
    const filterTabs = [...document.querySelectorAll('.filter-tab')];
    const searchInput = document.getElementById('searchInput');
    const markAllReadButton = document.getElementById('markAllRead');
    const cartButton = document.getElementById('cartButton');
    const mobileMenuButton = document.getElementById('mobileMenuButton');
    const mobileMenu = document.getElementById('mobileMenu');
    const settingsButton = document.getElementById('settingsButton');

    function applyFilters() {
        const activeTab = document.querySelector('.filter-tab.active');
        const filter = activeTab?.dataset.filter || 'All';
        const search = (searchInput?.value || '').trim().toLowerCase();

        notificationCards.forEach(card => {
            const type = card.dataset.type || '';
            const text = card.textContent.toLowerCase();
            const promotion = card.classList.contains('promotion-card');

            const matchesFilter =
                filter === 'All' ||
                type === filter ||
                (filter === 'Promotions' && promotion);

            const matchesSearch = search === '' || text.includes(search);

            card.style.display = matchesFilter && matchesSearch ? 'flex' : 'none';
        });
    }

    async function sendAction(action, id = '') {
        const data = new FormData();
        data.append('action', action);

        if (id !== '') {
            data.append('id', id);
        }

        const response = await fetch(controllerUrl, {
            method: 'POST',
            body: data,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Notification action failed.');
        }

        return result;
    }

    function updateUnreadCount() {
        const unread = document.querySelectorAll('.notification-card.unread').length;
        const number = document.querySelector('.summary-card:nth-child(2) .summary-number');

        if (number) {
            number.textContent = unread;
        }
    }

    function markCardAsRead(card) {
        card.classList.remove('unread', 'high-priority');
        card.classList.add('read');
        card.querySelector('.unread-dot')?.remove();
        updateUnreadCount();
    }

    filterTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            filterTabs.forEach(item => item.classList.remove('active'));
            tab.classList.add('active');
            applyFilters();
        });
    });

    searchInput?.addEventListener('input', applyFilters);

    notificationCards.forEach(card => {
        card.addEventListener('click', async event => {
            if (event.target.closest('.notification-action')) {
                return;
            }

            const id = card.dataset.id;
            if (!id || !card.classList.contains('unread')) {
                return;
            }

            try {
                await sendAction('read', id);
                markCardAsRead(card);
            } catch (error) {
                console.error(error);
            }
        });
    });

    markAllReadButton?.addEventListener('click', async () => {
        try {
            await sendAction('read_all');

            notificationCards.forEach(markCardAsRead);
            markAllReadButton.innerHTML =
                '<span class="material-symbols-outlined">done</span> All Read';
        } catch (error) {
            alert(error.message || 'Unable to mark notifications as read.');
        }
    });

    document.querySelectorAll('.notification-action').forEach(button => {
        button.addEventListener('click', event => {
            event.stopPropagation();
            const target = button.dataset.url;

            if (target) {
                window.location.href = target;
            }
        });
    });

    cartButton?.addEventListener('click', () => {
        window.location.href = '/Harvestly/Controller/Buyer/CartController.php';
    });

    mobileMenuButton?.addEventListener('click', () => {
        mobileMenu?.classList.toggle('open');
        const icon = mobileMenuButton.querySelector('.material-symbols-outlined');

        if (icon) {
            icon.textContent = mobileMenu?.classList.contains('open') ? 'close' : 'menu';
        }
    });


    settingsButton?.addEventListener('click', () => {
        window.location.href = '/Harvestly/Controller/Buyer/ProfileController.php';
    });

    document.querySelectorAll('.preference-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            checkbox.closest('.preference-row')
                ?.classList.toggle('disabled-preference', !checkbox.checked);
        });
    });

    applyFilters();
});
