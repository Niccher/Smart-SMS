Your Account Has Been Activated

Your Mpesa Analyzer account has been activated. You now have full access to all features.

Activation Details:
- Status: Active
- Activated By: <?= esc($activatedBy ?? 'System Administrator') ?>
- Effective Date: <?= esc($effectiveAt ?? date('Y-m-d H:i:s T')) ?>

You can now log in and use all features of Mpesa Analyzer.

Log In Now: <?= esc(base_url('login')) ?>

If you didn't expect this activation, please contact support.

Email ID: user_activated
Email sent:  <?= $sentAt ?? date('Y-m-d H:i:s T') ?>