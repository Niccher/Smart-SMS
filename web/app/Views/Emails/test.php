<h1 style="font-size: 24px; font-weight: 700; color: #ffffff; margin: 0 0 16px; line-height: 1.3; background-color: #4f46e5; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 20px 24px; border-radius: 8px; text-align: center;">✅ Email Settings Verified!</h1>
<p class="lead">Your SMTP configuration is working correctly. This email uses the same styled template system as all other Mpesa Analyzer notifications.</p>
<div class="info-box">
    <h3 style="font-size: 14px; font-weight: 600; color: #ffffff; margin: 0 0 16px; text-transform: uppercase; letter-spacing: 0.5px; background-color: #4f46e5; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 16px 20px; border-radius: 8px 8px 0 0;">Email Details</h3>
    <div class="info-row">
        <span class="info-label">Recipient</span>
        <span class="info-value"><?= esc($to ?? '') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">SMTP Host</span>
        <span class="info-value"><?= esc($smtpHost ?? 'smtp.gmail.com') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Port</span>
        <span class="info-value"><?= esc($smtpPort ?? 587) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Encryption</span>
        <span class="info-value"><?= esc(ucfirst($smtpCrypto ?? 'tls')) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">From Address</span>
        <span class="info-value"><?= esc($fromEmail ?? '') ?></span>
    </div>
</div>
<a href="<?= esc(base_url('admin/notifications')) ?>" class="button">Back to Notifications</a>
<div class="divider"></div>
<p style="font-size: 14px; color: #6b7280;">Sent from the Mpesa Analyzer admin panel. All email notifications — including signup, password resets, spending reports, and system alerts — use this same styled template system.</p>
