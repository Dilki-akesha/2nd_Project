<div class="page <?= ($page === 'tracking' ? 'active' : '') ?>" id="page-tracking">
    <div class="section-header">
        <h3>Order Tracking: #HLY-9821</h3>
        <span class="status-badge transit" id="trackingStatusBadge">In Transit</span>
    </div>

    <div class="tracking-grid">
        <!-- Left Column -->
        <div>
            <!-- Journey Progress (5-step stepper) -->
            <div class="table-wrap">
                <div class="panel-title"><i class="fas fa-route"></i> Journey Progress</div>
                <div class="timeline" id="timelineStepper">
                    <!-- Steps injected by JS -->
                </div>
            </div>

            <!-- Order Details -->
            <div class="table-wrap">
                <div class="order-details-grid">
                    <div><strong>Organic Carrots</strong><br>Bulk <span class="text-muted">20kg</span></div>
                    <div><strong>Honeycrisp Apples</strong><br>Retail <span class="text-muted">10ct</span></div>
                    <div><strong>Weight</strong><br>24.5 kg</div>
                    <div><strong>Handling</strong><br>$5.00</div>
                    <div><strong>Delivery Fee</strong><br>$45.00</div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div>
            <!-- Pickup Contact + Actions -->
            <div class="table-wrap">
                <div class="farm-contact">
                    <i class="fas fa-store"></i>
                    <strong>Green Valley Organic Farms</strong>
                    <span class="contact-name">Contact: Arthur</span>
                </div>
                <div class="action-buttons">
                    <button class="btn-sm btn-primary full-width" id="updateStageBtn">
                        <i class="fas fa-arrow-right"></i> Update Stage
                    </button>
                    <button class="btn-sm btn-warning full-width" id="rescheduleBtn">
                        <i class="fas fa-home"></i> Buyer Not Home? Reschedule
                    </button>
                </div>
            </div>

            <!-- Buyer Unavailable Flow (Max 2 Attempts) -->
            <div class="table-wrap">
                <div class="panel-title"><i class="fas fa-exclamation-triangle"></i> Delivery Attempts</div>
                <div style="padding: 16px 22px;">
                    <div id="attemptInfo" style="font-size: 13px; color: var(--md-on-surface-variant); margin-bottom: 12px;">
                        <strong>Attempts:</strong> <span id="attemptCount">0</span> / 2
                    </div>

                    <button class="btn-sm btn-warning full-width" id="recordUnavailableBtn" style="margin-bottom: 8px;">
                        <i class="fas fa-user-slash"></i> Record Buyer Unavailable
                    </button>

                    <div id="undeliverableMsg" style="display:none; background:#FFEBEE; color:#C62828; padding:10px 14px; border-radius:8px; font-size:13px; margin-top:8px;">
                        <i class="fas fa-ban"></i> Maximum delivery attempts reached. Admin has been notified.
                    </div>
                </div>
            </div>

            <!-- Buyer Contact -->
            <div class="table-wrap">
                <div class="live-location">
                    <i class="fas fa-user" style="color:var(--md-primary);"></i>
                    <strong>Buyer Contact</strong>
                    <div class="buyer-info">
                        <strong>Sarah Thompson</strong><br>
                        <span class="text-muted">3410 Arden Way, Sacramento</span><br>
                        <span class="text-muted"><i class="fas fa-phone"></i> +1 (555) 987-6543</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>