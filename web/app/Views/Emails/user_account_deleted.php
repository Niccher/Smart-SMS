<h1 style="font-size: 24px; font-weight: 700; color: #ffffff; margin: 0 0 16px; line-height: 1.3; background-color: #4f46e5; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 20px 24px; border-radius: 8px; text-align: center;">Your Account Has Been Deleted 🗑️</h1>
<p class="lead">Your Mpesa Analyzer account and all associated data have been permanently removed.</p>
<div class="info-box">
    <h3 style="font-size: 14px; font-weight: 600; color: #ffffff; margin: 0 0 16px; text-transform: uppercase; letter-spacing: 0.5px; background-color: #4f46e5; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 16px 20px; border-radius: 8px 8px 0 0;">Deletion Summary</h3>
    <div class="info-row">
        <span class="info-label">SMS Messages Removed</span>
        <span class="info-value"><?= esc(number_format($smsDeleted ?? 0)) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Transactions Removed</span>
        <span class="info-value"><?= esc(number_format($transactionsDeleted ?? 0)) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Upload Files Removed</span>
        <span class="info-value"><?= esc(number_format($filesDeleted ?? 0)) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Account Status</span>
        <span class="info-value"><span class="badge badge-danger">Permanently Deleted</span></span>
    </div>
    <div class="info-row">
        <span class="info-label">Deleted At</span>
        <span class="info-value"><?= esc($deletedAt ?? date('Y-m-d H:i:s T')) ?></span>
    </div>
</div>
<p>This action is final and cannot be undone. If you didn't request this deletion, please contact support immediately.</p>
<div class="divider"></div>
<p style="font-size: 14px; color: #6b7280;">Thank you for using Mpesa Analyzer. If you wish to use the service again in the future, you can create a new account at any time.</p>
