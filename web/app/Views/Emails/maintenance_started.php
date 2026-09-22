<h1 style="font-size: 24px; font-weight: 700; color: #ffffff; margin: 0 0 16px; line-height: 1.3; background-color: #4f46e5; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 20px 24px; border-radius: 8px; text-align: center;">Maintenance Mode Started 🔧</h1>
<p class="lead">Mpesa Analyzer is entering scheduled maintenance. The platform will be temporarily unavailable.</p>
<div class="info-box">
    <h3 style="font-size: 14px; font-weight: 600; color: #ffffff; margin: 0 0 16px; text-transform: uppercase; letter-spacing: 0.5px; background-color: #4f46e5; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 16px 20px; border-radius: 8px 8px 0 0;">Maintenance Details</h3>
    <div class="info-row">
        <span class="info-label">Status</span>
        <span class="info-value"><span class="badge badge-warning">Maintenance Active</span></span>
    </div>
    <div class="info-row">
        <span class="info-label">Started At</span>
        <span class="info-value"><?= esc($startedAt ?? date('Y-m-d H:i:s T')) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Estimated End</span>
        <span class="info-value"><?= esc($estimatedEnd ?? 'To be announced') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Message</span>
        <span class="info-value"><?= esc($message ?? 'We are performing scheduled maintenance. Please check back soon.') ?></span>
    </div>
</div>
<p>During this time, you won't be able to log in or access your data. All scheduled jobs are paused.</p>
<p>We'll send another notification when maintenance is complete.</p>
<div class="divider"></div>
<p style="font-size: 14px; color: #6b7280;">Thank you for your patience. We're working to make Mpesa Analyzer better for you.</p>
