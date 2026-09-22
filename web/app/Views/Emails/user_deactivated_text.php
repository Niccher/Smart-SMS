Your Account Has Been Deactivated

Your Mpesa Analyzer account has been deactivated. You will not be able to log in or access any features until reactivated.

Deactivation Details:
- Status: Deactivated
- Deactivated By: <?= esc($deactivatedBy ?? 'System Administrator') ?>
- Effective Date: <?= esc($effectiveAt ?? date('Y-m-d H:i:s T')) ?>
- Reason: <?= esc($reason ?? 'Not specified') ?>

Your data remains intact. Contact your administrator if you believe this was done in error or to request reactivation.

For questions about this deactivation, please contact your system administrator.

Email ID: user_deactivated
Email sent:  <?= $sentAt ?? date('Y-m-d H:i:s T') ?>