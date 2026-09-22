<h1 style="font-size: 24px; font-weight: 700; color: #ffffff; margin: 0 0 16px; line-height: 1.3; background-color: #4f46e5; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 20px 24px; border-radius: 8px; text-align: center;">Maintenance Complete ✅</h1>
<p class="lead">Mpesa Analyzer maintenance has finished. The platform is now fully operational.</p>
<div class="info-box">
    <h3 style="font-size: 14px; font-weight: 600; color: #ffffff; margin: 0 0 16px; text-transform: uppercase; letter-spacing: 0.5px; background-color: #4f46e5; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 16px 20px; border-radius: 8px 8px 0 0;">Maintenance Summary</h3>
    <div class="info-row">
        <span class="info-label">Status</span>
        <span class="info-value"><span class="badge badge-success">Normal Operation</span></span>
    </div>
    <div class="info-row">
        <span class="info-label">Started At</span>
        <span class="info-value"><?= esc($startedAt ?? 'N/A') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Ended At</span>
        <span class="info-value"><?= esc($endedAt ?? date('Y-m-d H:i:s T')) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Duration</span>
        <span class="info-value"><?= esc($duration ?? 'N/A') ?></span>
    </div>
</div>
<p>All features are now available. You can log in and continue using Mpesa Analyzer normally.</p>
<a href="<?= esc(base_url('login')) ?>" class="button">Log In Now</a>
<div class="divider"></div>
<p style="font-size: 14px; color: #6b7280;">Scheduled jobs have resumed. If you experience any issues, please contact support.</p>
