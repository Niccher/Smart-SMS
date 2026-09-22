M-Pesa Backup Backup Received

Your M-Pesa backup file has been uploaded successfully and is queued for classification analysis.

Upload Details:
- Total Messages: <?= esc(number_format($totalMessages ?? 0)) ?>
- Financial Senders: <?= esc(number_format($financialSenders ?? 0)) ?>
- Inflow Volume: Ksh <?= esc(number_format($inflowAmount ?? 0.0, 2)) ?>
- Outflow Volume: Ksh <?= esc(number_format($outflowAmount ?? 0.0, 2)) ?>

We are running our machine learning analysis on these transactions to automatically sort them into categories like shopping, transfers, utility bills, and loans. We will notify you once classification is complete.

Track Progress: <?= esc(base_url('history')) ?>

If you did not upload this backup file, please secure your account immediately.

Email ID: loot_uploaded
Email sent: <?= $sentAt ?? date('Y-m-d H:i:s T') ?>
