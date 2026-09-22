Your <?= esc(ucfirst($frequency ?? 'Monthly')) ?> Spending Report

Your scheduled Mpesa Analyzer report for <?= esc($period ?? 'this period') ?> is ready.

Report Summary:
- Period: <?= esc($period ?? 'N/A') ?>
- Frequency: <?= esc(ucfirst($frequency ?? 'monthly')) ?>
- Total Transactions: <?= esc(number_format($totalTransactions ?? 0)) ?>
- Total Spent: KES <?= esc(number_format($totalSpent ?? 0, 2)) ?>
- Top Category: <?= esc($topCategory ?? 'N/A') ?>
- Average Daily Spend: KES <?= esc(number_format($avgDaily ?? 0, 2)) ?>

View Full Report: <?= esc(base_url('reports')) ?>

You can adjust your report frequency or disable scheduled reports in Email Preferences.

Email ID: report
Email sent:  <?= $sentAt ?? date('Y-m-d H:i:s T') ?>