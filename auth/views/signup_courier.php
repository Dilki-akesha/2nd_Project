<?php
$error = isset($_GET['error']) ? sanitize($_GET['error']) : null;
?>
<div class="page-content" style="max-width: 580px; margin: 50px auto; padding: 0 20px;">
    <div style="background-color: var(--color-surface); border: 1px solid var(--color-outline-variant); border-radius: var(--radius-xl); padding: 36px; box-shadow: var(--shadow-md);">
        <div style="margin-bottom: 24px;">
            <a href="index.php?page=role_select" style="font-size: 13px; color: var(--color-outline); font-weight: 600;">&larr; Change Role</a>
            <h1 style="font-size: 24px; font-weight: 800; color: var(--color-on-surface); margin-top: 8px;">Courier Partner Fleet Registration</h1>
            <p style="font-size: 14px; color: var(--color-on-surface-variant);">Register your logistics company with official Business Registration (BRN)</p>
        </div>

        <div style="background-color: var(--color-surface-container-low); padding: 12px 16px; border-radius: var(--radius-md); border-left: 4px solid var(--color-primary); font-size: 12px; color: var(--color-on-surface-variant); margin-bottom: 20px;">
            ℹ️ <strong>Company Logistics Policy:</strong> Only registered logistics companies (PV/PB numbers) can register as Courier Partners on Harvestly. Individual drivers are not supported.
        </div>

        <?php if ($error): ?>
            <div style="background-color: var(--color-error-container); color: var(--color-error); padding: 12px 16px; border-radius: var(--radius-md); font-size: 13px; font-weight: 600; margin-bottom: 20px;">
                ⚠️ <?= $error; ?>
            </div>
        <?php endif; ?>

        <form action="index.php?action=signup_courier" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label class="form-label" for="c-comp">Registered Company Name</label>
                <input type="text" id="c-comp" name="company_name" class="form-control" placeholder="e.g. Lanka Agro Logistics Pvt Ltd" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="c-brn">Business Registration Number (BRN)</label>
                <input type="text" id="c-brn" name="brn_number" class="form-control" placeholder="e.g. PV-0089123" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label" for="c-contact">Contact Person Name</label>
                    <input type="text" id="c-contact" name="contact_person" class="form-control" placeholder="Nimal Silva" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="c-phone">Company Phone</label>
                    <input type="text" id="c-phone" name="phone" class="form-control" placeholder="+94 11 234 5678" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="c-email">Corporate Email Address</label>
                <input type="email" id="c-email" name="email" class="form-control" placeholder="logistics@company.lk" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="c-pass">Password</label>
                <input type="password" id="c-pass" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="c-district">Primary Fleet Operations District</label>
                <select id="c-district" name="district" class="form-control" required>
                    <option value="Colombo">Colombo</option>
                    <option value="Gampaha">Gampaha</option>
                    <option value="Kandy">Kandy</option>
                    <option value="Nuwara Eliya">Nuwara Eliya</option>
                    <option value="Jaffna">Jaffna</option>
                    <option value="Galle">Galle</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="c-address">Headquarters Business Address</label>
                <textarea id="c-address" name="business_address" class="form-control" rows="2" placeholder="Full registered company address..." required></textarea>
            </div>

            <div class="form-group" style="background: var(--color-surface-container-low); padding: 16px; border-radius: var(--radius-md); border: 1px dashed var(--color-outline-variant);">
                <label class="form-label" for="c-cert">Upload Business Registration Certificate (PDF, JPG, PNG)</label>
                <input type="file" id="c-cert" name="registration_cert" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                <span style="font-size: 11px; color: var(--color-outline); margin-top: 4px; display: block;">Official BRN certificate. Reviewed in Admin Verification Queue.</span>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 16px; padding: 12px;">
                Submit Company Fleet Application
            </button>
        </form>
    </div>
</div>
