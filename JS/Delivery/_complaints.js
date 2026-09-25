(function() {
    'use strict';

    const CONTROLLER_URL = '../../Controller/Delivery/IssueController.php';
    const submitBtn = document.getElementById('submitComplaintBtn');
    if (!submitBtn) return;

    // ---------- SUBMIT ISSUE ----------
    submitBtn.addEventListener('click', function() {
        const category = document.getElementById('issueCategory')?.value || '';
        const orderId = document.getElementById('issueOrder')?.value || '';
        const description = document.getElementById('issueDescription')?.value || '';
        const fileInput = document.getElementById('complaintFile');

        if (!category) {
            alert('⚠️ Please select a category.');
            return;
        }
        if (!description.trim()) {
            alert('⚠️ Please provide a description.');
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

        const formData = new FormData();
        formData.append('action', 'create');
        formData.append('category', category);
        formData.append('order_id', orderId);
        formData.append('description', description);

        if (fileInput && fileInput.files.length > 0) {
            formData.append('evidence', fileInput.files[0]);
        }

        fetch(CONTROLLER_URL, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Issue';

                if (data.success) {
                    alert('✅ ' + (data.message || 'Issue submitted!'));
                    // Reset form
                    document.getElementById('issueCategory').value = '';
                    document.getElementById('issueOrder').value = '';
                    document.getElementById('issueDescription').value = '';
                    // Reload page to show new issue
                    setTimeout(() => location.reload(), 800);
                } else {
                    alert('❌ ' + (data.message || 'Submission failed.'));
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Issue';
                alert('Network error.');
                console.error(err);
            });
    });

    // ---------- FILE UPLOAD LABEL ----------
    document.getElementById('complaintFile')?.addEventListener('change', function() {
        if (this.files.length > 0) {
            const label = this.closest('.file-upload').querySelector('span');
            if (label) label.textContent = this.files.length + ' file(s) selected';
        }
    });

    console.log('Issues module loaded');
})();