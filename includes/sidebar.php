<?php
/**
 * Admin Sidebar Component (Strictly Matching Stitch UI Navigation)
 */
$currentPage = isset($_GET['page']) ? sanitize($_GET['page']) : 'admin_overview';
?>
<aside class="sidebar no-print">
    <div class="sidebar-header">
        <a href="index.php?page=admin_overview" style="display: flex; items-center; gap: 10px;">
            <img src="assets/images/harvestly_logo.jpg" alt="Harvestly Logo" style="height: 38px; width: auto; border-radius: var(--radius-sm); object-fit: contain;">
        </a>
        <div class="sidebar-subtitle">Operations Console</div>
    </div>

    <ul class="sidebar-nav">
        <li>
            <a href="index.php?page=admin_overview" class="sidebar-link <?= ($currentPage === 'admin_overview') ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Overview
            </a>
        </li>
        <li>
            <a href="index.php?page=admin_users" class="sidebar-link <?= ($currentPage === 'admin_users') ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Users
            </a>
        </li>
        <li>
            <a href="index.php?page=admin_verifications" class="sidebar-link <?= ($currentPage === 'admin_verifications') ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                Verifications
            </a>
        </li>
        <li>
            <a href="index.php?page=admin_listings" class="sidebar-link <?= ($currentPage === 'admin_listings') ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                Listings
            </a>
        </li>
        <li>
            <a href="index.php?page=admin_orders" class="sidebar-link <?= ($currentPage === 'admin_orders') ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                Orders
            </a>
        </li>
        <li>
            <a href="index.php?page=admin_disputes" class="sidebar-link <?= ($currentPage === 'admin_disputes') ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>
                Disputes
            </a>
        </li>
        <li>
            <a href="index.php?page=admin_reports" class="sidebar-link <?= ($currentPage === 'admin_reports') ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                Reports
            </a>
        </li>
        <li>
            <a href="index.php?page=admin_regions_hubs" class="sidebar-link <?= ($currentPage === 'admin_regions_hubs') ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                Regions & Hubs
            </a>
        </li>
        <li>
            <a href="index.php?page=admin_settlements" class="sidebar-link <?= ($currentPage === 'admin_settlements') ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                Settlements
            </a>
        </li>
        <li>
            <a href="index.php?page=admin_notifications" class="sidebar-link <?= ($currentPage === 'admin_notifications') ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                Notifications
            </a>
        </li>
        <li>
            <a href="index.php?page=admin_settings" class="sidebar-link <?= ($currentPage === 'admin_settings') ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/></svg>
                Settings
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <div class="admin-profile-card">
            <div class="admin-avatar">AD</div>
            <div class="admin-info">
                <span class="admin-name"><?= isset($_SESSION['user_name']) ? sanitize($_SESSION['user_name']) : 'Admin User'; ?></span>
                <span class="admin-role">Administrator</span>
            </div>
        </div>
    </div>
</aside>
