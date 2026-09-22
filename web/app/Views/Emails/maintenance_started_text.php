Maintenance Mode Started

Mpesa Analyzer is entering scheduled maintenance. The platform will be temporarily unavailable.

Maintenance Details:
- Status: Maintenance Active
- Started At: <?= esc($startedAt ?? date('Y-m-d H:i:s T')) ?>
- Estimated End: <?= esc($estimatedEnd ?? 'To be announced') ?>
- Message: <?= esc($message ?? 'We are performing scheduled maintenance. Please check back soon.') ?>

During this time, you won't be able to log in or access your data. All scheduled jobs are paused.

We'll send another notification when maintenance is complete.

Thank you for your patience. We're working to make Mpesa Analyzer better for you.

Email ID: maintenance_started
Email sent:  <?= $sentAt ?? date('Y-m-d H:i:s T') ?>