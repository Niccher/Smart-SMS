<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> Device Details - Admin - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .stat-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .stat-card .stat-icon { width: 44px; height: 44px; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    .kv { font-size: 0.85rem; }
    .kv td:first-child { color: #6c757d; width: 40%; }
    .kv td:last-child { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 0.78rem; word-break: break-all; }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-mobile-screen-button me-2"></i> Device Details</h2>
        <p class="text-secondary mb-0"><span class="fingerprint"><?= esc($metrics['device']->device_Uuid ?? '') ?></span></p>
    </div>
    <a class="btn btn-outline-secondary btn-sm" href="<?= base_url('admin/devices') ?>">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Devices
    </a>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card"><div class="card-body p-4">
            <div class="fw-bold fs-3" style="color: var(--primary);"><?= number_format($metrics['upload_count']) ?></div>
            <div class="text-muted small">Uploads</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card"><div class="card-body p-4">
            <div class="fw-bold fs-3 text-success"><?= number_format($metrics['sms_total']) ?></div>
            <div class="text-muted small">SMS Records</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card"><div class="card-body p-4">
            <div class="fw-bold fs-3 text-info"><?= number_format($metrics['activity_count']) ?></div>
            <div class="text-muted small">API Calls</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card"><div class="card-body p-4">
            <div class="fw-bold fs-3 text-warning"><?= count($metrics['tokens']) ?></div>
            <div class="text-muted small">Tokens Used</div>
        </div></div>
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
                    <tr><td>Model</td><td><?= esc($dev->device_Model) ?> <?= esc($dev->device_Brand ? '(' . $dev->device_Brand . ')' : '') ?></td></tr>
                    <tr><td>Android ID</td><td><?= esc($dev->device_AndroidId ?? '-') ?></td></tr>
                    <tr><td>App Version</td><td><?= esc($dev->device_AppVersion ?? '-') ?></td></tr>
                    <tr><td>Build Fingerprint</td><td><?= esc($dev->device_Fingerprint ?? '-') ?></td></tr>
                    <tr><td>First Install (device)</td><td><?= esc($dev->device_FirstInstallTime ? date('Y-m-d H:i:s', (int) $dev->device_FirstInstallTime / 1000) : '-') ?></td></tr>
                    <tr><td>Last Update (device)</td><td><?= esc($dev->device_LastUpdateTime ? date('Y-m-d H:i:s', (int) $dev->device_LastUpdateTime / 1000) : '-') ?></td></tr>
                    <tr><td>Registered</td><td><?= esc($dev->device_Created_At ?? '-') ?></td></tr>
                    <tr><td>Manufacturer</td><td><?= esc($dev->device_Manufacturer ?? '-') ?></td></tr>
                    <tr><td>Hardware</td><td><?= esc($dev->device_Hardware ?? '-') ?></td></tr>
                    <tr><td>Serial</td><td><?= esc($dev->device_Serial ?? '-') ?></td></tr>
                    <tr><td>Locale</td><td><?= esc($dev->device_Locale ?? '-') ?></td></tr>
                    <tr><td>Timezone</td><td><?= esc($dev->device_Timezone ?? '-') ?></td></tr>
                    <tr><td>Screen</td><td><?= esc($dev->device_ScreenWidth) ?>x<?= esc($dev->device_ScreenHeight) ?> @<?= esc($dev->device_DensityDpi) ?>dpi</td></tr>
                    <tr><td>Storage</td><td><?= esc($dev->device_StorageTotal) ?><?= isset($dev->device_StorageAvailable) ? ' total / ' . esc($dev->device_StorageAvailable) . ' available' : '' ?></td></tr>
                    <tr><td>Battery</td><td><?= esc($dev->device_BatteryCapacity ?? '-') ?> mAh</td></tr>
                    <tr><td>ABIs / CPUs</td><td><?= esc($dev->device_Abis ?? '-') ?> / <?= esc($dev->device_CpuCount ?? '-') ?></td></tr>
                    <tr><td>App Cert Hash</td><td><?= esc($dev->device_AppCertHash ?? '-') ?></td></tr>
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
                    <tr><td>First Upload</td><td><?= esc($metrics['first_upload'] ?? '-') ?></td></tr>
                    <tr><td>Last Upload</td><td><?= esc($metrics['last_upload'] ?? '-') ?></td></tr>
                    <tr><td>Last API Activity</td><td><?= esc($metrics['last_activity'] ?? '-') ?></td></tr>
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
