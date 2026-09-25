<div class="page <?= ($page === 'earnings' ? 'active' : '') ?>" id="page-earnings">
    <div class="section-header">
        <h3>Earnings / Payouts</h3>
        <button class="btn-outline"><i class="fas fa-download"></i> Download Report</button>
    </div>

    <!-- Earnings Summary -->
    <div class="earnings-summary">
        <div class="earning-box">
            <div class="amount orange">LKR 8,450.00</div>
            <div class="label">Held</div>
        </div>
        <div class="earning-box">
            <div class="amount blue">LKR 12,200.50</div>
            <div class="label">Pending Payout</div>
        </div>
        <div class="earning-box">
            <div class="amount green">LKR 145,800.00</div>
            <div class="label">Paid</div>
        </div>
        <div class="earning-box">
            <div class="amount green">LKR 166,450.50</div>
            <div class="label">Total Earnings</div>
        </div>
    </div>

    <!-- Chart + Regional -->
    <div class="earnings-grid">
        <div class="chart-container">
            <div class="chart-header">
                <span class="chart-title">Income Trends</span>
                <span class="chart-subtitle">Monthly</span>
            </div>
            <canvas id="earningsChart"></canvas>
        </div>
        <div>
            <div class="table-wrap">
                <div class="panel-title">Regional Performance</div>
                <div class="regional-list">
                    <div><span class="status-badge completed">45%</span> Downtown Logistics</div>
                    <div><span class="status-badge transit">32%</span> North Valley Routes</div>
                    <div><span class="status-badge pending">23%</span> Westside Hub</div>
                </div>
                <div class="referral-box">
                    <i class="fas fa-share-alt"></i>
                    <p><strong>Refer a Courier</strong></p>
                    <p style="font-size:12px;color:var(--md-on-surface-variant);">Earn up to LKR 25,000</p>
                    <button class="btn-sm btn-primary">Get Link</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Earnings Per Delivery -->
    <div class="section-header" style="margin-top:24px;">
        <h3>Earnings Per Delivery</h3>
    </div>
    <div class="table-wrap">
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Delivery Fee</th>
                        <th>Earning Status</th>
                        <th>Settlement Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>#HLY-8845</strong></td>
                        <td>LKR 150.00</td>
                        <td><span class="status-badge completed">Paid</span></td>
                        <td>Sep 07, 2026</td>
                    </tr>
                    <tr>
                        <td><strong>#HLY-8832</strong></td>
                        <td>LKR 220.00</td>
                        <td><span class="status-badge progress">Pending Payout</span></td>
                        <td>—</td>
                    </tr>
                    <tr>
                        <td><strong>#HLY-8821</strong></td>
                        <td>LKR 185.00</td>
                        <td><span class="status-badge completed">Paid</span></td>
                        <td>Sep 10, 2026</td>
                    </tr>
                    <tr>
                        <td><strong>#HLY-8801</strong></td>
                        <td>LKR 190.00</td>
                        <td><span class="status-badge pending">Held</span></td>
                        <td>—</td>
                    </tr>
                    <tr>
                        <td><strong>#HLY-8794</strong></td>
                        <td>LKR 160.00</td>
                        <td><span class="status-badge review">Cancelled</span></td>
                        <td>—</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payment History -->
    <div class="section-header" style="margin-top:24px;">
        <h3>Recent Settlements</h3>
    </div>
    <div class="table-wrap">
        <div class="table-scroll">
            <table>
                <thead>
                    <tr><th>Date</th><th>Reference</th><th>Amount</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Sep 07, 2026</td>
                        <td>SET-C-2026-001</td>
                        <td>LKR 1,420.50</td>
                        <td><span class="status-badge completed">Completed</span></td>
                    </tr>
                    <tr>
                        <td>Aug 31, 2026</td>
                        <td>SET-C-2026-002</td>
                        <td>LKR 2,180.00</td>
                        <td><span class="status-badge completed">Completed</span></td>
                    </tr>
                    <tr>
                        <td>Aug 24, 2026</td>
                        <td>SET-C-2026-003</td>
                        <td>LKR 1,860.75</td>
                        <td><span class="status-badge completed">Completed</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>