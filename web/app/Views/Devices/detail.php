<?= $this->extend('Layouts/admin') ?>
<?= $this->section('title') ?> Device Details - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .stat-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .stat-card .stat-icon { width: 44px; height: 44px; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    .kv { font-size: 0.85rem; }
    .kv td:first-child { color: #6c757d; width: 40%; }
    .kv td:last-child { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 0.78rem; word-break: break-all; }
    .kv .fp-icon { width: 18px; color: var(--primary); margin-right: 6px; }
    .metric {
        display: flex; align-items: flex-start; gap: 0.85rem;
        padding: 1rem; height: 100%;
        background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 4px;
    }
    .metric-icon {
        width: 46px; height: 46px; border-radius: 4px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
        background: rgba(93, 95, 239, 0.12); color: var(--primary);
    }
    .metric-value { font-weight: 700; font-size: 1.4rem; line-height: 1.2; }
    .metric-label { font-size: 0.78rem; font-weight: 700; color: var(--text-main); }
    .metric-desc { font-size: 0.75rem; color: var(--text-muted); }
    [data-bs-theme="dark"] .stat-card { box-shadow: 0 4px 20px rgba(0,0,0,0.25); }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-mobile-screen-button me-2"></i> Device Details</h2>
        <p class="text-secondary mb-0"><span class="fingerprint"><?= esc($metrics['device']->device_Uuid ?? '') ?></span></p>
    </div>
    <a class="btn btn-outline-secondary btn-sm" href="<?= base_url('dashboard/devices') ?>">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Devices
    </a>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="metric">
            <span class="metric-icon"><i class="fa-solid fa-cloud-arrow-up"></i></span>
            <div class="min-w-0">
                <div class="metric-value" style="color: var(--primary);"><?= number_format($metrics['upload_count']) ?></div>
                <div class="metric-label">Uploads</div>
                <div class="metric-desc">Batches synced to server</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metric">
            <span class="metric-icon" style="color:#2ED573; background:rgba(46,213,115,0.12);"><i class="fa-solid fa-envelope"></i></span>
            <div class="min-w-0">
                <div class="metric-value text-success"><?= number_format($metrics['sms_total']) ?></div>
                <div class="metric-label">SMS Records</div>
                <div class="metric-desc">Messages uploaded</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metric">
            <span class="metric-icon" style="color:#1e90ff; background:rgba(30,144,255,0.12);"><i class="fa-solid fa-bolt"></i></span>
            <div class="min-w-0">
                <div class="metric-value" style="color:#1e90ff;"><?= number_format($metrics['activity_count']) ?></div>
                <div class="metric-label">API Calls</div>
                <div class="metric-desc">Endpoint hits recorded</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metric">
            <span class="metric-icon" style="color:#FFA502; background:rgba(255,165,2,0.12);"><i class="fa-solid fa-key"></i></span>
            <div class="min-w-0">
                <div class="metric-value" style="color:#FFA502;"><?= count($metrics['tokens']) ?></div>
                <div class="metric-label">Tokens Used</div>
                <div class="metric-desc">Linked access tokens</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card stat-card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-circle-info me-2"></i> Device Fingerprint</h5>
                <table class="table table-sm kv mb-0">
                    <?php $dev = $metrics['device']; ?>
                    <?php if ($dev): ?>
                    <tr><td><i class="fa-solid fa-mobile-screen fp-icon"></i> Model</td><td><?= esc($dev->device_Model) ?> <?= esc($dev->device_Brand ? '(' . $dev->device_Brand . ')' : '') ?></td></tr>
                    <tr><td><i class="fa-solid fa-fingerprint fp-icon"></i> Android ID</td><td><?= esc($dev->device_AndroidId ?? '-') ?></td></tr>
                    <tr><td><i class="fa-solid fa-code-branch fp-icon"></i> App Version</td><td><?= esc($dev->device_AppVersion ?? '-') ?></td></tr>
                    <tr><td><i class="fa-solid fa-shield-halved fp-icon"></i> Build Fingerprint</td><td><?= esc($dev->device_Fingerprint ?? '-') ?></td></tr>
                    <tr><td><i class="fa-solid fa-calendar-check fp-icon"></i> First Install</td><td><?= esc($dev->device_FirstInstallTime ? format_date_display($dev->device_FirstInstallTime) : '-') ?></td></tr>
                    <tr><td><i class="fa-solid fa-clock-rotate-left fp-icon"></i> Last Update</td><td><?= esc($dev->device_LastUpdateTime ? format_date_display($dev->device_LastUpdateTime) : '-') ?></td></tr>
                    <tr><td><i class="fa-solid fa-user-plus fp-icon"></i> Registered</td><td><?= esc($dev->device_Created_At ? format_date_display($dev->device_Created_At) : '-') ?></td></tr>
                    <tr><td><i class="fa-solid fa-globe fp-icon"></i> Locale</td><td><?= esc($dev->device_Locale ?? '-') ?></td></tr>
                    <tr><td><i class="fa-solid fa-earth-africa fp-icon"></i> Timezone</td><td><?= esc($dev->device_Timezone ?? '-') ?></td></tr>
                    <tr><td><i class="fa-solid fa-display fp-icon"></i> Screen</td><td><?= esc($dev->device_ScreenWidth) ?>x<?= esc($dev->device_ScreenHeight) ?> @<?= esc($dev->device_DensityDpi) ?>dpi</td></tr>
                    <tr><td><i class="fa-solid fa-hard-drive fp-icon"></i> Storage</td><td><?= esc($dev->device_StorageTotal) ?><?= isset($dev->device_StorageAvailable) ? ' total / ' . esc($dev->device_StorageAvailable) . ' available' : '' ?></td></tr>
                    <tr><td><i class="fa-solid fa-battery-three-quarters fp-icon"></i> Battery</td><td><?= esc($dev->device_BatteryCapacity ?? '-') ?> mAh</td></tr>
                    <tr><td><i class="fa-solid fa-microchip fp-icon"></i> ABIs / CPUs</td><td><?= esc($dev->device_Abis ?? '-') ?> / <?= esc($dev->device_CpuCount ?? '-') ?></td></tr>
                    <tr><td><i class="fa-solid fa-certificate fp-icon"></i> App Cert Hash</td><td><?= esc($dev->device_AppCertHash ?? '-') ?></td></tr>
                    <?php else: ?>
                    <tr><td colspan="2" class="text-center text-muted py-2">Fingerprint not captured for this device.</td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card stat-card mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-clock me-2"></i> Activity</h5>
                <table class="table table-sm kv mb-0">
                    <tr><td>First Upload</td><td><?= esc($metrics['first_upload'] ? format_date_display($metrics['first_upload']) : '-') ?></td></tr>
                    <tr><td>Last Upload</td><td><?= esc($metrics['last_upload'] ? format_date_display($metrics['last_upload']) : '-') ?></td></tr>
                    <tr><td>Last API Activity</td><td><?= esc($metrics['last_activity'] ? format_date_display($metrics['last_activity']) : '-') ?></td></tr>
                    <tr><td>IP Addresses</td><td><?= esc($metrics['ips'] ?? '-') ?></td></tr>
                </table>
            </div>
        </div>

        <div class="card stat-card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-key me-2"></i> Tokens Used</h5>
                <?php if (empty($metrics['tokens'])): ?>
                    <p class="text-muted small mb-0">No tokens linked to this device.</p>
                <?php else: ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($metrics['tokens'] as $t): ?>
                        <li class="mb-1"><span class="badge bg-light text-dark border"><code><?= esc($t) ?></code></span></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
