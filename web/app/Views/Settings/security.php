<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Security Settings - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .token-box {
        background: var(--bg-color);
        border: 1px solid var(--card-border);
        border-radius: 4px;
        padding: 12px;
        font-family: monospace;
        word-break: break-all;
        font-size: 0.9rem;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-shield-halved me-2"></i>Security</h2>
        <p class="text-secondary mb-0">Manage API tokens and connected devices.</p>
    </div>
    <a href="<?= base_url('dashboard/settings') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Settings
    </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (session()->has('message')) : ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= session('message') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card settings-card h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4" style="color: var(--primary);"><i class="fa-solid fa-key me-2"></i>API Tokens</h5>
                <p class="text-muted small mb-3">These tokens authenticate your Android app. Each token is tied to your account.</p>

                <?php if (empty($tokens)): ?>
                    <div class="alert alert-info">
                        <i class="fa-solid fa-circle-info me-1"></i> No API tokens generated yet.
                        <a href="<?= url_to('Info::index') ?>" class="alert-link">Generate one here</a>.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Created</th>
                                    <th>Last Used</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tokens as $token): ?>
                                <tr>
                                    <td><?= htmlspecialchars($token->name ?? 'Unknown') ?></td>
                                    <td><?= $token->created_at ?? 'N/A' ?></td>
                                    <td><?= $token->last_used_at ?? 'Never' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="<?= url_to('Info::index') ?>" class="btn btn-sm btn-primary rounded-pill px-3">
                        <i class="fa-solid fa-arrows-rotate me-1"></i> Manage Tokens
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card settings-card h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4" style="color: var(--primary);"><i class="fa-solid fa-mobile-screen me-2"></i>Linked Devices</h5>
                <p class="text-muted small mb-3">Android devices currently linked to your account.</p>

                <?php if (empty($devices)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="fa-solid fa-circle-info me-1"></i> No devices linked. Link your Android app using the token above.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Device Name</th>
                                    <th>Token</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($devices as $device): ?>
                                <tr>
                                    <td><?= htmlspecialchars($device->device_name ?? 'Unnamed Device') ?></td>
                                    <td><code><?= substr($device->device_token ?? '', 0, 12) ?>...</code></td>
                                    <td>
                                        <form action="<?= base_url('dashboard/settings/security/revoke-device') ?>" method="POST" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="device_token" value="<?= $device->device_token ?? '' ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill"
                                                    onclick="return confirm('Revoke this device? It will be disconnected immediately.');">
                                                <i class="fa-solid fa-unlink"></i> Revoke
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
