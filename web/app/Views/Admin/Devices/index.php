<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> Devices - Admin - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .stat-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .device-table td, .device-table th { font-size: 0.82rem; }
    .fingerprint { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 0.72rem; color: #6c757d; word-break: break-all; }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-mobile-screen-button me-2"></i> Devices</h2>
        <p class="text-secondary mb-0">All connected Android devices across every account.</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="card stat-card">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-list me-2"></i> Connected Devices</h5>
        <div class="table-responsive">
            <table class="table table-sm table-striped device-table">
                <thead>
                    <tr>
                        <th>Device</th>
                        <th>Owner</th>
                        <th>App Version</th>
                        <th>Uploads</th>
                        <th>First Upload</th>
                        <th>Last Upload</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($devices)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-3">No devices registered yet.</td></tr>
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
                        <td>
                            <?php if (!empty($d->username)): ?>
                                <a href="<?= base_url('admin/users') ?>"><?= esc($d->username) ?></a>
                                <div class="text-muted small"><?= esc($d->email ?? '') ?></div>
                            <?php else: ?>
                                <span class="text-muted">Unassigned</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($d->device_AppVersion ?: '-') ?></td>
                        <td><span class="fw-bold"><?= number_format((int) $d->upload_count) ?></span></td>
                        <td><small><?= esc($d->first_upload ?? '-') ?></small></td>
                        <td><small><?= esc($d->last_upload ?? '-') ?></small></td>
                        <td><a class="btn btn-sm btn-outline-primary" href="<?= base_url('admin/devices/detail/' . esc($d->device_Uuid, 'url')) ?>"><i class="fa-solid fa-eye"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($orphans)): ?>
<div class="card stat-card mt-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-3 text-warning"><i class="fa-solid fa-triangle-exclamation me-2"></i> Orphan Uploads (no fingerprint row)</h5>
        <div class="table-responsive">
            <table class="table table-sm table-striped device-table">
                <thead>
                    <tr><th>Device UUID</th><th>Owner</th><th>Uploads</th><th>First Upload</th><th>Last Upload</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($orphans as $o): ?>
                    <tr>
                        <td class="fingerprint"><?= esc($o->device_Uuid) ?></td>
                        <td><?= esc($o->username ?? ('user #' . ($o->device_user_id ?? '-'))) ?></td>
                        <td><span class="fw-bold"><?= number_format((int) $o->upload_count) ?></span></td>
                        <td><small><?= esc($o->first_upload ?? '-') ?></small></td>
                        <td><small><?= esc($o->last_upload ?? '-') ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
