<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>M-Pesa Report — <?= $report['month_name'] ?> <?= $report['year'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Outfit', sans-serif; background: #fff; color: #1a1a2e; padding: 40px; font-size: 14px; }
        .header { border-bottom: 3px solid #438EB9; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 30px; }
        .card { border: 1px solid #e0e0e0; border-radius: 4px; padding: 18px; }
        .inflow { color: #2ED573; } .outflow { color: #FF4757; } .net { color: #438EB9; }
        .chart-wrap { height: 240px; margin-bottom: 30px; }
        .cat-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f5f5f5; }
        .print-btn { position: fixed; top: 20px; right: 20px; background: #438EB9; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; cursor: pointer; font-weight: 600; }
        @media print { .print-btn { display: none; } }
    </style>
</head>
<body>
<button class="print-btn" onclick="window.print()">Print / Save PDF</button>

<?php $cs = currency_symbol(); ?>

<div class="header">
    <div>
        <h1 style="color:#438EB9">M-Pesa Monthly Report</h1>
        <p style="color:#888"><?= $report['month_name'] ?> <?= $report['year'] ?> &nbsp;·&nbsp; Gen: <?= date('Y-m-d') ?></p>
    </div>
</div>

<div class="grid-4">
    <div class="card"><label>Total Inflow</label><div class="inflow" style="font-size:1.4rem;font-weight:700"><?= $cs ?> <?= number_format($report['total_in'], 2) ?></div></div>
    <div class="card"><label>Total Outflow</label><div class="outflow" style="font-size:1.4rem;font-weight:700"><?= $cs ?> <?= number_format($report['total_out'], 2) ?></div></div>
    <div class="card"><label>Net Balance</label><div class="net" style="font-size:1.4rem;font-weight:700"><?= $report['net'] >= 0 ? '+' : '' ?><?= $cs ?> <?= number_format($report['net'], 2) ?></div></div>
    <div class="card"><label>Transactions</label><div style="font-size:1.4rem;font-weight:700"><?= number_format($report['tx_count']) ?></div></div>
</div>

<h4 style="margin-bottom:10px">Daily Cash Flow</h4>
<div class="chart-wrap"><canvas id="printChart"></canvas></div>

<h4 style="margin-bottom:10px">Category Breakdown</h4>
<?php foreach ($report['categories'] as $name => $val): if ($val > 0): ?>
    <div class="cat-row"><span><?= $name ?></span><strong><?= $cs ?> <?= number_format($val, 2) ?></strong></div>
<?php endif; endforeach; ?>

<?php if (!empty($report['top_counterparties'])): ?>
<br><h4 style="margin-bottom:10px">Top Counterparties</h4>
<?php foreach ($report['top_counterparties'] as $cp): ?>
<div class="cat-row"><span><?= htmlspecialchars($cp->counterparty) ?></span><strong><?= $cs ?> <?= number_format($cp->total_amount, 2) ?></strong></div>
<?php endforeach; endif; ?>

<script>
new Chart(document.getElementById('printChart'), {
    type: 'bar',
    data: { labels: <?= json_encode($report['labels']) ?>, datasets: [ { label: 'Inflow', data: <?= json_encode($report['inflow']) ?>, backgroundColor: '#2ED573' }, { label: 'Outflow', data: <?= json_encode($report['outflow']) ?>, backgroundColor: '#FF4757' } ] },
    options: { responsive: true, maintainAspectRatio: false, scales: { x: { grid: { display: false } }, y: { beginAtZero: true } } }
});
</script>
</body>
</html>
