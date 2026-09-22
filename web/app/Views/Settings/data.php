<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Data Management - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-database me-2"></i>Data Management</h2>
        <p class="text-secondary mb-0">Export, purge, and manage your financial records.</p>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?= $this->include('Layouts/_control_center_nav', ['activeTab' => 'data']) ?>

<?php if (session()->has('message')) : ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= session('message') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (session()->has('error')) : ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= session('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Left Column: Exports, Purging & Summary -->
    <div class="col-lg-6">
        <!-- Data Exports -->
        <div class="card settings-card mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-download me-2"></i>Export Transactions</h5>
                <p class="text-muted small mb-3">Download your analyzed transaction records in standard formats for backup or external spreadsheet analysis.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= base_url('dashboard/settings/export/csv') ?>" class="btn btn-success rounded-pill px-4 fw-semibold">
                        <i class="fa-solid fa-file-csv me-1"></i> Export CSV
                    </a>
                    <a href="<?= base_url('dashboard/settings/export/json') ?>" class="btn btn-info rounded-pill px-4 fw-semibold text-white">
                        <i class="fa-solid fa-file-code me-1"></i> Export JSON
                    </a>
                </div>
            </div>
        </div>

        <!-- Purge Old Data -->
        <div class="card settings-card mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-2" style="color: var(--primary);"><i class="fa-solid fa-eraser me-2"></i>Purge Old Data</h5>
                <p class="text-muted small mb-3">Remove SMS records and analyzed transactions older than a specified timeline. Account configurations remain untouched.</p>
                <form action="<?= base_url('dashboard/settings/data/purge') ?>" method="POST"
                      onsubmit="return confirm('This will permanently delete data older than the selected period. This cannot be undone. Continue?');">
                    <?= csrf_field() ?>
                    <div class="d-flex gap-2 align-items-end">
                        <div class="flex-grow-1">
                            <label class="form-label small fw-bold text-secondary">Delete older than:</label>
                            <select class="form-select form-select-sm" name="months">
                                <option value="3">3 months</option>
                                <option value="6">6 months</option>
                                <option value="12" selected>12 months</option>
                                <option value="24">24 months</option>
                                <option value="36">36 months</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4 fw-semibold flex-shrink-0">
                            <i class="fa-solid fa-trash-can me-1"></i> Purge Data
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Summary -->
        <div class="card settings-card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-2" style="color: var(--primary);"><i class="fa-solid fa-chart-simple me-2"></i>Data Summary</h5>
                <p class="text-muted small mb-0">Your account currently has <strong><?= $total_uploads ?? 0 ?></strong> upload batch(es) 
                with the earliest record from <strong><?= $oldest_upload ?></strong>.</p>
            </div>
        </div>
    </div>

    <!-- Right Column: Upload batches & Non-Finance deletion -->
    <div class="col-lg-6">
        <!-- Latest Upload Batch -->
        <div class="card settings-card mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-clock-rotate-left me-2"></i>Latest Upload Batch</h5>
                <p class="text-muted small mb-3">Your most recently uploaded SMS dataset batch.</p>
                <?php
                $db = \Config\Database::connect();
                $userId = auth()->user()->id;
                $tokenType = \CodeIgniter\Shield\Authentication\Authenticators\AccessTokens::ID_TYPE_ACCESS_TOKEN;
                $uploads = $db->query("
                    SELECT l.*, COALESCE(CASE WHEN l.loot_Created >= '2000-01-01' THEN l.loot_Created ELSE NULL END, ls.loot_Created, l.loot_Created) AS loot_Created_resolved
                    FROM tbl_Loot l
                    LEFT JOIN tbl_Loot_Summary ls ON ls.loot_Uuid = l.loot_Uuid
                    LEFT JOIN auth_identities i ON i.secret = SHA2(l.loot_Owner, 256) AND i.type = ?
                    WHERE l.loot_user_id = ? OR i.user_id = ?
                    ORDER BY COALESCE(CASE WHEN l.loot_Created >= '2000-01-01' THEN l.loot_Created ELSE NULL END, ls.loot_Created, l.loot_Created) DESC LIMIT 1
                ", [$tokenType, $userId, $userId])->getResult();
                ?>
                <?php if (!empty($uploads)): ?>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Uploaded</th>
                                <th>File Name</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($uploads as $upload): ?>
                            <tr>
                                <td><small class="text-secondary"><?= format_date_display($upload->loot_Created_resolved ?? $upload->loot_Created ?? '') ?></small></td>
                                <td><small class="fw-semibold text-dark"><?= htmlspecialchars($upload->loot_Name ?? 'N/A') ?></small></td>
                                <td class="text-end">
                                    <form action="<?= base_url('dashboard/settings/data/delete-upload') ?>" method="POST"
                                          onsubmit="return confirm('Delete this upload batch and all its SMS data? This cannot be undone.');"
                                          class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="uuid" value="<?= $upload->loot_Uuid ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2 py-0" style="font-size: 0.75rem;">
                                            <i class="fa-solid fa-trash-can"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-end">
                    <a href="<?= base_url('dashboard/history') ?>" class="btn btn-light btn-sm rounded-pill px-3 fw-bold border">
                        <i class="fa-solid fa-clock-rotate-left me-1"></i> Show all Uploads
                    </a>
                </div>
                <?php else: ?>
                <div class="alert alert-info mb-0 py-2">
                    <i class="fa-solid fa-circle-info me-1"></i> No upload batches found.
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Delete Non-Finance SMS -->
        <div class="card settings-card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-2 text-danger"><i class="fa-solid fa-trash-can me-2"></i>Delete Non-Finance SMS</h5>
                <p class="text-muted small mb-3">
                    Permanently delete SMS messages from marketing, OTPs, or service alerts that have no financial operations. Finance-related SMS are kept.
                </p>

                <div class="alert alert-danger py-2 small mb-3">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> Irreversible.
                </div>

                <div class="d-flex gap-3 mb-3 small">
                    <span class="badge bg-danger rounded-pill px-3 py-2">
                        <i class="fa-solid fa-envelope me-1"></i> <?= number_format($non_finance_count ?? 0) ?> SMS
                    </span>
                    <span class="badge bg-secondary rounded-pill px-3 py-2">
                        <i class="fa-solid fa-address-book me-1"></i> <?= number_format($non_finance_senders ?? 0) ?> senders
                    </span>
                </div>

                <form action="<?= base_url('dashboard/settings/data/delete-non-finance') ?>" method="POST"
                      onsubmit="return confirm('This permanently deletes <?= number_format($non_finance_count ?? 0) ?> non-finance SMS. This cannot be undone. Continue?');">
                    <?= csrf_field() ?>
                    <label class="form-label small fw-bold text-danger">Type <code>DELETE</code> to confirm</label>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm" name="confirm" placeholder="DELETE" autocomplete="off" required>
                        <button type="submit" class="btn btn-danger btn-sm fw-semibold" <?= empty($non_finance_count) ? 'disabled' : '' ?>>
                            Delete
                        </button>
                    </div>
                    <?php if (empty($non_finance_count)): ?>
                        <div class="form-text text-success small mt-1">No non-finance SMS found.</div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
