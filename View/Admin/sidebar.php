<?php
/**
 * Admin Sidebar Component (Strictly Matching Harvestly Admin Requirements)
 */
$currentPage = isset($_GET['page']) ? sanitize($_GET['page']) : 'admin_overview';
?>
<aside class="sidebar no-print" id="adminSidebar">
    <div class="sidebar-header">
        <a href="index.php?page=admin_overview" style="display: flex; align-items: center; gap: 10px;">
            <img src="assets/images/harvestly_logo.jpg" alt="Harvestly Logo" style="height: 38px; width: auto; border-radius: var(--radius-sm); object-fit: contain;">
        </a>
        <div class="sidebar-subtitle">Operations Console</div>
    </div>

    <ul class="sidebar-nav" style="overflow-y: auto; max-height: calc(100vh - 150px); padding-bottom: 20px;">
        <!-- 1. Dashboard -->
        <li>
            <a href="index.php?page=admin_overview" class="sidebar-link <?= ($currentPage === 'admin_overview') ? 'active' : ''; ?>">
                Dashboard
            </a>
        </li>

        <!-- 2. Users -->
        <li>
            <a href="index.php?page=admin_users" class="sidebar-link <?= ($currentPage === 'admin_users') ? 'active' : ''; ?>">
                Users
            </a>
        </li>

        <!-- 3. Farmer Approvals -->
        <li>
            <a href="index.php?page=admin_farmer_approvals" class="sidebar-link <?= ($currentPage === 'admin_farmer_approvals') ? 'active' : ''; ?>">
                Farmer Approvals
            </a>
        </li>

        <!-- 4. Courier Approvals -->
        <li>
            <a href="index.php?page=admin_courier_approvals" class="sidebar-link <?= ($currentPage === 'admin_courier_approvals') ? 'active' : ''; ?>">
                Courier Approvals
            </a>
        </li>

        <!-- 5. Products -->
        <li>
            <a href="index.php?page=admin_listings" class="sidebar-link <?= ($currentPage === 'admin_listings') ? 'active' : ''; ?>">
                Products
            </a>
        </li>

        <!-- 6. Product Categories -->
        <li>
            <a href="index.php?page=admin_categories" class="sidebar-link <?= ($currentPage === 'admin_categories') ? 'active' : ''; ?>" style="font-weight: 700; color: #cbffc2;">
                Product Categories
            </a>
        </li>

        <!-- 7. Orders -->
        <li>
            <a href="index.php?page=admin_orders" class="sidebar-link <?= ($currentPage === 'admin_orders') ? 'active' : ''; ?>">
                Orders
            </a>
        </li>

        <!-- 8. Deliveries -->
        <li>
            <a href="index.php?page=admin_deliveries" class="sidebar-link <?= ($currentPage === 'admin_deliveries') ? 'active' : ''; ?>">
                Deliveries
            </a>
        </li>

        <!-- 9. Pending Assignments -->
        <li>
            <a href="index.php?page=admin_pending_assignments" class="sidebar-link <?= ($currentPage === 'admin_pending_assignments') ? 'active' : ''; ?>">
                Pending Assignments
            </a>
        </li>

        <!-- 10. District Distances -->
        <li>
            <a href="index.php?page=admin_district_distances" class="sidebar-link <?= ($currentPage === 'admin_district_distances') ? 'active' : ''; ?>">
                District Distances
            </a>
        </li>

        <!-- 11. Complaints / Issues -->
        <li>
            <a href="index.php?page=admin_complaints" class="sidebar-link <?= ($currentPage === 'admin_complaints') ? 'active' : ''; ?>">
                Complaints / Issues
            </a>
        </li>

        <!-- 12. Payments -->
        <li>
            <a href="index.php?page=admin_payments" class="sidebar-link <?= ($currentPage === 'admin_payments') ? 'active' : ''; ?>">
                Payments
            </a>
        </li>

        <!-- 13. Earnings / Settlements -->
        <li>
            <a href="index.php?page=admin_settlements" class="sidebar-link <?= ($currentPage === 'admin_settlements') ? 'active' : ''; ?>">
                Earnings / Settlements
            </a>
        </li>

        <!-- 14. Platform Settings -->
        <li>
            <a href="index.php?page=admin_settings" class="sidebar-link <?= ($currentPage === 'admin_settings') ? 'active' : ''; ?>">
                Platform Settings
            </a>
        </li>

        <!-- 15. Notifications -->
        <li>
            <a href="index.php?page=admin_notifications" class="sidebar-link <?= ($currentPage === 'admin_notifications') ? 'active' : ''; ?>">
                Notifications
            </a>
        </li>

        <!-- 16. Reports -->
        <li>
            <a href="index.php?page=admin_reports" class="sidebar-link <?= ($currentPage === 'admin_reports') ? 'active' : ''; ?>">
                Reports
            </a>
        </li>

        <!-- 17. Profile -->
        <li>
            <a href="index.php?page=admin_profile" class="sidebar-link <?= ($currentPage === 'admin_profile') ? 'active' : ''; ?>">
                Admin Profile
            </a>
        </li>

        <!-- 18. Logout -->
        <li>
            <form action="index.php?action=logout" method="POST" onsubmit="return confirm('Log out of Admin Operations Console?');"><?= csrfField() ?>
                <button type="submit" class="sidebar-link text-error" style="width:100%; border:0; background:transparent; text-align:left; cursor:pointer;">
                Logout
                </button>
            </form>
        </li>
    </ul>

    <div class="sidebar-footer">
        <a href="index.php?page=admin_profile" class="admin-profile-card" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;">
            <div class="admin-avatar">AD</div>
            <div class="admin-info">
                <span class="admin-name"><?= isset($_SESSION['user_name']) ? sanitize($_SESSION['user_name']) : 'Admin User'; ?></span>
                <span class="admin-role">Administrator</span>
            </div>
        </a>
    </div>
</aside>
