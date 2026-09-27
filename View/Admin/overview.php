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
                <span class="kpi-value kpi-value-danger"><?= number_format($kpis['active_complaints']); ?></span>
                <span class="kpi-label">Open Complaints</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon-box">📜</div>
            <div class="kpi-data">
                <span class="kpi-value kpi-value-warning"><?= number_format($kpis['pending_verifications']); ?></span>
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
    <div class="overview-grid">
        <!-- HTML Bar Chart - Order Volume Trend -->
        <div class="chart-container">
            <div class="chart-header">
                <div>
                    <h3>Weekly Order Volume Trend</h3>
                    <span>Orders per day (This week)</span>
                </div>
                <span class="chart-summary">Marketplace activity</span>
            </div>
            <div class="bar-chart">
                <?php $counts=array_column($weeklyOrders,'total','day');$max=max(1,...array_values($counts));for($i=6;$i>=0;$i--):$day=date('Y-m-d',strtotime("-$i days"));$count=(int)($counts[$day]??0); ?>
                <div class="bar-col"><span class="bar-value"><?=$count?></span><div class="bar-fill" style="height:<?=max(2,($count/$max)*100)?>%"></div><span class="bar-label"><?=e(date('D',strtotime($day)))?></span></div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Recent Activity Feed -->
        <div class="activity-panel">
            <h3>Recent Activity Feed</h3>
            <div class="activity-list">
                <?php if (empty($activities)): ?>
                    <p class="empty-state">No recent activity logged.</p>
                <?php else: ?>
                    <?php foreach ($activities as $act): ?>
                        <div class="activity-item">
                            <div class="activity-type"><?= sanitize($act['type']); ?></div>
                            <div class="activity-detail"><?= sanitize($act['detail']); ?></div>
                            <div class="activity-time"><?= date('M d, H:i', strtotime($act['created_at'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
