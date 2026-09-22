<h1 style="font-size: 24px; font-weight: 700; color: #ffffff; margin: 0 0 16px; line-height: 1.3; background-color: #4f46e5; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 20px 24px; border-radius: 8px; text-align: center;">Your <?= esc(ucfirst($frequency ?? 'Monthly')) ?> Spending Report 📊</h1>
<p class="lead">Your scheduled Mpesa Analyzer report for <strong><?= esc($period ?? 'this period') ?></strong> is ready.</p>
<p>Here's a quick summary:</p>
<div class="info-box">
    <h3 style="font-size: 14px; font-weight: 600; color: #ffffff; margin: 0 0 16px; text-transform: uppercase; letter-spacing: 0.5px; background-color: #4f46e5; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 16px 20px; border-radius: 8px 8px 0 0;">Report Summary</h3>
    <div class="info-row">
        <span class="info-label">Period</span>
        <span class="info-value"><?= esc($period ?? 'N/A') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Frequency</span>
        <span class="info-value"><span class="badge badge-info"><?= esc(ucfirst($frequency ?? 'monthly')) ?></span></span>
    </div>
    <div class="info-row">
        <span class="info-label">Total Transactions</span>
        <span class="info-value"><?= esc(number_format($totalTransactions ?? 0)) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Total Spent</span>
        <span class="info-value">KES <?= esc(number_format($totalSpent ?? 0, 2)) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Top Category</span>
        <span class="info-value"><?= esc($topCategory ?? 'N/A') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Average Daily Spend</span>
        <span class="info-value">KES <?= esc(number_format($avgDaily ?? 0, 2)) ?></span>
    </div>
</div>
<a href="<?= esc(base_url('reports')) ?>" class="button">View Full Report</a>
<div class="divider"></div>
<p style="font-size: 14px; color: #6b7280;">You can adjust your report frequency or disable scheduled reports in <a href="<?= esc(base_url('settings/notifications')) ?>">Email Preferences</a>.</p>
