// ============================================================ 
// DELIVERY TRACKING — 5-STEP STEPPER + Buyer Unavailable
// ============================================================ 

(function() {
    'use strict';

    // ============================================================ 
    // 5 STAGES (spec එකට අනුව — hub steps නෑ)
    // ============================================================ 
    const stages = [
        'Assigned',
        'Picked Up',
        'In Transit',
        'Out for Delivery',
        'Delivered'
    ];

    let currentStageIndex = 2;  // Start at "In Transit" for demo

    const stepperContainer = document.getElementById('timelineStepper');
    const updateBtn = document.getElementById('updateStageBtn');
    const statusBadge = document.getElementById('trackingStatusBadge');
    const rescheduleBtn = document.getElementById('rescheduleBtn');

    // ---------- STAGE INFO (time + description) ----------
    function getStageInfo(idx) {
        const info = [
            { time: 'Oct 24, 2023 - 08:30 AM', desc: 'Order confirmed by Harvestly' },
            { time: 'Oct 24, 2023 - 10:15 AM', desc: 'Collected from Green Valley Farm' },
            { time: 'Expected by 2:30 PM',     desc: '4.2km from delivery location' },
            { time: 'Expected by 3:00 PM',     desc: 'Out for doorstep delivery' },
            { time: 'Pending arrival',         desc: 'Delivery complete' }
        ];
        return info[idx] || { time: 'Pending', desc: '' };
    }

    // ---------- RENDER STEPPER ----------
    function renderStepper() {
        if (!stepperContainer) return;

        let html = '';
        stages.forEach((stage, idx) => {
            let dotClass = 'dot';
            if (idx < currentStageIndex) dotClass += ' completed';
            else if (idx === currentStageIndex) dotClass += ' active';

            const stageInfo = getStageInfo(idx);
            html += `
                <div class="timeline-item">
                    <div class="${dotClass}"></div>
                    <div class="content">
                        <div class="title">${stage}</div>
                        <div class="time">${stageInfo.time}</div>
                        <div class="desc">${stageInfo.desc}</div>
                    </div>
                </div>
            `;
        });
        stepperContainer.innerHTML = html;

        if (statusBadge) {
            statusBadge.textContent = stages[currentStageIndex];
            statusBadge.className = 'status-badge ' + (
                currentStageIndex === stages.length - 1 ? 'completed' : 'transit'
            );
        }

        if (updateBtn) {
            if (currentStageIndex === stages.length - 1) {
                updateBtn.innerHTML = '<i class="fas fa-check"></i> Already Delivered';
                updateBtn.disabled = true;
                updateBtn.style.opacity = '0.6';
            } else if (currentStageIndex === stages.length - 2) {
                updateBtn.innerHTML = '<i class="fas fa-check"></i> Mark as Delivered';
            } else {
                updateBtn.innerHTML = '<i class="fas fa-arrow-right"></i> Update Stage';
            }
        }
    }

    if (stepperContainer) renderStepper();

    // ---------- UPDATE STAGE ----------
    if (updateBtn) {
        updateBtn.addEventListener('click', function() {
            if (currentStageIndex < stages.length - 1) {
                currentStageIndex++;

                if (currentStageIndex === stages.length - 1) {
                    // Reached Delivered → confirm
                    if (confirm('Confirm that this order has been delivered to the Buyer?')) {
                        renderStepper();
                        alert('✅ Marked as Delivered.\n\nBuyer will confirm receipt, or order auto-completes after 48 hours.');
                    } else {
                        currentStageIndex--;
                    }
                } else {
                    renderStepper();
                    alert(`📦 Stage updated to "${stages[currentStageIndex]}"`);
                }
            }
        });
    }

    // ---------- RESCHEDULE ----------
    if (rescheduleBtn) {
        rescheduleBtn.addEventListener('click', function() {
            if (currentStageIndex === stages.length - 1) {
                alert('⚠️ Order already delivered. Reschedule not available.');
            } else {
                alert('📅 Delivery rescheduled for tomorrow.');
            }
        });
    }

    // ============================================================ 
    // BUYER UNAVAILABLE FLOW (DB-connected)
    // ============================================================ 
    const DELIVERY_ID = 1;  // TODO: Get from URL or page context
    const ATTEMPT_CONTROLLER = '../../Controller/Delivery/DeliveryAttemptController.php';

    const unavailableBtn = document.getElementById('recordUnavailableBtn');
    const attemptCountEl = document.getElementById('attemptCount');
    const undeliverableMsg = document.getElementById('undeliverableMsg');

    // Load current count on page load
    fetch(ATTEMPT_CONTROLLER + '?action=getCount&delivery_id=' + DELIVERY_ID)
        .then(r => r.json())
        .then(data => {
            if (data.success && attemptCountEl) {
                attemptCountEl.textContent = data.attempt_count;
            }
        })
        .catch(err => console.error('Load attempts error:', err));

    if (unavailableBtn) {
        unavailableBtn.addEventListener('click', function() {
            const btn = this;
            btn.disabled = true;

            const formData = new FormData();
            formData.append('action', 'record');
            formData.append('delivery_id', DELIVERY_ID);

            fetch(ATTEMPT_CONTROLLER, { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (attemptCountEl && data.attempt_count) {
                        attemptCountEl.textContent = data.attempt_count;
                    }

                    if (!data.success) {
                        alert('⚠️ ' + (data.message || 'Cannot record attempt.'));
                        btn.disabled = data.attempt_count >= 2;
                        return;
                    }

                    alert('📝 ' + data.message);

                    if (data.status === 'UNDELIVERABLE') {
                        if (undeliverableMsg) undeliverableMsg.style.display = 'block';
                        btn.disabled = true;
                        btn.style.opacity = '0.6';

                        if (statusBadge) {
                            statusBadge.textContent = 'Undeliverable';
                            statusBadge.className = 'status-badge pending';
                        }
                    } else {
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    alert('Network error');
                    console.error(err);
                    btn.disabled = false;
                });
        });
    }
})();