Your Role Has Been Upgraded

Your account permissions have been updated. You now have <?= esc($newRole ?? 'Admin') ?> access.

Change Details:
- Previous Role: <?= esc($oldRole ?? 'User') ?>
- New Role: <?= esc($newRole ?? 'Admin') ?>
- Changed By: <?= esc($changedBy ?? 'System Administrator') ?>
- Effective Date: <?= esc($effectiveAt ?? date('Y-m-d H:i:s T')) ?>

Your new permissions are active immediately. You may need to refresh your session to see all changes.

Open Admin Panel: <?= esc(base_url('admin')) ?>

If you believe this change was made in error, please contact your system administrator.

Email ID: user_upgraded
Email sent:  <?= $sentAt ?? date('Y-m-d H:i:s T') ?>