<?= $this->extend('Layouts/admin') ?>
<?= $this->section('title') ?> Budget Tracker - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .budget-card { border-radius: 4px; border: none; background: rgba(255,255,255,0.85); backdrop-filter: blur(10px); box-shadow: 0 6px 24px rgba(31,38,135,0.06); transition: transform .2s; padding: 24px; }
    .budget-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(31,38,135,0.1); }
    .ring-wrap { position: relative; width: 110px; height: 110px; }
    .ring-wrap canvas { position: absolute; top: 0; left: 0; }
    .ring-wrap .ring-label { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; line-height: 1.2; }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-2">
    <div>
        <h2 class="fw-bold mb-1" style="color:var(--primary);">Budget Tracker</h2>
        <p class="text-secondary mb-0">Set spending limits and enforce discipline.</p>
    </div>
    <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#budgetModal"><i class="fa-solid fa-plus me-2"></i>New Budget</button>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success rounded-3"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<?php if (empty($budgets)): ?>
<div class="text-center py-5">
    <i class="fa-solid fa-piggy-bank fa-3x mb-3 text-muted opacity-50"></i>
    <h5 class="text-muted">No budgets established yet.</h5>
    <button class="btn btn-primary rounded-pill px-4 mt-3" data-bs-toggle="modal" data-bs-target="#budgetModal">Create Budget</button>
</div>
<?php else: ?>
<div class="row g-4">
    <?php foreach ($budgets as $b):
        $pct  = (float)$b['percentage'];
        $color = $pct >= 100 ? '#FF4757' : ($pct >= 80 ? '#FFA502' : '#2ED573');
    ?>
    <div class="col-md-6 col-lg-4">
        <div class="budget-card">
            <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($b['label'] ?: $b['category']) ?></h6>
            <small class="text-muted text-capitalize"><?= $b['period'] ?> · <?= $b['category'] ?></small>
            
            <div class="d-flex align-items-center gap-4 mt-4 mb-4">
                <div class="ring-wrap">
                    <canvas class="ring-canvas" data-pct="<?= $pct ?>" data-color="<?= $color ?>" width="110" height="110"></canvas>
                    <div class="ring-label">
                        <div class="fw-bold small" style="color:<?= $color ?>"><?= number_format($pct,0) ?>%</div>
                        <div style="font-size:10px" class="text-secondary">used</div>
                    </div>
                </div>
                <div>
                    <div class="small text-muted mb-1">Spent</div>
                    <div class="fw-bold" style="color:<?= $color ?>"><?= currency_symbol() ?> <?= number_format($b['spent'], 0) ?></div>
                    <?php if (!empty($b['carry_over'])): ?>
                    <div class="small text-muted mt-1">Carry-over</div>
                    <div class="small fw-bold text-info">+<?= currency_symbol() ?> <?= number_format($b['carry_over'], 0) ?></div>
                    <?php endif; ?>
                    <div class="small text-muted mt-2 mb-1">Limit</div>
                    <div class="fw-bold text-dark"><?= currency_symbol() ?> <?= number_format($b['amount_limit'], 0) ?></div>
                </div>
            </div>

            <div class="d-flex gap-2 border-top pt-3">
                <form method="POST" action="<?= base_url('dashboard/budget/delete') ?>" class="w-100 text-end" onsubmit="return confirm('Delete budget?')">
                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-light text-danger rounded-pill px-3 shadow-sm border"><i class="fa-solid fa-trash me-2"></i>Remove</button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Form Modal -->
<div class="modal fade" id="budgetModal">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0" style="border-radius:4px;">
        <form method="POST" action="<?= base_url('dashboard/budget/save') ?>">
            <div class="modal-header border-0 pb-0"><h5 class="fw-bold">New Budget</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4">
                <label class="form-label small fw-bold text-uppercase text-secondary">Target Category</label>
                <select name="category" class="form-select mb-3" required><?php foreach($categories as $c): ?><option value="<?= $c ?>"><?= $c ?></option><?php endforeach; ?></select>
                
                <label class="form-label small fw-bold text-uppercase text-secondary">Display Name</label>
                <input type="text" name="label" class="form-control mb-3" placeholder="e.g. Monthly Groceries">
                
                <label class="form-label small fw-bold text-uppercase text-secondary">Limit (Ksh)</label>
                <input type="number" name="amount_limit" class="form-control mb-3" required>
                
                <label class="form-label small fw-bold text-uppercase text-secondary">Period</label>
                    <div class="d-flex gap-3">
                        <div class="form-check"><input class="form-check-input" type="radio" name="period" value="monthly" checked id="pM"><label for="pM">Monthly</label></div>
                        <div class="form-check"><input class="form-check-input" type="radio" name="period" value="weekly" id="pW"><label for="pW">Weekly</label></div>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="rollover" value="1" id="budgetRollover">
                        <label class="form-check-label" for="budgetRollover">Roll over unused amount to next month</label>
                    </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Save Budget</button>
            </div>
        </form>
    </div></div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
document.querySelectorAll('.ring-canvas').forEach(canvas => {
    const pct = parseFloat(canvas.dataset.pct), color = canvas.dataset.color, ctx = canvas.getContext('2d');
    const cx = 55, cy = 55, r = 45;
    ctx.beginPath(); ctx.arc(cx, cy, r, 0, Math.PI * 2); ctx.strokeStyle = '#f0f0f0'; ctx.lineWidth = 10; ctx.stroke();
    ctx.beginPath(); ctx.arc(cx, cy, r, -Math.PI / 2, (pct/100) * Math.PI*2 - Math.PI/2); ctx.strokeStyle = color; ctx.lineWidth = 10; ctx.lineCap = 'round'; ctx.stroke();
});
</script>
<?= $this->endSection() ?>
