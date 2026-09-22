<h1 style="font-size: 24px; font-weight: 700; color: #ffffff; margin: 0 0 16px; line-height: 1.3; background-color: #4f46e5; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 20px 24px; border-radius: 8px; text-align: center;">Backup Received 📥</h1>
<p class="lead">Your M-Pesa backup file has been uploaded successfully and is queued for classification analysis.</p>
<div class="info-box">
    <h3 style="font-size: 14px; font-weight: 600; color: #ffffff; margin: 0 0 16px; text-transform: uppercase; letter-spacing: 0.5px; background-color: #4f46e5; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 16px 20px; border-radius: 8px 8px 0 0;">Upload Details</h3>
    <div class="info-row">
        <span class="info-label">Total Messages</span>
        <span class="info-value"><?= esc(number_format($totalMessages ?? 0)) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Financial Senders</span>
        <span class="info-value"><?= esc(number_format($financialSenders ?? 0)) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Inflow Volume</span>
        <span class="info-value">Ksh <?= esc(number_format($inflowAmount ?? 0.0, 2)) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Outflow Volume</span>
        <span class="info-value">Ksh <?= esc(number_format($outflowAmount ?? 0.0, 2)) ?></span>
    </div>
</div>
<p>We are running our machine learning analysis on these transactions to automatically sort them into categories like shopping, transfers, utility bills, and loans. We will notify you once classification is complete.</p>
<a href="<?= esc(base_url('history')) ?>" class="button">Track Progress</a>
<div class="divider"></div>
<p style="font-size: 14px; color: #6b7280;">If you did not upload this backup file, please secure your account immediately.</p>
