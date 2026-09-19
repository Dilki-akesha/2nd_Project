<div class="page-content">
    <div class="section-header">
        <div class="section-title-group">
            <h1>Operations Overview</h1>
            <p>Real-time Sri Lankan agricultural marketplace statistics & volume trends</p>
        </div>
        <div>
            <span class="badge badge-success">● System Operational</span>
        </div>
    </div>

    <!-- KPI Cards Grid -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon-box">👨‍🌾</div>
            <div class="kpi-data">
                <span class="kpi-value"><?= number_format($kpis['total_farmers']); ?></span>
                <span class="kpi-label">Active Farmers</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon-box">🛒</div>
            <div class="kpi-data">
                <span class="kpi-value"><?= number_format($kpis['total_buyers']); ?></span>
                <span class="kpi-label">Registered Buyers</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon-box">🚚</div>
            <div class="kpi-data">
                <span class="kpi-value"><?= number_format($kpis['total_couriers']); ?></span>
                <span class="kpi-label">Courier Companies</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon-box">📦</div>
            <div class="kpi-data">
                <span class="kpi-value"><?= number_format($kpis['total_orders']); ?></span>
                <span class="kpi-label">Total Orders</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon-box">⚠️</div>
            <div class="kpi-data">
                <span class="kpi-value" style="color: var(--color-error);"><?= number_format($kpis['active_disputes']); ?></span>
                <span class="kpi-label">Active Disputes</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon-box">📜</div>
            <div class="kpi-data">
                <span class="kpi-value" style="color: var(--color-warning);"><?= number_format($kpis['pending_verifications']); ?></span>
                <span class="kpi-label">Pending Verification</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon-box">💰</div>
            <div class="kpi-data">
                <span class="kpi-value">Rs. <?= number_format($kpis['weekly_settlements'], 2); ?></span>
                <span class="kpi-label">Weekly Settlements</span>
            </div>
        </div>
    </div>

    <!-- Charts & Trends Section -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 32px;">
        <!-- HTML Bar Chart - Order Volume Trend -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 style="font-size: 16px; font-weight: 700;">Weekly Order Volume Trend</h3>
                <span style="font-size: 12px; color: var(--color-outline);">Orders per day (This week)</span>
            </div>
            <div class="bar-chart">
                <div class="bar-col">
                    <span class="bar-value">42</span>
                    <div class="bar-fill" style="height: 60%;"></div>
                    <span class="bar-label">Mon</span>
                </div>
                <div class="bar-col">
                    <span class="bar-value">58</span>
                    <div class="bar-fill" style="height: 80%;"></div>
                    <span class="bar-label">Tue</span>
                </div>
                <div class="bar-col">
                    <span class="bar-value">35</span>
                    <div class="bar-fill" style="height: 50%;"></div>
                    <span class="bar-label">Wed</span>
                </div>
                <div class="bar-col">
                    <span class="bar-value">64</span>
                    <div class="bar-fill" style="height: 90%;"></div>
                    <span class="bar-label">Thu</span>
                </div>
                <div class="bar-col">
                    <span class="bar-value">72</span>
                    <div class="bar-fill" style="height: 100%;"></div>
                    <span class="bar-label">Fri</span>
                </div>
                <div class="bar-col">
                    <span class="bar-value">50</span>
                    <div class="bar-fill" style="height: 70%;"></div>
                    <span class="bar-label">Sat</span>
                </div>
                <div class="bar-col">
                    <span class="bar-value">28</span>
                    <div class="bar-fill" style="height: 40%;"></div>
                    <span class="bar-label">Sun</span>
                </div>
            </div>
        </div>

        <!-- Recent Activity Feed -->
        <div style="background: #fff; border: 1px solid var(--color-outline-variant); border-radius: var(--radius-lg); padding: 24px;">
            <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 16px;">Recent Activity Feed</h3>
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <?php if (empty($activities)): ?>
                    <p style="font-size: 13px; color: var(--color-outline);">No recent activity logged.</p>
                <?php else: ?>
                    <?php foreach ($activities as $act): ?>
                        <div style="padding-bottom: 10px; border-bottom: 1px solid var(--color-outline-variant); font-size: 13px;">
                            <div style="font-weight: 700; color: var(--color-primary);"><?= sanitize($act['type']); ?></div>
                            <div style="color: var(--color-on-surface); margin-top: 2px;"><?= sanitize($act['detail']); ?></div>
                            <div style="font-size: 11px; color: var(--color-outline); margin-top: 2px;"><?= date('M d, H:i', strtotime($act['created_at'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
