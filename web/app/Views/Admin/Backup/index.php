<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> Database Backup - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 6px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); background: var(--card-bg, #fff); }
    .metric-pill {
        background: var(--body-bg, rgba(0,0,0,0.02));
        border: 1px solid var(--card-border, #e2e8f0);
        border-radius: 6px;
        padding: 0.75rem 1rem;
        height: 100%;
    }
    .metric-pill .label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); font-weight: 600; }
    .metric-pill .val { font-size: 1.15rem; font-weight: 700; color: var(--text-main); line-height: 1.3; }
    .backup-row:hover { background: rgba(93, 95, 239, 0.04); }
    [data-bs-theme="dark"] .settings-card { box-shadow: 0 4px 20px rgba(0,0,0,0.25); }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-database me-2"></i> Database Backup</h2>
        <p class="text-secondary mb-0">Generate fresh SQL dumps, manage backup archives, and inspect storage footprint.</p>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Sub Navigation -->
<div class="card settings-card mb-4">
    <div class="card-body p-3 pb-0">
        <?= view('Admin/System/_nav', ['active' => 'backup']) ?>
    </div>
</div>

<?php
$freeBytes = @disk_free_space(WRITEPATH) ?: 0;
$totalBytes = @disk_total_space(WRITEPATH) ?: 1;
$usedBytes = $totalBytes - $freeBytes;
$pct = round(($usedBytes / $totalBytes) * 100, 1);
$freeGB = round($freeBytes / 1024 / 1024 / 1024, 1);
$totalGB = round($totalBytes / 1024 / 1024 / 1024, 1);
?>

<div class="row g-4 mb-4">
    <!-- Create Backup Card (Primary Action) -->
    <div class="col-lg-7">
        <div class="card settings-card h-100 border p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 bg-primary-subtle text-primary rounded"><i class="fa-solid fa-file-export fs-5"></i></span>
                    <div>
                        <h5 class="fw-bold mb-0">Create New Backup</h5>
                        <small class="text-muted">Generate and download an instant database dump</small>
                    </div>
                </div>
            </div>

            <form id="backupForm" class="d-flex flex-column h-100">
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <div class="metric-pill">
                            <div class="form-check form-switch mb-1">
                                <input class="form-check-input" type="checkbox" name="compress" id="compress" value="1" checked>
                                <label class="form-check-label fw-semibold" for="compress">
                                    <i class="fa-solid fa-file-zipper text-primary me-1"></i> Compress (gzip)
                                </label>
                            </div>
                            <small class="text-muted d-block" style="font-size: 0.75rem;">Significantly reduces file size</small>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="metric-pill">
                            <div class="form-check form-switch mb-1">
                                <input class="form-check-input" type="checkbox" name="structure_only" id="structureOnly" value="1">
                                <label class="form-check-label fw-semibold" for="structureOnly">
                                    <i class="fa-solid fa-table-columns text-primary me-1"></i> Schema Only
                                </label>
                            </div>
                            <small class="text-muted d-block" style="font-size: 0.75rem;">Exclude table rows / data</small>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3 flex-wrap mb-4">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                        <i class="fa-solid fa-download me-1"></i> Download Backup
                    </button>
                    <span id="backupStatus" class="text-secondary small"></span>
                </div>

                <div class="mt-auto pt-3 border-top d-flex align-items-center flex-wrap gap-2 text-muted small">
                    <span class="badge bg-body-secondary text-body border fw-normal">
                        <i class="fa-solid fa-server me-1"></i> <?= esc($db_info['driver']) ?> <?= esc($db_info['version']) ?>
                    </span>
                    <span class="badge bg-body-secondary text-body border fw-normal">
                        <i class="fa-solid fa-database me-1"></i> <?= esc($db_info['database']) ?>
                    </span>
                    <span class="badge bg-body-secondary text-body border fw-normal">
                        <i class="fa-solid fa-network-wired me-1"></i> <?= esc($db_info['host']) ?>
                    </span>
                </div>
            </form>
        </div>
    </div>

    <!-- Storage & Database Summary -->
    <div class="col-lg-5">
        <div class="card settings-card h-100 border p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 bg-info-subtle text-info rounded"><i class="fa-solid fa-chart-pie fs-5"></i></span>
                    <div>
                        <h5 class="fw-bold mb-0">Database Summary</h5>
                        <small class="text-muted">Storage &amp; footprint</small>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-4">
                    <div class="metric-pill text-center">
                        <div class="label">Tables</div>
                        <div class="val"><?= $table_stats['total_tables'] ?></div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="metric-pill text-center">
                        <div class="label">Rows</div>
                        <div class="val"><?= number_format($table_stats['total_rows']) ?></div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="metric-pill text-center">
                        <div class="label">DB Size</div>
                        <div class="val text-primary"><?= esc($table_stats['total_size_human']) ?></div>
                    </div>
                </div>
            </div>

            <!-- Disk Safeguard -->
            <div class="mt-auto pt-3 border-top">
                <div class="d-flex justify-content-between mb-1 small text-muted">
                    <span><i class="fa-solid fa-hard-drive me-1"></i> Disk Safeguard</span>
                    <span class="fw-semibold text-body"><?= $freeGB ?> GB free of <?= $totalGB ?> GB</span>
                </div>
                <div class="progress mb-2" style="height: 6px;">
                    <div class="progress-bar <?= $pct > 85 ? 'bg-danger' : 'bg-success' ?>" role="progressbar" style="width: <?= $pct ?>%" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small class="text-muted" style="font-size: 0.72rem;">Directory: <code><?= WRITEPATH ?>backups/</code></small>
            </div>
        </div>
    </div>
</div>

<!-- Backup History -->
<div class="card settings-card border mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div class="d-flex align-items-center gap-2">
                <span class="p-2 bg-success-subtle text-success rounded"><i class="fa-solid fa-clock-rotate-left fs-5"></i></span>
                <div>
                    <h5 class="fw-bold mb-0">Backup History <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1"><?= count($backup_history) ?></span></h5>
                    <small class="text-muted">Saved database archives on disk</small>
                </div>
            </div>
            <a href="<?= base_url('admin/audit') . '?category=system&action=db_backup_download' ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3" target="_blank">
                <i class="fa-solid fa-clipboard-list me-1"></i> Audit Log
            </a>
        </div>

        <?php if (empty($backup_history)): ?>
            <div class="text-center text-secondary py-5">
                <i class="fa-solid fa-folder-open fs-1 d-block mb-2 opacity-50"></i>
                <p class="mb-0">No backups found in archive. Generate your first backup above.</p>
            </div>
        <?php else: 
            $topHistory = array_slice($backup_history, 0, 5);
            $restHistory = array_slice($backup_history, 5);
        ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Filename</th>
                            <th class="text-center" style="width: 120px;">Size</th>
                            <th style="width: 200px;">Created</th>
                            <th class="text-end" style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topHistory as $idx => $backup): ?>
                            <tr class="backup-row">
                                <td class="text-secondary small"><?= $idx + 1 ?></td>
                                <td>
                                    <i class="fa-solid fa-file-zipper text-secondary me-2"></i><code><?= esc($backup['name']) ?></code>
                                </td>
                                <td class="text-center"><span class="badge bg-body-secondary text-body border"><?= esc($backup['human_size']) ?></span></td>
                                <td class="small text-muted"><?= esc($backup['modified']) ?></td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= base_url('admin/system/backup/download-file/' . urlencode($backup['name'])) ?>" class="btn btn-outline-primary" title="Download">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-delete-backup" data-file="<?= esc($backup['name']) ?>" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <?php if (!empty($restHistory)): ?>
                        <tbody class="collapse" id="olderBackups">
                            <?php foreach ($restHistory as $idx => $backup): ?>
                                <tr class="backup-row">
                                    <td class="text-secondary small"><?= $idx + 6 ?></td>
                                    <td>
                                        <i class="fa-solid fa-file-zipper text-secondary me-2"></i><code><?= esc($backup['name']) ?></code>
                                    </td>
                                    <td class="text-center"><span class="badge bg-body-secondary text-body border"><?= esc($backup['human_size']) ?></span></td>
                                    <td class="small text-muted"><?= esc($backup['modified']) ?></td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= base_url('admin/system/backup/download-file/' . urlencode($backup['name'])) ?>" class="btn btn-outline-primary" title="Download">
                                                <i class="fa-solid fa-download"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger btn-delete-backup" data-file="<?= esc($backup['name']) ?>" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    <?php endif; ?>
                </table>
            </div>

            <?php if (!empty($restHistory)): ?>
                <div class="text-center mt-3">
                    <button class="btn btn-outline-primary btn-sm px-4 rounded-pill" type="button" data-bs-toggle="collapse" data-bs-target="#olderBackups" aria-expanded="false" id="toggleOlderBtn">
                        <i class="fa-solid fa-chevron-down me-1"></i> Show Older Backups (<?= count($restHistory) ?>)
                    </button>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Collapsible Table Breakdown -->
<div class="card settings-card border">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="p-2 bg-secondary-subtle text-secondary rounded"><i class="fa-solid fa-table-list fs-5"></i></span>
                <div>
                    <h5 class="fw-bold mb-0">Database Schema Breakdown</h5>
                    <small class="text-muted"><?= $table_stats['total_tables'] ?> tables &bull; <?= number_format($table_stats['total_rows']) ?> rows &bull; <?= esc($table_stats['total_size_human']) ?></small>
                </div>
            </div>
            <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" type="button" data-bs-toggle="collapse" data-bs-target="#tableStatsCollapse" aria-expanded="false" id="toggleTableStatsBtn">
                <i class="fa-solid fa-chevron-down me-1"></i> View Tables
            </button>
        </div>

        <div class="collapse mt-3" id="tableStatsCollapse">
            <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Table Name</th>
                            <th class="text-end" style="width: 140px;">Rows</th>
                            <th class="text-end" style="width: 140px;">Size</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($table_stats['tables'] as $idx => $table): ?>
                            <tr>
                                <td class="text-secondary small"><?= $idx + 1 ?></td>
                                <td>
                                    <code class="small"><?= esc($table['name']) ?></code>
                                </td>
                                <td class="text-end fw-semibold">
                                    <?= number_format($table['rows']) ?>
                                </td>
                                <td class="text-end text-muted small">
                                    <?= esc($table['size_human']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <td colspan="2" class="text-end"><i class="fa-solid fa-calculator text-primary me-1"></i> Totals</td>
                            <td class="text-end"><?= number_format($table_stats['total_rows']) ?></td>
                            <td class="text-end"><?= esc($table_stats['total_size_human']) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function confirmAction(title, text, icon = 'warning', confirmText = 'Yes, proceed') {
    return Swal.fire({
        title: title,
        text: text,
        icon: icon,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        reverseButtons: true,
    });
}

// Backup form handler
document.getElementById('backupForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    const status = document.getElementById('backupStatus');
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generating...';
    status.textContent = 'Creating backup...';
    status.className = 'text-secondary small';

    const formData = new FormData(this);
    const params = new URLSearchParams();
    for (const [key, value] of formData) {
        params.append(key, value);
    }

    try {
        const response = await fetch('<?= base_url('admin/system/backup/download') ?>?' + params);
        if (!response.ok) {
            const err = await response.json();
            throw new Error(err.message || 'Backup failed');
        }

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = response.headers.get('Content-Disposition')?.match(/filename="(.+)"/)?.[1] || 'backup.sql';
        a.click();
        window.URL.revokeObjectURL(url);

        status.textContent = 'Backup downloaded!';
        status.className = 'text-success small fw-semibold';
    } catch (err) {
        status.textContent = 'Error: ' + err.message;
        status.className = 'text-danger small fw-semibold';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-download me-1"></i> Download Backup';
    }
});

// Delete backup handler
document.querySelectorAll('.btn-delete-backup').forEach(btn => {
    btn.addEventListener('click', async function() {
        const file = this.dataset.file;
        const result = await confirmAction(
            'Delete Backup?',
            `This will permanently delete "${file}".\n\nThis action cannot be undone.`,
            'warning',
            'Yes, delete'
        );
        if (!result.isConfirmed) return;
        
        const row = this.closest('tr');
        row.style.opacity = '0.5';
        
        try {
            const res = await fetch('<?= base_url('admin/system/backup/delete') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'file=' + encodeURIComponent(file)
            });
            const data = await res.json();
            if (data.status === 'success') {
                row.remove();
                Swal.fire('Deleted!', data.message, 'success');
            } else {
                Swal.fire('Error', data.message, 'error');
                row.style.opacity = '1';
            }
        } catch (e) {
            Swal.fire('Error', 'Error: ' + e.message, 'error');
            row.style.opacity = '1';
        }
    });
});

// Toggle button label switches
document.addEventListener('DOMContentLoaded', function() {
    const olderBtn = document.getElementById('toggleOlderBtn');
    const olderEl = document.getElementById('olderBackups');
    if (olderBtn && olderEl) {
        olderEl.addEventListener('show.bs.collapse', function() {
            olderBtn.innerHTML = '<i class="fa-solid fa-chevron-up me-1"></i> Hide Older Backups';
        });
        olderEl.addEventListener('hide.bs.collapse', function() {
            olderBtn.innerHTML = '<i class="fa-solid fa-chevron-down me-1"></i> Show Older Backups (<?= isset($restHistory) ? count($restHistory) : 0 ?>)';
        });
    }

    const tableBtn = document.getElementById('toggleTableStatsBtn');
    const tableEl = document.getElementById('tableStatsCollapse');
    if (tableBtn && tableEl) {
        tableEl.addEventListener('show.bs.collapse', function() {
            tableBtn.innerHTML = '<i class="fa-solid fa-chevron-up me-1"></i> Hide Tables';
        });
        tableEl.addEventListener('hide.bs.collapse', function() {
            tableBtn.innerHTML = '<i class="fa-solid fa-chevron-down me-1"></i> View Tables';
        });
    }
});
</script>
<?= $this->endSection() ?>