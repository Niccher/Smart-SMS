<h1 style="font-size: 24px; font-weight: 700; color: #ffffff; margin: 0 0 16px; line-height: 1.3; background-color: #4f46e5; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 20px 24px; border-radius: 8px; text-align: center;">Your Data Has Been Deleted 🗑️</h1>
<p class="lead">As requested, we have permanently deleted your M-Pesa transaction data from Mpesa Analyzer.</p>
<div class="info-box">
    <h3 style="font-size: 14px; font-weight: 600; color: #ffffff; margin: 0 0 16px; text-transform: uppercase; letter-spacing: 0.5px; background-color: #4f46e5; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 16px 20px; border-radius: 8px 8px 0 0;">Deletion Summary</h3>
    <div class="info-row">
        <span class="info-label">SMS Messages Deleted</span>
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
        <span class="info-label">Orphaned Records Removed</span>
        <span class="info-value"><?= esc(number_format($orphanSmsDeleted ?? 0)) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Account Status</span>
        <span class="info-value"><span class="badge badge-warning">Preserved</span></span>
    </div>
    <div class="info-row">
        <span class="info-label">Deleted At</span>
        <span class="info-value"><?= esc($deletedAt ?? date('Y-m-d H:i:s T')) ?></span>
    </div>
</div>
<p>Your account remains active. You can upload new statements at any time to start fresh analysis.</p>
<a href="<?= esc(base_url('dashboard')) ?>" class="button">Go to Dashboard</a>
<div class="divider"></div>
<p style="font-size: 14px; color: #6b7280;">This action cannot be undone. If you didn't request this deletion, please contact support immediately.</p>
