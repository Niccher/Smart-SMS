<?= $this->extend('Layouts/admin') ?>
<?= $this->section('title') ?> Spending Goals - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?><style>.settings-card{border:none;border-radius:4px;box-shadow:0 4px 15px rgba(0,0,0,0.03)}</style><?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div><h2 class="fw-bold mb-1" style="color:var(--primary);"><i class="fa-solid fa-bullseye me-2"></i>Spending Goals</h2><p class="text-secondary mb-0">Set targets and track your progress.</p></div>
    <div>
        <a href="<?= base_url('dashboard/settings') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
        <button class="btn btn-primary rounded-pill px-4 fw-semibold btn-sm" data-bs-toggle="modal" data-bs-target="#goalModal"><i class="fa-solid fa-plus me-1"></i> New Goal</button>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php if (session()->has('message')): ?><div class="alert alert-success alert-dismissible fade show"><?= session('message') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<?php if (empty($goals)): ?><div class="text-center py-5"><i class="fa-solid fa-bullseye fa-3x mb-3 text-muted opacity-50"></i><h5 class="text-muted">No goals yet.</h5></div>
<?php else: ?>
<div class="row g-4">
    <?php foreach ($goals as $g):
        $color = $g['percentage'] >= 100 ? '#FF4757' : ($g['percentage'] >= 80 ? '#FFA502' : '#2ED573');
    ?>
    <div class="col-md-6 col-lg-4">
        <div class="card settings-card h-100"><div class="card-body p-4">
            <div class="d-flex justify-content-between">
                <div><h6 class="fw-bold mb-0"><?= htmlspecialchars($g['label'] ?: $g['category']) ?></h6><small class="text-muted text-capitalize"><?= $g['period'] ?> · <?= $g['category'] ?></small></div>
                <form action="<?= base_url('dashboard/settings/goals/delete') ?>" method="POST" onsubmit="return confirm('Delete goal?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $g['id'] ?>"><button type="submit" class="btn btn-sm btn-link text-danger"><i class="fa-solid fa-trash-can"></i></button></form>
            </div>
            <div class="mt-3">
                <div class="d-flex justify-content-between small mb-1"><span>Progress</span><span class="fw-bold" style="color:<?= $color ?>"><?= $g['percentage'] ?>%</span></div>
                <div class="progress" style="height:8px"><div class="progress-bar" style="width:<?= $g['percentage'] ?>%;background:<?= $color ?>"></div></div>
            </div>
            <div class="d-flex justify-content-between mt-3 small">
                <span>Spent: <strong><?= currency_symbol() ?><?= number_format($g['spent'],0) ?></strong></span>
                <span>Target: <strong><?= currency_symbol() ?><?= number_format($g['target_amount'],0) ?></strong></span>
            </div>
            <div class="mt-2 small text-muted">Remaining: <strong><?= currency_symbol() ?><?= number_format($g['remaining'],0) ?></strong></div>
        </div></div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="modal fade" id="goalModal"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0" style="border-radius:4px;">
    <form action="<?= base_url('dashboard/settings/goals/save') ?>" method="POST">
        <div class="modal-header border-0 pb-0"><h5 class="fw-bold">New Spending Goal</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body p-4">
            <select name="category" class="form-select mb-3" required><?php foreach($categories as $c): ?><option><?= $c ?></option><?php endforeach; ?></select>
            <input type="text" name="label" class="form-control mb-3" placeholder="Label (e.g. Eating Out)">
            <input type="number" name="target_amount" class="form-control mb-3" placeholder="Target Amount" required step="0.01">
            <div class="d-flex gap-3 mb-3">
                <div class="form-check"><input class="form-check-input" type="radio" name="period" value="monthly" checked id="gm"><label for="gm">Monthly</label></div>
                <div class="form-check"><input class="form-check-input" type="radio" name="period" value="weekly" id="gw"><label for="gw">Weekly</label></div>
            </div>
            <div class="form-check"><input class="form-check-input" type="checkbox" name="rollover" value="1" id="gr"><label for="gr">Roll over unused</label></div>
        </div>
        <div class="modal-footer border-0 pt-0"><button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Save Goal</button></div>
    </form>
</div></div></div>
<?= $this->endSection() ?>
