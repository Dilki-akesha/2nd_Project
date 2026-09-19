<?php
/**
 * Shared Footer Component
 */
$currentPage = isset($_GET['page']) ? sanitize($_GET['page']) : 'landing';
$isAdminPage = strpos($currentPage, 'admin_') === 0;
?>

<?php if (!$isAdminPage): ?>
    <!-- Public Footer -->
    <footer class="public-footer no-print" style="background-color: var(--color-surface-container-high); padding: 64px 24px 32px; border-top: 1px solid var(--color-outline-variant); margin-top: auto;">
        <div style="max-width: 1280px; margin: 0 auto; display: flex; flex-direction: column; gap: 48px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 32px;">
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div class="logo-brand">
                        <img src="assets/images/harvestly_logo.jpg" alt="Harvestly Logo" style="height: 40px; width: auto; border-radius: var(--radius-md); object-fit: contain;">
                        <span class="logo-title">Harvestly</span>
                    </div>
                    <p style="font-size: 14px; color: var(--color-on-surface-variant); line-height: 1.6;">
                        Empowering Sri Lankan agriculture through transparent, direct farm-to-doorstep trade with zero broker markups.
                    </p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <strong style="font-size: 15px; color: var(--color-on-surface);">Browse Produce</strong>
                    <a href="#" style="font-size: 14px; color: var(--color-on-surface-variant);">Nuwara Eliya Vegetables</a>
                    <a href="#" style="font-size: 14px; color: var(--color-on-surface-variant);">Tropical Fruits & Citrus</a>
                    <a href="#" style="font-size: 14px; color: var(--color-on-surface-variant);">Organic Greens & Gotu Kola</a>
                    <a href="#" style="font-size: 14px; color: var(--color-on-surface-variant);">Ceylon Spices & Cinnamon</a>
                </div>

                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <strong style="font-size: 15px; color: var(--color-on-surface);">Company & Trust</strong>
                    <a href="#" style="font-size: 14px; color: var(--color-on-surface-variant);">About Harvestly</a>
                    <a href="#" style="font-size: 14px; color: var(--color-on-surface-variant);">Doorstep Delivery Rates</a>
                    <a href="#" style="font-size: 14px; color: var(--color-on-surface-variant);">Terms of Service</a>
                    <a href="#" style="font-size: 14px; color: var(--color-on-surface-variant);">Privacy Policy</a>
                </div>

                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <strong style="font-size: 15px; color: var(--color-on-surface);">Customer Support</strong>
                    <p style="font-size: 14px; color: var(--color-on-surface-variant);">📞 +94 11 759 8400</p>
                    <p style="font-size: 14px; color: var(--color-on-surface-variant);">✉️ support@harvestly.lk</p>
                    <p style="font-size: 14px; color: var(--color-on-surface-variant);">📍 Colombo 03, Sri Lanka</p>
                </div>
            </div>

            <div style="padding-top: 24px; border-top: 1px solid var(--color-outline-variant); display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; font-size: 13px; color: var(--color-outline);">
                <p>© 2026 Harvestly Technologies Ltd. All rights reserved. Fresh direct farm-to-doorstep marketplace.</p>
                <span>SL Registered Business (BRN PV-002941)</span>
            </div>
        </div>
    </footer>
<?php endif; ?>

<script src="js/main.js"></script>
</body>
</html>
