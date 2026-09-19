<div class="page-content" style="max-width: 800px; margin: 0 auto;">
    <div class="section-header">
        <div class="section-title-group">
            <h1>Platform Settings</h1>
            <p>Configure platform commission rate, zone delivery base rates, and escrow auto-release timeout</p>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div style="background: #d1e7dd; color: #0f5132; padding: 12px 16px; border-radius: var(--radius-md); font-weight: 600; margin-bottom: 20px;">
            ✓ <?= sanitize($_GET['success']); ?>
        </div>
    <?php endif; ?>

    <div style="background: #fff; border: 1px solid var(--color-outline-variant); border-radius: var(--radius-xl); padding: 36px; box-shadow: var(--shadow-sm);">
        <form action="index.php?admin_action=update_settings" method="POST">
            <h2 style="font-size: 18px; font-weight: 800; color: var(--color-primary); margin-bottom: 16px; border-bottom: 2px solid var(--color-outline-variant); padding-bottom: 8px;">
                ⚙️ Financial & Commission Configuration
            </h2>

            <div class="form-group">
                <label class="form-label" for="set-comm">Platform Commission Rate (%)</label>
                <input type="number" step="0.1" id="set-comm" name="commission_rate" class="form-control" value="<?= sanitize($settings['commission_rate'] ?? '12.0'); ?>" required>
                <span style="font-size: 11px; color: var(--color-outline);">Percentage retained per order transaction before farmer payout settlement.</span>
            </div>

            <h2 style="font-size: 18px; font-weight: 800; color: var(--color-primary); margin-top: 28px; margin-bottom: 16px; border-bottom: 2px solid var(--color-outline-variant); padding-bottom: 8px;">
                🚚 Delivery-Fee Tier Base Rates
            </h2>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label" for="set-w">Zone 1 Base Rate - Western (Rs.)</label>
                    <input type="number" step="0.01" id="set-w" name="tier_base_fee_western" class="form-control" value="<?= sanitize($settings['tier_base_fee_western'] ?? '150.00'); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="set-c">Zone 2 Base Rate - Central (Rs.)</label>
                    <input type="number" step="0.01" id="set-c" name="tier_base_fee_central" class="form-control" value="<?= sanitize($settings['tier_base_fee_central'] ?? '220.00'); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="set-s">Zone 3 Base Rate - Southern (Rs.)</label>
                    <input type="number" step="0.01" id="set-s" name="tier_base_fee_southern" class="form-control" value="<?= sanitize($settings['tier_base_fee_southern'] ?? '190.00'); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="set-n">Zone 4 Base Rate - Northern (Rs.)</label>
                    <input type="number" step="0.01" id="set-n" name="tier_base_fee_northern" class="form-control" value="<?= sanitize($settings['tier_base_fee_northern'] ?? '250.00'); ?>" required>
                </div>
            </div>

            <h2 style="font-size: 18px; font-weight: 800; color: var(--color-primary); margin-top: 28px; margin-bottom: 16px; border-bottom: 2px solid var(--color-outline-variant); padding-bottom: 8px;">
                ⏱️ Escrow & Auto-Release Policy
            </h2>

            <div class="form-group">
                <label class="form-label" for="set-timeout">Confirm Received Auto-Release Timeout (Hours)</label>
                <input type="number" id="set-timeout" name="auto_release_timeout_hours" class="form-control" value="<?= sanitize($settings['auto_release_timeout_hours'] ?? '48'); ?>" min="24" max="168" required>
                <span style="font-size: 11px; color: var(--color-outline);">Hours after doorstep delivery before escrow funds automatically release to farmer if buyer does not manually click Confirm Received. (Standard: 48-72h). No OTP is used anywhere in this system.</span>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 20px; padding: 12px 32px;">
                Save Settings to Database
            </button>
        </form>
    </div>
</div>
