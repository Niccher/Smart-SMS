Your Role Has Been Changed

Your account permissions have been updated. Your role is now <?= esc($newRole ?? 'User') ?>.

Change Details:
- Previous Role: <?= esc($oldRole ?? 'Admin') ?>
- New Role: <?= esc($newRole ?? 'User') ?>
- Changed By: <?= esc($changedBy ?? 'System Administrator') ?>
- Effective Date: <?= esc($effectiveAt ?? date('Y-m-d H:i:s T')) ?>

Some admin features may no longer be accessible. Your regular user features remain unchanged.

Go to Dashboard: <?= esc(base_url('dashboard')) ?>

If you believe this change was made in error, please contact your system administrator.

Email ID: user_downgraded
Email sent:  <?= $sentAt ?? date('Y-m-d H:i:s T') ?>