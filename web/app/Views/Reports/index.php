<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Monthly Reports & Insights - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .report-hero { background: linear-gradient(135deg, #438EB9 0%, #2B7DBC 100%); border-radius: 4px; color: white; padding: 32px 40px; margin-bottom: 28px; position: relative; overflow: hidden; }
    .report-hero::after { content: ''; position: absolute; top: -60%; right: -5%; width: 280px; height: 280px; background: rgba(255,255,255,0.08); border-radius: 50%; }
    .glass-card { background: var(--card-bg); backdrop-filter: blur(10px); border: 1px solid var(--card-border); border-radius: 4px; box-shadow: 0 6px 24px rgba(31,38,135,0.06); transition: transform .2s, box-shadow .2s; }
    .glass-card:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(31,38,135,0.1); }
    .metric-icon { width: 44px; height: 44px; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 12px; }
    select.period-select { border-radius: 4px; border: 1px solid var(--card-border); padding: 8px 16px; font-weight: 600; color: var(--primary); background: var(--bg-color); cursor: pointer; }
    .trend-badge { padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 0.8rem; }
    .trend-up { background: rgba(255,71,87,0.15); color: #FF4757; }
    .trend-down { background: rgba(46,213,115,0.15); color: #2ED573; }
    .trend-flat { background: rgba(160,160,160,0.15); color: #888; }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="report-hero shadow-sm">
    <div class="row align-items-center">
        <div class="col-md-7">
            <p class="mb-1 opacity-75 small fw-bold text-uppercase">Financial Report &amp; Insights</p>
            <h1 class="fw-bold mb-1 fs-3"><?= $report['month_name'] ?> <?= $report['year'] ?></h1>
            <p class="opacity-75 mb-0"><?= number_format($report['tx_count']) ?> transactions analysed this month</p>
        </div>
        <div class="col-md-5 d-flex justify-content-md-end align-items-center gap-3 mt-3 mt-md-0 position-relative" style="z-index: 2;">
            <form method="GET" action="<?= base_url('dashboard/reports') ?>" class="d-flex align-items-center gap-2">
                <select name="month_key" id="monthPicker" class="period-select" onchange="applyMonth(this.value)">
                    <?php foreach ($months as $m): ?>
                        <option value="<?= $m['y'] ?>-<?= $m['m'] ?>" <?= $selectedKey === $m['value'] ? 'selected' : '' ?>>
                            <?= $m['label'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="year" id="hidYear" value="<?= $report['year'] ?>">
                <input type="hidden" name="month" id="hidMonth" value="<?= $report['month'] ?>">
            </form>
            <a href="<?= base_url('dashboard/reports/print?year='.$report['year'].'&month='.$report['month']) ?>" 
               target="_blank" class="btn btn-light fw-bold rounded-pill px-4">
                <i class="fa-solid fa-print me-2"></i>Export PDF
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php $cs = currency_symbol(); ?>

<!-- Summary Cards & Overall Trends -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="glass-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <p class="text-secondary fw-bold small mb-0">Total Outflow</p>
                <div class="metric-icon bg-danger-subtle text-danger mb-0"><i class="fa-solid fa-arrow-up-long"></i></div>
            </div>
            <h3 class="fw-bold mb-1 text-danger"><?= $cs ?> <?= number_format($report['total_out'], 2) ?></h3>
            <?php 
            $trendClass = $trends['trend'] === 'down' ? 'trend-down' : ($trends['trend'] === 'up' ? 'trend-up' : 'trend-flat');
            $trendIcon  = $trends['trend'] === 'down' ? '↓' : ($trends['trend'] === 'up' ? '↑' : '-');
            ?>
            <span class="trend-badge <?= $trendClass ?> px-2 py-0" style="font-size: 0.7rem;"><?= $trendIcon ?> <?= $trends['percentage'] ?>% vs Last Mo.</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="glass-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <p class="text-secondary fw-bold small mb-0">Total Inflow</p>
                <div class="metric-icon bg-success-subtle text-success mb-0"><i class="fa-solid fa-arrow-down-long"></i></div>
            </div>
            <h3 class="fw-bold mb-0 text-success"><?= $cs ?> <?= number_format($report['total_in'], 2) ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="glass-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <p class="text-secondary fw-bold small mb-0">Net Balance</p>
                <div class="metric-icon <?= $report['net'] >= 0 ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning' ?> mb-0">
                    <i class="fa-solid fa-scale-balanced"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-0 <?= $report['net'] >= 0 ? 'text-primary' : 'text-warning' ?>">
                <?= $report['net'] >= 0 ? '+' : '' ?><?= $cs ?> <?= number_format($report['net'], 2) ?>
            </h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="glass-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <p class="text-secondary fw-bold small mb-0">Transactions</p>
                <div class="metric-icon bg-info-subtle text-info mb-0"><i class="fa-solid fa-receipt"></i></div>
            </div>
            <h3 class="fw-bold mb-0 text-info"><?= number_format($report['tx_count']) ?></h3>
        </div>
    </div>
</div>

<!-- Rich LLM Insights (Fees & Loan Metrics) -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="glass-card card p-3 h-100 border-0 border-start border-4 border-warning shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-secondary fw-bold mb-1" style="font-size:0.85rem;">Monthly Transaction Fees</h6>
                    <h3 class="fw-bold mb-0 text-warning"><?= $cs ?> <?= number_format($report['total_fees'] ?? 0, 2) ?></h3>
                    <p class="small text-muted mb-0 mt-1" style="font-size:0.75rem;">Excise duties &amp; carrier transaction charges parsed by AI</p>
                </div>
                <div class="insight-icon bg-warning-subtle text-warning mb-0 fs-3 p-2 rounded-3">
                    <i class="fa-solid fa-receipt"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="glass-card card p-3 h-100 border-0 border-start border-4 border-info shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-secondary fw-bold mb-1" style="font-size:0.85rem;">Disbursed Loans Received</h6>
                    <h3 class="fw-bold mb-0 text-info"><?= $cs ?> <?= number_format($report['total_loans'] ?? 0, 2) ?></h3>
                    <p class="small text-muted mb-0 mt-1" style="font-size:0.75rem;">Fuliza, M-Shwari, and mobile overdraft disbursements</p>
                </div>
                <div class="insight-icon bg-info-subtle text-info mb-0 fs-3 p-2 rounded-3">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Daily Cash Flow Graph -->
    <div class="col-lg-7">
        <div class="glass-card p-4 h-100">
            <h5 class="fw-bold mb-4"><i class="fa-solid fa-chart-column me-2 text-primary"></i>Daily Cash Flow</h5>
            <div style="position: relative; height: 320px;">
                <canvas id="dailyFlowChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Category Spending Split -->
    <div class="col-lg-5">
        <div class="glass-card p-4 h-100">
            <h5 class="fw-bold mb-4"><i class="fa-solid fa-chart-pie me-2 text-primary"></i>LLM Spending Split</h5>
            <div style="position: relative; height:200px;" class="mb-3">
                <canvas id="catDonut"></canvas>
            </div>
            <div style="max-height: 200px; overflow-y: auto;">
                <?php
                $catColors = ['#438EB9', '#2ED573', '#FFA502', '#FF4757', '#1E90FF', '#a29bfe', '#fd79a8', '#00cec9', '#d63031', '#e84393', '#ffeaa7'];
                $idx = 0;
                $catSum = array_sum($report['categories'] ?? []) ?: 1;
                foreach ($report['categories'] as $name => $val):
                    if ($val == 0) continue;
                    $percent = round(($val / $catSum) * 100);
                    $color = $catColors[$idx % count($catColors)];
                ?>
                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom border-light">
                    <div class="d-flex align-items-center gap-2">
                        <span style="width:10px;height:10px;background:<?= $color ?>;border-radius:50%;"></span>
                        <span class="small fw-semibold text-dark"><?= htmlspecialchars($name) ?></span>
                    </div>
                    <div class="text-end">
                        <span class="small fw-bold text-dark"><?= $cs ?> <?= number_format($val, 0) ?></span>
                        <span class="text-secondary small ms-1" style="font-size: 0.75rem;"><?= $percent ?>%</span>
                    </div>
                </div>
                <?php $idx++; endforeach; ?>
                <?php if ($idx === 0): ?>
                    <p class="text-muted small text-center py-4">No category data captured.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- AI Subscriptions/Recurring Detector -->
<?php if (!empty($recurring)): ?>
<div class="glass-card p-4 mb-4 border-start border-4 border-primary">
    <div class="d-flex align-items-center gap-3 mb-4">
        <div class="insight-icon bg-primary-subtle text-primary mb-0"><i class="fa-solid fa-clock-rotate-left text-primary"></i></div>
        <div>
            <h5 class="fw-bold mb-0">Detected Recurring Payments</h5>
            <small class="text-muted">AI identified identical transactions occurring regularly over the last 6 months.</small>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="text-muted small text-uppercase">
                <tr><th>Counterparty</th><th>Consistent Amount</th><th>Occurrences</th><th>Last Paid</th></tr>
            </thead>
            <tbody>
                <?php foreach ($recurring as $rec): ?>
                <tr>
                    <td class="fw-bold small text-dark"><?= htmlspecialchars($rec->counterparty) ?></td>
                    <td class="text-danger fw-bold small"><?= $cs ?> <?= number_format($rec->amount, 2) ?></td>
                    <td><span class="badge bg-light text-dark border small"><?= $rec->occurs ?> times</span></td>
                    <td class="text-muted timestamp small" data-timestamp="<?= strtotime($rec->last_paid) ?>">
                        <?= date('d M Y', strtotime($rec->last_paid)) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Top Counterparties -->
<?php if (!empty($report['top_counterparties'])): ?>
<div class="glass-card p-4">
    <h5 class="fw-bold mb-4"><i class="fa-solid fa-users me-2 text-primary"></i>Top Spending Counterparties (This Month)</h5>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">
                    <th>Entity Partner</th>
                    <th class="text-center">Transactions</th>
                    <th class="text-end">Total Spent</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $colors2 = ['#438EB9','#2ED573','#FFA502','#FF4757','#1E90FF'];
                foreach ($report['top_counterparties'] as $i => $cp):
                    $c = $colors2[$i % 5];
                ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width:36px;height:36px;background:<?= $c ?>15; border:1px solid <?= $c ?>30;">
                                <i class="fa-solid <?= strpos(strtolower($cp->counterparty), 'bank') !== false ? 'fa-building-columns' : 'fa-user' ?>" style="color:<?= $c ?>;"></i>
                            </div>
                            <span class="fw-bold small text-dark"><?= htmlspecialchars($cp->counterparty) ?></span>
                        </div>
                    </td>
                    <td class="text-center small fw-semibold text-secondary">
                        <?= $cp->trans_count ?> transactions
                    </td>
                    <td class="text-end fw-bold text-dark">
                        <?= $cs ?> <?= number_format($cp->total_amount, 2) ?>
                    </td>
                    <td class="text-end">
                        <a href="<?= base_url('dashboard/transactions?search=' . urlencode($cp->counterparty)) ?>" 
                           class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1 fw-bold" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> Trace
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function applyMonth(val) {
    const [y, m] = val.split('-');
    document.getElementById('hidYear').value = y;
    document.getElementById('hidMonth').value = m;
    document.querySelector('form').submit();
}

Chart.defaults.font.family = "'Outfit', sans-serif";

new Chart(document.getElementById('dailyFlowChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($report['labels']) ?>,
        datasets: [{ label: 'Inflow', data: <?= json_encode($report['inflow']) ?>, backgroundColor: 'rgba(46,213,115,0.75)', borderRadius: 4, stack: '0' },
                   { label: 'Outflow', data: <?= json_encode($report['outflow']) ?>, backgroundColor: 'rgba(255,71,87,0.75)', borderRadius: 4, stack: '1' }]
    },
    options: { 
        responsive: true, 
        maintainAspectRatio: false, 
        plugins: { tooltip: { intersect: false, mode: 'index' } }, 
        scales: { 
            x: { grid: { display: false } }, 
            y: { beginAtZero: true, grid: { borderDash: [5,5], color: 'rgba(0,0,0,0.03)' } } 
        } 
    }
});

const catData = <?= json_encode(array_values(array_filter($report['categories'] ?? []))) ?>;
const catLabels = <?= json_encode(array_keys(array_filter($report['categories'] ?? []))) ?>;
const paletteColors = ['#438EB9', '#2ED573', '#FFA502', '#FF4757', '#1E90FF', '#a29bfe', '#fd79a8', '#00cec9', '#d63031', '#e84393', '#ffeaa7'];

if (catData.length > 0) {
    new Chart(document.getElementById('catDonut'), {
        type: 'doughnut',
        data: {
            labels: catLabels,
            datasets: [{ data: catData, backgroundColor: paletteColors, borderWidth: 0, hoverOffset: 8 }]
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { display: false } } }
    });
} else {
    document.getElementById('catDonut').parentElement.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-muted small">No data available</div>';
}

// Relative timestamps
document.querySelectorAll('.timestamp').forEach(el => {
    const ts = parseInt(el.dataset.timestamp) * 1000;
    const diffDays = Math.floor((Date.now() - ts) / (1000 * 60 * 60 * 24));
    if(diffDays === 0) el.innerHTML += ' (Today)';
    else if(diffDays === 1) el.innerHTML += ' (Yesterday)';
    else el.innerHTML += ` (${diffDays} days ago)`;
});
</script>
<?= $this->endSection() ?>
