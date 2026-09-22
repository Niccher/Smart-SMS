Your Magic Login Link

Click the link below to securely log in to your Mpesa Analyzer account. This link is unique to you and expires in 15 minutes.

<?= esc($magicLink ?? '#') ?>

Login Details:
- IP Address: <?= esc($ipAddress ?? 'Unknown') ?>
- Device: <?= esc($userAgent ?? 'Unknown') ?>
- Date & Time: <?= esc($date ?? date('Y-m-d H:i:s T')) ?>

If you didn't request this login link, please ignore this email. Your account remains secure.

Email ID: password_reset
Email sent:  <?= $sentAt ?? date('Y-m-d H:i:s T') ?>