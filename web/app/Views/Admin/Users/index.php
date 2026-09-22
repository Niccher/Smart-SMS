<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> User Management - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); }
    .user-avatar { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; color: #fff; flex-shrink: 0; }
    .group-badge { font-size: 0.7rem; padding: 0.25rem 0.5rem; }
    .status-badge { font-size: 0.7rem; padding: 0.25rem 0.5rem; }
    .table thead th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; border-bottom-width: 1px; }
    .table td { vertical-align: middle; }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-users me-2"></i> User Management</h2>
        <p class="text-secondary mb-0">Manage user accounts, groups, and status</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="card settings-card mb-4">
    <div class="card-body p-3">
        <form method="get" action="<?= base_url('admin/users') ?>" class="d-flex gap-2 flex-wrap align-items-center">
            <div class="input-group" style="max-width: 340px;">
                <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-secondary"></i></span>
                <input type="text" class="form-control" name="q" value="<?= esc($search) ?>" placeholder="Search by username or email...">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass me-1"></i> Search</button>
            <?php if ($search !== ''): ?>
                <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card settings-card">
    <div class="card-body p-0">
        <?php if (empty($users)): ?>
            <div class="text-center text-muted p-5">
                <i class="fa-solid fa-user-slash fs-1 d-block mb-2"></i>
                No users found
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 55px;">ID</th>
                            <th>User</th>
                            <th style="width: 180px;">Groups</th>
                            <th style="width: 120px;">Status</th>
                            <th style="width: 120px;">Storage</th>
                            <th style="width: 160px;">Registered</th>
                            <th style="width: 190px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <?php
                                $initials = strtoupper(substr($user['username'], 0, 1));
                                $palette = ['#5D9CEC', '#A55EEA', '#26C6DA', '#EF5350', '#66BB6A', '#FFA726', '#AB47BC', '#EC407A'];
                                $avatarColor = $palette[$user['id'] % count($palette)];
                            ?>
                            <tr data-user-id="<?= $user['id'] ?>">
                                <td><small class="text-muted">#<?= $user['id'] ?></small></td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="user-avatar" style="background: <?= $avatarColor ?>;"><?= esc($initials) ?></div>
                                        <div>
                                            <strong class="d-block"><?= esc($user['username']) ?></strong>
                                            <em class="text-muted" style="font-size: 0.85rem;"><?= esc($user['email']) ?></em>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php foreach (explode(',', $user['groups']) as $g): ?>
                                        <?php
                                            $badgeClass = 'bg-secondary';
                                            if ($g === 'superadmin') $badgeClass = 'bg-danger';
                                            elseif ($g === 'admin') $badgeClass = 'bg-warning text-dark';
                                            elseif ($g === 'user') $badgeClass = 'bg-info';
                                        ?>
                                        <span class="badge group-badge <?= $badgeClass ?> me-1"><?= esc(ucfirst($g)) ?></span>
                                    <?php endforeach; ?>
                                </td>
                                <td>
                                    <?php if ($user['active']): ?>
                                        <span class="badge status-badge bg-success"><i class="fa-solid fa-circle-check me-1"></i>Active</span>
                                    <?php else: ?>
                                        <span class="badge status-badge bg-danger"><i class="fa-solid fa-circle-xmark me-1"></i>Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-muted"><?= esc($user['storage_human']) ?></small></td>
                                <td><small class="text-muted"><?= esc($user['created_at']) ?></small></td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-secondary btn-toggle"
                                            data-user-id="<?= $user['id'] ?>"
                                            data-username="<?= esc($user['username'], 'attr') ?>"
                                            data-active="<?= $user['active'] ? '1' : '0' ?>"
                                            title="<?= $user['active'] ? 'Deactivate' : 'Activate' ?>">
                                            <i class="fa-solid <?= $user['active'] ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                                        </button>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" title="Change Group">
                                                <i class="fa-solid fa-user-gear"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item btn-change-group" href="#" data-user-id="<?= $user['id'] ?>" data-username="<?= esc($user['username'], 'attr') ?>" data-group="user">User</a></li>
                                                <li><a class="dropdown-item btn-change-group" href="#" data-user-id="<?= $user['id'] ?>" data-username="<?= esc($user['username'], 'attr') ?>" data-group="admin">Admin</a></li>
                                                <li><a class="dropdown-item btn-change-group" href="#" data-user-id="<?= $user['id'] ?>" data-username="<?= esc($user['username'], 'attr') ?>" data-group="superadmin">Superadmin</a></li>
                                            </ul>
                                        </div>
                                        <button type="button" class="btn btn-outline-danger btn-delete" data-user-id="<?= $user['id'] ?>" data-username="<?= esc($user['username'], 'attr') ?>" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="card-footer bg-white border-0 p-3">
                    <nav aria-label="Users pagination">
                        <ul class="pagination pagination-sm mb-0 justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item"><a class="page-link" href="<?= base_url('admin/users?page=' . ($page - 1) . ($search ? '&q=' . urlencode($search) : '')) ?>">Previous</a></li>
                            <?php endif; ?>
                            <?php
                            $start = max(1, $page - 2);
                            $end = min($total_pages, $page + 2);
                            for ($i = $start; $i <= $end; $i++):
                            ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= base_url('admin/users?page=' . $i . ($search ? '&q=' . urlencode($search) : '')) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item"><a class="page-link" href="<?= base_url('admin/users?page=' . ($page + 1) . ($search ? '&q=' . urlencode($search) : '')) ?>">Next</a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <small class="text-muted d-block text-center mt-1">Showing <?= min($per_page, $total - ($page - 1) * $per_page) ?> of <?= $total ?> users</small>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function postAjax(url, body) {
    return fetch(url, {
        method: 'POST',
        body: new URLSearchParams(body),
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(r => r.json());
}

function confirmAction(options) {
    return Swal.fire({
        title: options.title,
        text: options.text,
        icon: options.icon || 'warning',
        showCancelButton: true,
        confirmButtonColor: options.confirmColor || '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: options.confirmText || 'Yes',
        cancelButtonText: 'Cancel',
        showLoaderOnConfirm: true,
        allowOutsideClick: false,
        preConfirm: () => options.action(),
    });
}

function toastResult(res) {
    if (res.status === 'success') {
        Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
        setTimeout(() => location.reload(), 900);
    } else {
        Swal.fire({ icon: 'error', title: 'Error', text: res.message });
    }
}

document.querySelectorAll('.btn-toggle').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.userId;
        const username = this.dataset.username;
        const active = this.dataset.active === '1' ? '0' : '1';
        const activate = active === '1';

        confirmAction({
            title: activate ? 'Activate user?' : 'Deactivate user?',
            text: `Are you sure you want to ${activate ? 'activate' : 'deactivate'} "${username}"?`,
            icon: activate ? 'info' : 'warning',
            confirmColor: activate ? '#198754' : '#d33',
            confirmText: activate ? 'Activate' : 'Deactivate',
            action: () => postAjax('<?= base_url('admin/users/toggle') ?>', { user_id: id, active }),
        }).then(result => { if (result.isConfirmed) toastResult(result.value); });
    });
});

document.querySelectorAll('.btn-change-group').forEach(a => {
    a.addEventListener('click', function(e) {
        e.preventDefault();
        const id = this.dataset.userId;
        const username = this.dataset.username;
        const group = this.dataset.group;

        confirmAction({
            title: 'Change group?',
            text: `Move "${username}" to the "${group}" group?`,
            icon: 'question',
            confirmColor: '#0d6efd',
            confirmText: 'Change',
            action: () => postAjax('<?= base_url('admin/users/change-group') ?>', { user_id: id, group }),
        }).then(result => { if (result.isConfirmed) toastResult(result.value); });
    });
});

document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.userId;
        const username = this.dataset.username;

        confirmAction({
            title: 'Delete user?',
            text: `Delete "${username}" and ALL their data? This cannot be undone.`,
            icon: 'warning',
            confirmText: 'Delete',
            action: () => postAjax('<?= base_url('admin/users/delete') ?>', { user_id: id }),
        }).then(result => { if (result.isConfirmed) toastResult(result.value); });
    });
});
</script>
<?= $this->endSection() ?>
