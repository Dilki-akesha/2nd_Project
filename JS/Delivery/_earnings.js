// ============================================================ */
// EARNINGS PAGE — Chart + Transaction Details Modal
// ============================================================ */

// ============================================================ */
// EARNINGS CHART
// ============================================================ */
let earningsChartInstance = null;

function initEarningsChart() {
    const canvas = document.getElementById('earningsChart');
    if (!canvas) return;

    if (earningsChartInstance) {
        earningsChartInstance.destroy();
        earningsChartInstance = null;
    }

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    const rect = canvas.getBoundingClientRect();
    if (rect.width === 0 || rect.height === 0) {
        setTimeout(initEarningsChart, 100);
        return;
    }

    earningsChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
            datasets: [{
                label: 'Earnings ($)',
                data: [3200, 2800, 3400, 4100, 3800, 4500, 4900, 5200, 4800, 5600, 6100],
                backgroundColor: 'rgba(46, 125, 50, 0.7)',
                borderColor: '#2E7D32',
                borderWidth: 2,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { callback: v => '$' + v.toLocaleString() }
                },
                x: { grid: { display: false } }
            }
        }
    });
    console.log('📊 Chart initialised');
}
window.initEarningsChart = initEarningsChart;

// ============================================================ */
// TRANSACTION DETAILS MODAL
// ============================================================ */
function showTransactionModal(data) {
    let modal = document.getElementById('txnModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'txnModal';
        modal.innerHTML =
            '<div class="modal-overlay" onclick="closeTransactionModal()"></div>' +
            '<div class="modal-box">' +
                '<div class="modal-header">' +
                    '<h3><i class="fas fa-file-invoice"></i> Transaction Details</h3>' +
                    '<button class="modal-close" onclick="closeTransactionModal()">×</button>' +
                '</div>' +
                '<div class="modal-body" id="txnModalBody"></div>' +
                '<div class="modal-footer">' +
                    '<button class="btn-secondary" onclick="closeTransactionModal()">Close</button>' +
                '</div>' +
            '</div>';
        document.body.appendChild(modal);
    }

    document.getElementById('txnModalBody').innerHTML =
        '<div class="txn-detail-row"><span class="txn-label">Transaction ID</span><span class="txn-value"><strong>' + data.id + '</strong></span></div>' +
        '<div class="txn-detail-row"><span class="txn-label">Date</span><span class="txn-value">' + data.date + '</span></div>' +
        '<div class="txn-detail-row"><span class="txn-label">Amount</span><span class="txn-value txn-amount">' + data.amount + '</span></div>' +
        '<div class="txn-detail-row"><span class="txn-label">Status</span><span class="txn-value">' + data.status + '</span></div>' +
        '<div class="txn-detail-row"><span class="txn-label">Method</span><span class="txn-value">' + data.method + '</span></div>' +
        '<hr style="margin: 16px 0;">' +
        '<div class="txn-detail-row"><span class="txn-label">Reference</span><span class="txn-value">ACCT-****-4532 → Bank Transfer</span></div>' +
        '<div class="txn-detail-row"><span class="txn-label">Processed</span><span class="txn-value">' + data.date + ', 3:42 PM</span></div>';

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeTransactionModal() {
    const modal = document.getElementById('txnModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}
window.showTransactionModal = showTransactionModal;
window.closeTransactionModal = closeTransactionModal;

// ============================================================ */
// ATTACH DETAILS BUTTONS
// ============================================================ */
function attachDetailsButtons() {
    var rows = document.querySelectorAll('#page-earnings .table-wrap tbody tr');
    console.log('🔵 Found ' + rows.length + ' rows in earnings tables');

    rows.forEach(function(row) {
        var detailsBtn = row.querySelector('button.btn-outline');
        if (!detailsBtn) return;

        if (detailsBtn.dataset.listenerAttached === 'yes') return;
        detailsBtn.dataset.listenerAttached = 'yes';

        detailsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            var cells = row.querySelectorAll('td');
            if (cells.length < 5) return;

            console.log('🟢 Details clicked: ' + cells[1].textContent.trim());

            showTransactionModal({
                date: cells[0].textContent.trim(),
                id: cells[1].textContent.trim(),
                amount: cells[2].textContent.trim(),
                status: cells[3].textContent.trim(),
                method: cells[4].textContent.trim()
            });
        });
    });
}

// ============================================================ */
// INIT
// ============================================================ */
document.addEventListener('DOMContentLoaded', function() {
    const earningsPage = document.getElementById('page-earnings');
    if (earningsPage && earningsPage.classList.contains('active')) {
        setTimeout(initEarningsChart, 300);
    }

    attachDetailsButtons();
    attachDownloadButton();
    attachReferralButton();

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeTransactionModal();
    });

    console.log('🍃 Earnings module loaded');
});
// ============================================================ 
// DOWNLOAD REPORT
// ============================================================ 
function downloadEarningsReport() {
    // Get data from the table
    var rows = document.querySelectorAll('#page-earnings .table-wrap tbody tr');
    var csv = 'Date,Transaction,Amount,Status,Method\n';
    
    // Add Payment History rows
    var paymentRows = document.querySelectorAll('#page-earnings .table-wrap:nth-of-type(2) tbody tr');
    paymentRows.forEach(function(row) {
        var cells = row.querySelectorAll('td');
        if (cells.length >= 5) {
            var line = [
                cells[0].textContent.trim(),
                cells[1].textContent.trim(),
                cells[2].textContent.trim(),
                cells[3].textContent.trim(),
                cells[4].textContent.trim()
            ].join(',');
            csv += line + '\n';
        }
    });
    
    // Create download
    var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    var url = URL.createObjectURL(blob);
    var link = document.createElement('a');
    link.href = url;
    link.download = 'harvestly-earnings-' + new Date().toISOString().split('T')[0] + '.csv';
    link.click();
    URL.revokeObjectURL(url);
    
    console.log('Report downloaded');
}

// Attach to Download Report button
function attachDownloadButton() {
    var btn = document.querySelector('#page-earnings .section-header .btn-outline');
    if (!btn) return;
    if (btn.dataset.downloadAttached === 'yes') return;
    btn.dataset.downloadAttached = 'yes';
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        downloadEarningsReport();
    });
    console.log('Download button attached');
}
// ============================================================ 
// REFERRAL LINK
// ============================================================ 
function generateReferralLink() {
    // In a real app, this would come from your backend
    // For now, use a random code
    var refCode = 'MT' + Math.floor(1000 + Math.random() * 9000);
    var link = 'https://harvestly.com/register?ref=' + refCode;
    return link;
}

function attachReferralButton() {
    var btn = document.querySelector('#page-earnings .referral-box .btn-primary');
    if (!btn) return;
    if (btn.dataset.referralAttached === 'yes') return;
    btn.dataset.referralAttached = 'yes';
    
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        var link = generateReferralLink();
        
        // Copy to clipboard
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(link).then(function() {
                showReferralToast(link);
            }).catch(function() {
                fallbackCopy(link);
            });
        } else {
            fallbackCopy(link);
        }
    });
    console.log('Referral button attached');
}

function fallbackCopy(text) {
    var input = document.createElement('textarea');
    input.value = text;
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);
    showReferralToast(text);
}

function showReferralToast(link) {
    // Create toast notification
    var toast = document.createElement('div');
    toast.className = 'referral-toast';
    toast.innerHTML = 
        '<i class="fas fa-check-circle"></i> ' +
        '<div>' +
            '<strong>Link copied!</strong>' +
            '<p>' + link + '</p>' +
        '</div>';
    document.body.appendChild(toast);
    
    setTimeout(function() {
        toast.classList.add('show');
    }, 50);
    
    setTimeout(function() {
        toast.classList.remove('show');
        setTimeout(function() {
            if (toast.parentNode) toast.parentNode.removeChild(toast);
        }, 300);
    }, 3000);
}