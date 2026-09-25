<div class="page <?= ($page === 'requests' ? 'active' : '') ?>" id="page-requests">

 <div class="section-header">
    <h3>Delivery Requests</h3>
    <div class="actions" style="display:flex;gap:10px;flex-wrap:wrap;">
        <span class="status-badge pending">04 Requests</span>
        <span class="status-badge completed">$48.20 Est.</span>
        <span class="status-badge transit">12x Peak</span>
    </div>
 </div>

 <div class="cards-grid">
    <!-- Request Card 1 -->
    <div class="request-card">
        <div class="card-top">
            <span class="order-id">#HLY-9921 <span class="time-ago">· 10 mins ago</span></span>
            <span class="status-badge pending">Pending</span>
            <span class="offer-deadline" style="font-size:11px;color:#ba1a1a;margin-left:8px;">
               <i class="fas fa-clock"></i> 28 min left
            </span>
        </div>
        <div class="card-body">
            <div class="detail-row"><i class="fas fa-user"></i> Farmer: Sarah Jenkins</div>
            <div class="detail-row"><i class="fas fa-store"></i> Buyer: Green Leaf Bistro</div>
            <div class="detail-row"><i class="fas fa-map-pin"></i> Route: Caskwood Farm, Sector 4</div>
            <div class="detail-row"><i class="fas fa-arrow-up"></i> Origin: <strong>Kandy District</strong></div>
            <div class="detail-row"><i class="fas fa-arrow-down"></i> Destination: <strong>Colombo District</strong></div>
            <div class="detail-row"><i class="fas fa-dollar-sign"></i> Earnings: $12.50</div>
            <div class="detail-row"><i class="fas fa-tag"></i> Status: Done - Full Surcharge</div>
        </div>
        <div class="card-actions">
            <button class="btn-sm btn-accept"><i class="fas fa-check"></i> Receive</button>
            <button class="btn-sm btn-primary"><i class="fas fa-box"></i> Produce</button>
            <button class="btn-sm btn-warning"><i class="fas fa-exclamation"></i> Urgent</button>
            <button class="btn-sm btn-reject"><i class="fas fa-times"></i> Reject</button>
        </div>
    </div>

    <!-- Request Card 2 -->
    <div class="request-card">
        <div class="card-top">
            <span class="order-id">#HLY-8845 <span class="time-ago">· 22 mins ago</span></span>
            <span class="status-badge pending">Pending</span>
            <span class="offer-deadline" style="font-size:11px;color:#ba1a1a;margin-left:8px;">
                 <i class="fas fa-clock"></i> 12 min left
            </span>
        </div>
        <div class="card-body">
            <div class="detail-row"><i class="fas fa-user"></i> Farmer: Mike Rossi</div>
            <div class="detail-row"><i class="fas fa-store"></i> Buyer: Whole Foods Coop</div>
            <div class="detail-row"><i class="fas fa-map-pin"></i> Route: Riverbend Orchards</div>
            <div class="detail-row"><i class="fas fa-arrow-up"></i> Origin: <strong>Matale District</strong></div>
            <div class="detail-row"><i class="fas fa-arrow-down"></i> Destination: <strong>Gampaha District</strong></div>
            <div class="detail-row"><i class="fas fa-dollar-sign"></i> Earnings: $18.90</div>
            <div class="detail-row"><i class="fas fa-tag"></i> Status: Wholesale rate</div>
        </div>
        <div class="card-actions">
            <button class="btn-sm btn-accept"><i class="fas fa-tag"></i> Wholesale</button>
            <button class="btn-sm btn-reject"><i class="fas fa-times"></i> Reject</button>
        </div>
    </div>

    <!-- Request Card 3 -->
    <div class="request-card">
        <div class="card-top">
            <span class="order-id">#HLY-7102 <span class="time-ago">· 45 mins ago</span></span>
            <span class="status-badge pending">Pending</span>
            <span class="offer-deadline" style="font-size:11px;color:#ba1a1a;margin-left:8px;">
                <i class="fas fa-clock"></i> 5 min left
            </span>
        </div>
        <div class="card-body">
            <div class="detail-row"><i class="fas fa-user"></i> Farmer: Anita Gupta</div>
            <div class="detail-row"><i class="fas fa-store"></i> Buyer: Residential #142</div>
            <div class="detail-row"><i class="fas fa-map-pin"></i> Route: The Greenhouse Annex</div>
            <div class="detail-row"><i class="fas fa-arrow-up"></i> Origin: <strong>Nuwara Eliya District</strong></div>
            <div class="detail-row"><i class="fas fa-arrow-down"></i> Destination: <strong>Colombo District</strong></div>
            <div class="detail-row"><i class="fas fa-dollar-sign"></i> Earnings: $8.25</div>
            <div class="detail-row"><i class="fas fa-tag"></i> Status: Standard Short-Haul</div>
        </div>
        <div class="card-actions">
            <button class="btn-sm btn-accept"><i class="fas fa-leaf"></i> Organic</button>
            <button class="btn-sm btn-reject"><i class="fas fa-times"></i> Reject</button>
        </div>
    </div>
 </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔵 Requests page handlers loaded');

    // ---- PRODUCE BUTTON (btn-primary) ----
    var produceBtns = document.querySelectorAll('#page-requests .card-actions .btn-primary');
    console.log('Produce buttons: ' + produceBtns.length);
    produceBtns.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var card = this.closest('.request-card');
            var orderId = card ? card.querySelector('.order-id').textContent.trim().split(' ')[0] : 'Order';
            alert('📦 ' + orderId + ': Produce action triggered!');
            this.disabled = true;
            this.style.opacity = '0.6';
        });
    });

    // ---- URGENT BUTTON (btn-warning) ----
    var urgentBtns = document.querySelectorAll('#page-requests .card-actions .btn-warning');
    console.log('Urgent buttons: ' + urgentBtns.length);
    urgentBtns.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var card = this.closest('.request-card');
            var orderId = card ? card.querySelector('.order-id').textContent.trim().split(' ')[0] : 'Order';
            alert('⚠️ ' + orderId + ' flagged as URGENT!');
            var badge = card ? card.querySelector('.status-badge') : null;
            if (badge) {
                badge.textContent = 'Urgent';
                badge.className = 'status-badge pending';
            }
        });
    });
});
</script>

<!-- Reject Reason Modal -->
<div id="rejectModal" class="route-modal">
    <div class="route-modal-overlay" onclick="closeRejectModal()"></div>
    <div class="route-modal-box">
        <div class="route-modal-header">
            <h3><i class="fas fa-times-circle"></i> Reject Assignment Offer</h3>
            <button class="route-modal-close" onclick="closeRejectModal()">&times;</button>
        </div>
        <form id="rejectForm" onsubmit="return false;">
            <input type="hidden" id="rejectOrderId" value="" />
            <div class="route-modal-body">
                <p style="font-size:13px;color:var(--md-on-surface-variant);margin-bottom:16px;">
                    Please tell us why you're rejecting this offer.
                </p>

                <div class="form-group">
                    <label for="rejectReason">Rejection Reason <span style="color:#ba1a1a;">*</span></label>
                    <select id="rejectReason" required>
                        <option value="">Select a reason</option>
                        <option>Route not covered</option>
                        <option>Vehicle unavailable</option>
                        <option>Capacity full</option>
                        <option>Pricing too low</option>
                        <option>Delivery address too far</option>
                        <option>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="rejectNotes">Additional Notes (Optional)</label>
                    <textarea id="rejectNotes" placeholder="Provide any additional details..." style="min-height:80px;"></textarea>
                </div>

                <div id="rejectFormError" class="route-form-error" style="display:none;"></div>
            </div>
            <div class="route-modal-footer">
                <button type="button" class="btn-secondary" onclick="closeRejectModal()">Cancel</button>
                <button type="button" class="btn-primary" id="confirmRejectBtn">
                    <i class="fas fa-times"></i> Confirm Rejection
                </button>
            </div>
        </form>
    </div>
</div>

<div id="routeToast" class="route-toast">
    <i class="fas fa-check-circle"></i>
    <span id="routeToastText">Rejected</span>
</div>