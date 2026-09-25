<?php
require_once __DIR__ . '/../../Model/Delivery/Availability.php';

$courierId = $_SESSION['user_id'] ?? 5;
$availabilityModel = new Availability();
$currentStatus = $availabilityModel->get($courierId);
$isAvailable = ($currentStatus === 'AVAILABLE');
?>

<div class="page <?= ($page === 'availability' ? 'active' : '') ?>" id="page-availability">
    <div class="section-header">
        <h3>Availability Status</h3>
    </div>

    <div class="table-wrap" style="padding: 32px; max-width: 720px;">
        <div style="text-align: center; margin-bottom: 24px;">
            <i class="fas fa-truck" style="font-size: 48px; color: var(--md-primary); margin-bottom: 12px;"></i>
            <h4 style="margin: 8px 0;">Set Your Availability</h4>
            <p style="color: var(--md-on-surface-variant); font-size: 14px;">
                Only approved and available Courier Partners are eligible for automatic delivery assignment.
            </p>
        </div>

        <div class="availability-toggle-wrap" style="justify-content: center; margin-bottom: 24px;">
            <span class="toggle-label">Availability:</span>
            <div class="toggle-switch" id="availabilityTogglePage">
                <input type="checkbox" id="availCheckboxPage" <?= $isAvailable ? 'checked' : '' ?> />
                <label for="availCheckboxPage" class="slider"></label>
            </div>
            <span class="toggle-status" id="availStatusPage">
                <?= $isAvailable ? 'Available' : 'Unavailable' ?>
            </span>
        </div>

        <div class="coverage-info-banner">
            <i class="fas fa-info-circle"></i>
            <div>
                <strong>How it works</strong>
                <p>When an order becomes Ready for Delivery, Harvestly automatically selects an approved and available Courier Partner supporting the required district route.</p>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const checkbox = document.getElementById('availCheckboxPage');
    const statusEl = document.getElementById('availStatusPage');
    if (!checkbox || !statusEl) return;

    checkbox.addEventListener('change', function() {
        const newStatus = this.checked ? 'AVAILABLE' : 'UNAVAILABLE';

        const formData = new FormData();
        formData.append('action', 'set');
        formData.append('status', newStatus);

        // ⚠️ FIX: Delivery/ folder එකතු කරා
        fetch('../../Controller/Delivery/AvailabilityController.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.text())  // Debug: get raw text first
        .then(text => {
            console.log('Availability response:', text);

            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                alert('Server error. Check console.');
                this.checked = !this.checked;
                return;
            }

            if (!data.success) {
                alert('Failed to update availability');
                this.checked = !this.checked;
                return;
            }

            statusEl.textContent = newStatus === 'AVAILABLE' ? 'Available' : 'Unavailable';
            statusEl.style.color = newStatus === 'AVAILABLE' ? 'var(--md-primary)' : '#ba1a1a';
        })
        .catch(err => {
            alert('Network error');
            this.checked = !this.checked;
            console.error(err);
        });
    });
})();
</script>