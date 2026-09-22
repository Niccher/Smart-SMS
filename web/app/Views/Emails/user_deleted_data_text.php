Your Data Has Been Deleted

As requested, we have permanently deleted your M-Pesa transaction data from Mpesa Analyzer.

Deletion Summary:
- SMS Messages Deleted: <?= esc(number_format($smsDeleted ?? 0)) ?>
- Transactions Removed: <?= esc(number_format($transactionsDeleted ?? 0)) ?>
- Upload Files Removed: <?= esc(number_format($filesDeleted ?? 0)) ?>
- Account Status: Preserved
- Deleted At: <?= esc($deletedAt ?? date('Y-m-d H:i:s T')) ?>

Your account remains active. You can upload new statements at any time to start fresh analysis.

Go to Dashboard: <?= esc(base_url('dashboard')) ?>

This action cannot be undone. If you didn't request this deletion, please contact support immediately.

Email ID: user_deleted_data
Email sent:  <?= $sentAt ?? date('Y-m-d H:i:s T') ?>