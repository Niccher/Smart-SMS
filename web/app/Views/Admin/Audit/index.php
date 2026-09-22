<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> Audit Trail - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .audit-row { cursor: pointer; }
    .audit-row:hover { background: rgba(177,184,237,0.1); }
    .metadata-popover { max-width: 400px; }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-clipboard-list me-2"></i> Audit Trail</h2>
        <p class="text-secondary mb-0">Track all admin actions and system events.</p>
    </div>
    <a href="<?= base_url('admin/audit/export') . '?' . http_build_query($filters) ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-file-csv me-1"></i> Export CSV
    </a>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="row g-4">
    <!-- Filters -->
    <div class="col-lg-12">
        <div class="card settings-card mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-filter me-2"></i> Filters</h5>
                <form id="filterForm" class="row g-3" method="GET">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Category</label>
                        <select class="form-select form-select-sm" name="category" id="categoryFilter">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat => $count): ?>
                                <option value="<?= esc($cat) ?>" <?= $filters['category'] === $cat ? 'selected' : '' ?>>
                                    <?= esc(ucfirst($cat)) ?> <span class="badge bg-secondary ms-1"><?= $count ?></span>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Action</label>
                        <select class="form-select form-select-sm" name="action" id="actionFilter">
                            <option value="">All Actions</option>
                            <?php foreach ($actions as $act => $count): ?>
                                <option value="<?= esc($act) ?>" <?= $filters['action'] === $act ? 'selected' : '' ?>>
                                    <?= esc($act) ?> <span class="badge bg-secondary ms-1"><?= $count ?></span>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small">User</label>
                        <select class="form-select form-select-sm" name="user_id" id="userFilter">
                            <option value="">All Users</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>" <?= $filters['user_id'] == $user['id'] ? 'selected' : '' ?>>
                                    <?= esc($user['username']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small">Date From</label>
                        <input type="date" class="form-control form-control-sm" name="date_from" value="<?= esc($filters['date_from']) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small">Date To</label>
                        <input type="date" class="form-control form-control-sm" name="date_to" value="<?= esc($filters['date_to']) ?>">
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass me-1"></i> Apply</button>
                        <a href="<?= base_url('admin/audit') ?>" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-rotate me-1"></i> Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Audit Log Table -->
    <div class="col-lg-12">
        <div class="card settings-card">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0" style="color: var(--primary);"><i class="fa-solid fa-list me-2"></i> Audit Entries <span class="badge bg-secondary ms-1"><?= $total ?></span></h5>
                    <small class="text-muted">Showing <?= count($entries) ?> of <?= $total ?> entries</small>
                </div>

                <?php if (empty($entries)): ?>
                    <div class="text-center text-muted p-5">
                        <i class="fa-solid fa-inbox fs-1 d-block mb-2"></i>
                        No audit entries match the current filters.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 160px;">Date & Time</th>
                                    <th style="width: 120px;">User</th>
                                    <th style="width: 120px;">IP</th>
                                    <th style="width: 100px;">Category</th>
                                    <th style="width: 180px;">Action</th>
                                    <th>Description</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($entries as $entry): 
                                    $catColor = match($entry['action_category']) {
                                        'auth' => 'bg-primary',
                                        'user' => 'bg-success',
                                        'config' => 'bg-warning text-dark',
                                        'data' => 'bg-info text-dark',
                                        'system' => 'bg-danger',
                                        default => 'bg-secondary',
                                    };
                                    $meta = $entry['metadata'] ? json_decode($entry['metadata'], true) : [];
                                ?>
                                    <tr class="audit-row" tabindex="0" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-html="true" 
                                        data-bs-title="<strong>Metadata</strong>" 
                                        data-metadata="<?= esc(json_encode($meta, JSON_PRETTY_PRINT), 'attr') ?>">
                                        <td>
                                            <small><?= esc($entry['created_at']) ?></small>
                                        </td>
                                        <td>
                                            <strong><?= esc($entry['username'] ?? 'System') ?></strong>
                                            <?php if (!empty($entry['user_id'])): ?>
                                                <br><small class="text-muted">ID: <?= $entry['user_id'] ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><small class="text-muted font-monospace"><?= esc($entry['ip'] ?? '—') ?></small></td>
                                        <td><span class="badge <?= $catColor ?>"><?= esc(ucfirst($entry['action_category'])) ?></span></td>
                                        <td><code class="small"><?= esc($entry['action']) ?></code></td>
                                        <td>
                                            <?= esc($entry['description'] ?? '—') ?>
                                            <?php if (!empty($meta)): ?>
                                                <i class="fa-solid fa-info-circle text-muted ms-1" title="Click for metadata"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-secondary btn-sm btn-copy-meta" data-meta="<?= esc(json_encode($meta), 'attr') ?>" title="Copy metadata">
                                                <i class="fa-solid fa-copy"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav class="mt-3" aria-label="Audit log pagination">
                            <ul class="pagination pagination-sm justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= base_url('admin/audit') . '?page=' . ($page - 1) . '&' . http_build_query(array_filter($filters)) ?>">&laquo; Prev</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php
                                $start = max(1, $page - 2);
                                $end = min($total_pages, $page + 2);
                                for ($i = $start; $i <= $end; $i++):
                                ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= base_url('admin/audit') . '?page=' . $i . '&' . http_build_query(array_filter($filters)) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= base_url('admin/audit') . '?page=' . ($page + 1) . '&' . http_build_query(array_filter($filters)) ?>">Next &raquo;</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize popovers
document.addEventListener('DOMContentLoaded', function() {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(el) {
        return new bootstrap.Popover(el, {
            content: function() {
                var raw = el.getAttribute('data-metadata') || '';
                if (!raw || raw === '[]' || raw === '{}') {
                    return '<span class="text-muted fst-italic">No metadata</span>';
                }
                var escaped = raw
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;");
                return '<pre class="mb-0 small text-wrap font-monospace" style="max-height: 250px; overflow-y: auto;">' + escaped + '</pre>';
            }
        });
    });

    // Copy metadata
    document.querySelectorAll('.btn-copy-meta').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const meta = this.dataset.meta;
            navigator.clipboard.writeText(meta).then(() => {
                const original = this.innerHTML;
                this.innerHTML = '<i class="fa-solid fa-check text-success"></i>';
                setTimeout(() => this.innerHTML = original, 1000);
            });
        });
    });

    // Update actions when category changes
    const categoryFilter = document.getElementById('categoryFilter');
    const actionFilter = document.getElementById('actionFilter');
    if (categoryFilter && actionFilter) {
        categoryFilter.addEventListener('change', function() {
            // Reload page with new category to update actions
            const params = new URLSearchParams(window.location.search);
            params.set('category', this.value);
            params.delete('action');
            window.location.search = params.toString();
        });
    }
});
</script>
<?= $this->endSection() ?>