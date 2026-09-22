<?= $this->extend('Layouts/admin') ?>
<?= $this->section('title') ?> Recurring Transactions - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?><style>.settings-card{border:none;border-radius:4px;box-shadow:0 4px 15px rgba(0,0,0,0.03)}</style><?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div><h2 class="fw-bold mb-1" style="color:var(--primary);"><i class="fa-solid fa-rotate me-2"></i>Recurring Transactions</h2><p class="text-secondary mb-0">Track bills, subscriptions, and regular payments.</p></div>
    <div>
        <a href="<?= base_url('dashboard/settings') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
        <button class="btn btn-primary rounded-pill px-4 fw-semibold btn-sm" data-bs-toggle="modal" data-bs-target="#recModal"><i class="fa-solid fa-plus me-1"></i> Add Recurring</button>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php if (session()->has('message')): ?><div class="alert alert-success alert-dismissible fade show"><?= session('message') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card settings-card"><div class="card-body p-4">
            <h5 class="fw-bold mb-3" style="color:var(--primary);">Your Recurring Items</h5>
            <?php if (empty($recurring)): ?><p class="text-muted">No recurring transactions tracked yet.</p>
            <?php else: ?>
            <div class="table-responsive"><table class="table table-sm">
                <thead><tr><th>Label</th><th>Counterparty</th><th>Amount</th><th>Frequency</th><th>Next</th><th></th></tr></thead>
                <tbody><?php foreach ($recurring as $r): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($r['label']) ?></td>
                    <td><small><?= htmlspecialchars($r['counterparty']) ?></small></td>
                    <td><?= currency_symbol() ?><?= number_format($r['amount'],0) ?></td>
                    <td><span class="badge bg-info"><?= $r['frequency'] ?></span></td>
                    <td><small><?= $r['next_expected'] ?? 'N/A' ?></small></td>
                    <td>
                        <form action="<?= base_url('dashboard/settings/recurring/delete') ?>" method="POST" onsubmit="return confirm('Remove?')">
                            <?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-link text-danger"><i class="fa-solid fa-trash-can"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?></tbody>
            </table></div>
            <?php endif; ?>
        </div></div>

        <?php if (!empty($detected)): ?>
        <div class="card settings-card mt-4"><div class="card-body p-4">
            <h5 class="fw-bold mb-3" style="color:var(--primary);"><i class="fa-solid fa-magnifying-glass me-2"></i>Detected Patterns</h5>
            <p class="text-muted small">These transactions appear frequently. Add them as recurring items.</p>
            <div class="table-responsive"><table class="table table-sm">
                <thead><tr><th>Counterparty</th><th>Amount</th><th>Category</th><th>Occurrences</th></tr></thead>
                <tbody><?php foreach ($detected as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['counterparty']) ?></td>
                    <td><?= currency_symbol() ?><?= number_format($d['amount'],0) ?></td>
                    <td><?= htmlspecialchars($d['description']) ?></td>
                    <td><span class="badge bg-success"><?= $d['occurrences'] ?>x</span></td>
                </tr>
                <?php endforeach; ?></tbody>
            </table></div>
        </div></div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="recModal"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0" style="border-radius:4px;">
    <form action="<?= base_url('dashboard/settings/recurring/save') ?>" method="POST">
        <div class="modal-header border-0 pb-0"><h5 class="fw-bold">Add Recurring</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body p-4">
            <input type="text" name="label" class="form-control mb-3" placeholder="Label (e.g. Netflix)" required>
            <input type="text" name="counterparty" class="form-control mb-3" placeholder="Counterparty (e.g. Netflix Kenya)">
            <input type="number" name="amount" class="form-control mb-3" placeholder="Amount" required step="0.01">
            <select name="frequency" class="form-select mb-3"><option value="monthly">Monthly</option><option value="weekly">Weekly</option><option value="quarterly">Quarterly</option><option value="yearly">Yearly</option></select>
            <select name="direction" class="form-select mb-3"><option value="sent">Sent (Payment)</option><option value="received">Received (Income)</option></select>
            <input type="number" name="day_of_period" class="form-control" placeholder="Day of month (1-31)" min="1" max="31" value="1">
        </div>
        <div class="modal-footer border-0 pt-0"><button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Save</button></div>
    </form>
</div></div></div>
<?= $this->endSection() ?>
