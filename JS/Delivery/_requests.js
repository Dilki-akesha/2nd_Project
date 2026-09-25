(function() {
    'use strict';

    // ============================================================ 
    // ACCEPT BUTTONS
    // ============================================================ 
    document.querySelectorAll('.btn-sm.btn-accept').forEach(btn => {
        btn.addEventListener('click', function() {
            const card = this.closest('.request-card');
            if (card) {
                const badge = card.querySelector('.status-badge');
                if (badge) {
                    badge.textContent = 'Accepted';
                    badge.className = 'status-badge completed';
                }
            }
            this.disabled = true;
            this.style.opacity = '0.6';

            const rejectBtn = card?.querySelector('.btn-reject');
            if (rejectBtn) rejectBtn.disabled = true;

            alert('✅ Delivery request accepted!');
        });
    });

    // ============================================================ 
    // REJECT BUTTONS (opens modal)
    // ============================================================ 
    const rejectModal = document.getElementById('rejectModal');
    const rejectOrderIdInput = document.getElementById('rejectOrderId');
    const rejectReasonSelect = document.getElementById('rejectReason');
    const rejectNotesInput = document.getElementById('rejectNotes');
    const rejectErrorEl = document.getElementById('rejectFormError');
    const confirmRejectBtn = document.getElementById('confirmRejectBtn');

    let currentRejectCard = null;

    document.querySelectorAll('.btn-sm.btn-reject').forEach(btn => {
        btn.addEventListener('click', function() {
            const card = this.closest('.request-card');
            const orderId = card?.querySelector('.order-id')?.textContent?.trim().split(' ')[0] || 'Unknown';
            currentRejectCard = card;

            rejectOrderIdInput.value = orderId;
            rejectReasonSelect.value = '';
            rejectNotesInput.value = '';
            rejectErrorEl.style.display = 'none';

            rejectModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
    });

    // ---------- CLOSE MODAL ----------
    window.closeRejectModal = function() {
        rejectModal.style.display = 'none';
        document.body.style.overflow = '';
        currentRejectCard = null;
    };

    // ---------- CONFIRM REJECTION ----------
    confirmRejectBtn?.addEventListener('click', function() {
        const reason = rejectReasonSelect.value;
        if (!reason) {
            rejectErrorEl.textContent = 'Please select a rejection reason.';
            rejectErrorEl.style.display = 'block';
            return;
        }

        // Update card
        if (currentRejectCard) {
            const badge = currentRejectCard.querySelector('.status-badge');
            if (badge) {
                badge.textContent = 'Rejected';
                badge.className = 'status-badge pending';
            }

            const acceptBtn = currentRejectCard.querySelector('.btn-accept');
            const rejectBtn = currentRejectCard.querySelector('.btn-reject');
            if (acceptBtn) acceptBtn.disabled = true;
            if (rejectBtn) rejectBtn.disabled = true;

            // Show rejection reason in card
            const cardBody = currentRejectCard.querySelector('.card-body');
            if (cardBody) {
                const reasonRow = document.createElement('div');
                reasonRow.className = 'detail-row';
                reasonRow.innerHTML = '<i class="fas fa-info-circle"></i> Reason: ' + reason;
                reasonRow.style.color = '#ba1a1a';
                cardBody.appendChild(reasonRow);
            }
        }

        closeRejectModal();
        showToast('Offer rejected');
    });

    // ---------- ESC ----------
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && rejectModal?.style.display === 'flex') closeRejectModal();
    });

    // ---------- TOAST ----------
    let toastTimer = null;
    function showToast(msg) {
        const t = document.getElementById('routeToast');
        const txt = document.getElementById('routeToastText');
        if (!t || !txt) return;
        txt.textContent = msg;
        t.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => t.classList.remove('show'), 3000);
    }

    // ---------- PRODUCE / URGENT (existing) ----------
    document.querySelectorAll('#page-requests .card-actions .btn-sm.btn-primary').forEach(btn => {
        btn.addEventListener('click', function() {
            const card = this.closest('.request-card');
            const orderId = card?.querySelector('.order-id')?.textContent?.trim().split(' ')[0] || 'Order';
            alert('📦 ' + orderId + ': Produce action triggered!');
            this.disabled = true;
            this.style.opacity = '0.6';
        });
    });

    document.querySelectorAll('#page-requests .card-actions .btn-sm.btn-warning').forEach(btn => {
        btn.addEventListener('click', function() {
            const card = this.closest('.request-card');
            const orderId = card?.querySelector('.order-id')?.textContent?.trim().split(' ')[0] || 'Order';
            alert('⚠️ ' + orderId + ' flagged as URGENT!');
            const badge = card?.querySelector('.status-badge');
            if (badge) {
                badge.textContent = 'Urgent';
                badge.className = 'status-badge pending';
            }
        });
    });

        // ============================================================ 
    // OFFER DEADLINE COUNTDOWN
    // ============================================================ 
    document.querySelectorAll('.offer-deadline').forEach(el => {
        const text = el.textContent.trim();
        const match = text.match(/(\d+)\s*min/);
        if (!match) return;

        let minutesLeft = parseInt(match[1]);
        const interval = setInterval(() => {
            minutesLeft--;
            if (minutesLeft <= 0) {
                el.innerHTML = '<i class="fas fa-clock"></i> Expired';
                el.style.color = '#707a6c';
                clearInterval(interval);

                // Disable buttons
                const card = el.closest('.request-card');
                card?.querySelectorAll('button').forEach(b => {
                    b.disabled = true;
                    b.style.opacity = '0.5';
                });

                // Badge → Expired
                const badge = card?.querySelector('.status-badge');
                if (badge) {
                    badge.textContent = 'Expired';
                    badge.className = 'status-badge pending';
                }
            } else {
                el.innerHTML = '<i class="fas fa-clock"></i> ' + minutesLeft + ' min left';
            }
        }, 60000); // Every minute
    });

    console.log('Requests module loaded (with Reject Modal)');
})();