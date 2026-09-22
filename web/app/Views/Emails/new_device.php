<h1 style="font-size: 24px; font-weight: 700; color: #ffffff; margin: 0 0 16px; line-height: 1.3; background-color: #4f46e5; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 20px 24px; border-radius: 8px; text-align: center;">New Device Connected 📱</h1>
<p class="lead">A new device was linked to your Mpesa Analyzer account.</p>
<div class="info-box">
    <h3 style="font-size: 14px; font-weight: 600; color: #ffffff; margin: 0 0 16px; text-transform: uppercase; letter-spacing: 0.5px; background-color: #4f46e5; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 16px 20px; border-radius: 8px 8px 0 0;">Device Details</h3>
    <div class="info-row">
        <span class="info-label">Device Name</span>
        <span class="info-value"><?= esc($deviceName ?? 'Unknown Device') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Device Type</span>
        <span class="info-value"><?= esc($deviceType ?? 'Mobile') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Platform</span>
        <span class="info-value"><?= esc($platform ?? 'Unknown') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">IP Address</span>
        <span class="info-value"><?= esc($ipAddress ?? 'Unknown') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Location (approx.)</span>
        <span class="info-value"><?= esc($location ?? 'Unknown') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Connected At</span>
        <span class="info-value"><?= esc($connectedAt ?? date('Y-m-d H:i:s T')) ?></span>
    </div>
</div>
<p>If this was you, no action is needed. If you don't recognize this device, please secure your account immediately.</p>
<a href="<?= esc(base_url('settings/security')) ?>" class="button">Review Devices</a>
<div class="divider"></div>
<p style="font-size: 14px; color: #6b7280;">You can manage all connected devices from your <a href="<?= esc(base_url('settings/security')) ?>">Security Settings</a>.</p>
