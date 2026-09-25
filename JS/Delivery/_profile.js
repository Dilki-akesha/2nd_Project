(function() {
    'use strict';

    const CONTROLLER_URL = '../../Controller/Delivery/ProfileController.php';
    const form = document.getElementById('profileForm');
    if (!form) return;

    const inputs = form.querySelectorAll('input, select');

    // Save original values for Cancel
    let originalValues = {};
    inputs.forEach(inp => {
        originalValues[inp.name] = inp.value;
    });

    // ---------- EDIT ----------
    document.getElementById('editProfileBtn')?.addEventListener('click', function() {
        inputs.forEach(inp => inp.removeAttribute('readonly'));
        inputs.forEach(inp => inp.focus());
        this.disabled = true;
        alert('✏️ Edit mode enabled. Modify fields and click Save Changes.');
    });

    // ---------- CANCEL ----------
    document.getElementById('cancelProfileBtn')?.addEventListener('click', function() {
        inputs.forEach(inp => {
            inp.value = originalValues[inp.name] || '';
            inp.setAttribute('readonly', true);
        });
        document.getElementById('editProfileBtn').disabled = false;
        alert('❌ Changes cancelled.');
    });

    // ---------- SAVE ----------
    document.getElementById('saveProfileBtn')?.addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        const formData = new FormData(form);
        formData.append('action', 'update');

        fetch(CONTROLLER_URL, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Save Changes';

                if (data.success) {
                    // Update original values
                    inputs.forEach(inp => {
                        originalValues[inp.name] = inp.value;
                        inp.setAttribute('readonly', true);
                    });
                    document.getElementById('editProfileBtn').disabled = false;
                    alert('✅ ' + (data.message || 'Profile updated successfully!'));
                } else {
                    alert('❌ ' + (data.message || 'Update failed.'));
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
                alert('Network error.');
                console.error(err);
            });
    });

    // Initially readonly
    inputs.forEach(inp => inp.setAttribute('readonly', true));

    console.log('Profile module loaded');
})();