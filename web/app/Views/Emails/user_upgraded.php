<h1 style="font-size: 24px; font-weight: 700; color: #ffffff; margin: 0 0 16px; line-height: 1.3; background-color: #4f46e5; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 20px 24px; border-radius: 8px; text-align: center;">Your Role Has Been Upgraded ⬆️</h1>
<p class="lead">Your account permissions have been updated. You now have <strong><?= esc($newRole ?? 'Admin') ?></strong> access.</p>
<div class="info-box">
    <h3 style="font-size: 14px; font-weight: 600; color: #ffffff; margin: 0 0 16px; text-transform: uppercase; letter-spacing: 0.5px; background-color: #4f46e5; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 16px 20px; border-radius: 8px 8px 0 0;">Change Details</h3>
    <div class="info-row">
        <span class="info-label">Previous Role</span>
        <span class="info-value"><span class="badge badge-info"><?= esc($oldRole ?? 'User') ?></span></span>
    </div>
    <div class="info-row">
        <span class="info-label">New Role</span>
        <span class="info-value"><span class="badge badge-success"><?= esc($newRole ?? 'Admin') ?></span></span>
    </div>
    <div class="info-row">
        <span class="info-label">Changed By</span>
        <span class="info-value"><?= esc($changedBy ?? 'System Administrator') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Effective Date</span>
        <span class="info-value"><?= esc($effectiveAt ?? date('Y-m-d H:i:s T')) ?></span>
    </div>
</div>
<p>Your new permissions are active immediately. You may need to refresh your session to see all changes.</p>
<a href="<?= esc(base_url('admin')) ?>" class="button">Open Admin Panel</a>
<div class="divider"></div>
<p style="font-size: 14px; color: #6b7280;">If you believe this change was made in error, please contact your system administrator.</p>
