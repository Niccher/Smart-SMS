<?= $this->extend('Layouts/admin') ?>
<?= $this->section('title') ?> My Devices - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .stat-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .stat-card .stat-icon { width: 44px; height: 44px; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    .device-table td, .device-table th { font-size: 0.85rem; }
    .fingerprint { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 0.75rem; color: #6c757d; word-break: break-all; }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-mobile-screen-button me-2"></i> My Devices</h2>
        <p class="text-secondary mb-0">Every Android device that uploaded data for your account.</p>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primary btn-sm" href="<?= base_url('dashboard/devices/tokens') ?>">
            <i class="fa-solid fa-key me-1"></i> Token Usage
        </a>
        <a class="btn btn-outline-primary btn-sm" href="<?= base_url('dashboard/devices/activity') ?>">
            <i class="fa-solid fa-bolt me-1"></i> API Activity
        </a>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<?= $this->include('Layouts/_control_center_nav', ['activeTab' => 'devices']) ?>

<div class="card stat-card">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-list me-2"></i> Connected Devices</h5>
        <div class="table-responsive">
            <table class="table table-sm table-striped device-table">
                <thead>
                    <tr>
                        <th>Device</th>
                        <th>App Version</th>
                        <th>Uploads</th>
                        <th>First Upload</th>
                        <th>Last Upload</th>
                        <th>IPs</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($devices)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-3">No devices have uploaded data yet. Open the Android app and sync your messages.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($devices as $d): ?>
                    <tr>
                        <td>
                            <strong><?= esc($d->device_Model ?: ($d->device_Uuid ?: 'Unknown device')) ?></strong>
                            <?php if (!empty($d->device_Brand)): ?>
                                <span class="badge bg-light text-dark border ms-1"><?= esc($d->device_Brand) ?></span>
                            <?php endif; ?>
                            <div class="fingerprint"><?= esc($d->device_Uuid) ?></div>
                        </td>
                        <td><?= esc($d->device_AppVersion ?: '-') ?></td>
                        <td><span class="fw-bold"><?= number_format((int) $d->upload_count) ?></span></td>
                        <td><small><?= $d->first_upload ? format_date_display($d->first_upload) : '-' ?></small></td>
                        <td><small><?= $d->last_upload ? format_date_display($d->last_upload) : '-' ?></small></td>
                        <td><small class="text-muted"><?= esc($d->ips ?? '-') ?></small></td>
                        <td><a class="btn btn-sm btn-outline-primary" href="<?= base_url('dashboard/devices/detail/' . esc($d->device_Uuid, 'url')) ?>"><i class="fa-solid fa-eye"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
