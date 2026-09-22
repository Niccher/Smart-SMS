<h1 style="font-size: 24px; font-weight: 700; color: #ffffff; margin: 0 0 16px; line-height: 1.3; background-color: #4f46e5; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 20px 24px; border-radius: 8px; text-align: center;">Your Account Has Been Deactivated ⚠️</h1>
<p class="lead">Your Mpesa Analyzer account has been deactivated. You will not be able to log in or access any features until reactivated.</p>
<div class="info-box">
    <h3 style="font-size: 14px; font-weight: 600; color: #ffffff; margin: 0 0 16px; text-transform: uppercase; letter-spacing: 0.5px; background-color: #4f46e5; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 16px 20px; border-radius: 8px 8px 0 0;">Deactivation Details</h3>
    <div class="info-row">
        <span class="info-label">Status</span>
        <span class="info-value"><span class="badge badge-danger">Deactivated</span></span>
    </div>
    <div class="info-row">
        <span class="info-label">Deactivated By</span>
        <span class="info-value"><?= esc($deactivatedBy ?? 'System Administrator') ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Effective Date</span>
        <span class="info-value"><?= esc($effectiveAt ?? date('Y-m-d H:i:s T')) ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Reason</span>
        <span class="info-value"><?= esc($reason ?? 'Not specified') ?></span>
    </div>
</div>
<p>Your data remains intact. Contact your administrator if you believe this was done in error or to request reactivation.</p>
<div class="divider"></div>
<p style="font-size: 14px; color: #6b7280;">For questions about this deactivation, please contact your system administrator.</p>
