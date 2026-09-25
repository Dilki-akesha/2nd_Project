(function() {
    'use strict';

    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '../../Controller/AuthController.php?action=logout';
            }
        });
    }

    console.log('Auth module loaded');
})();