<?= $this->extend('Layouts/admin') ?>
<?= $this->section('title') ?> Token Usage - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .stat-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .token-table td, .token-table th { font-size: 0.85rem; }
    .token-mask { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 0.75rem; }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-key me-2"></i> Token Usage</h2>
        <p class="text-secondary mb-0">Access tokens that have uploaded or linked data for your account.</p>
    </div>
    <a class="btn btn-outline-secondary btn-sm" href="<?= base_url('dashboard/devices') ?>">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Devices
    </a>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="card stat-card">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-sm table-striped token-table">
                <thead>
                    <tr>
                        <th>Token</th>
                        <th>Label</th>
                        <th>Created</th>
                        <th>Last Used</th>
                        <th>Uploads</th>
                        <th>Last Upload</th>
                        <th>Device</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tokens)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-3">No access tokens found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($tokens as $t): ?>
                    <tr>
                        <td class="token-mask"><?= esc($t['masked_token']) ?></td>
                        <td><?= esc($t['name'] ?? '-') ?></td>
                        <td><small><?= format_date_display($t['created_at'] ?? '') ?></small></td>
                        <td><small><?= format_date_display($t['last_used_at'] ?? '') ?></small></td>
                        <td><span class="fw-bold"><?= number_format((int) $t['upload_count']) ?></span></td>
                        <td><small><?= format_date_display($t['last_upload'] ?? '') ?></small></td>
                        <td><small><?= esc($t['device_model'] ?: '-') ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
