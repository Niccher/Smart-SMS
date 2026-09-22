<h1 style="font-size: 24px; font-weight: 700; color: #ffffff; margin: 0 0 16px; line-height: 1.3; background-color: #4f46e5; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 20px 24px; border-radius: 8px; text-align: center;">Your Magic Login Link 🔐</h1>
<p class="lead">Click the button below to securely log in to your Mpesa Analyzer account. This link is unique to you and expires in <strong>15 minutes</strong>.</p>
<a href="<?= esc($magicLink ?? '#') ?>" class="button">Log In Securely</a>
<div class="divider"></div>
<div class="info-box">
    <h3 style="font-size: 14px; font-weight: 600; color: #ffffff; margin: 0 0 16px; text-transform: uppercase; letter-spacing: 0.5px; background-color: #4f46e5; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 16px 20px; border-radius: 8px 8px 0 0;">Login Details</h3>
    <div class="info-row">
        <span class="info-label">IP Address</span>
        <span class="info-value"><?= esc($ipAddress ?? 'Unknown') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Device</span>
        <span class="info-value"><?= esc($userAgent ?? 'Unknown') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Date & Time</span>
        <span class="info-value"><?= esc($date ?? date('Y-m-d H:i:s T')) ?></span>
    </div>
</div>
<p style="font-size: 14px; color: #6b7280;">If you didn't request this login link, please ignore this email. Your account remains secure.</p>
