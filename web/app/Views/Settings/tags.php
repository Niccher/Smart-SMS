<?= $this->extend('Layouts/admin') ?>
<?= $this->section('title') ?> Tags - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?><style>.settings-card{border:none;border-radius:4px;box-shadow:0 4px 15px rgba(0,0,0,0.03)}</style><?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div><h2 class="fw-bold mb-1" style="color:var(--primary);"><i class="fa-solid fa-tags me-2"></i>Transaction Tags</h2><p class="text-secondary mb-0">Create custom labels to organize your transactions.</p></div>
    <a href="<?= base_url('dashboard/settings') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php if (session()->has('message')): ?><div class="alert alert-success alert-dismissible fade show"><?= session('message') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card settings-card"><div class="card-body p-4">
            <h5 class="fw-bold mb-3" style="color:var(--primary);">New Tag</h5>
            <form action="<?= base_url('dashboard/settings/tags/save') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="mb-3"><label class="form-label fw-semibold">Name</label><input type="text" name="name" class="form-control" required placeholder="e.g. Groceries"></div>
                <div class="mb-3"><label class="form-label fw-semibold">Color</label><input type="color" name="color" class="form-control form-control-color" value="#438EB9"></div>
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold"><i class="fa-solid fa-plus me-1"></i> Create Tag</button>
            </form>
        </div></div>
    </div>
    <div class="col-lg-8">
        <div class="card settings-card"><div class="card-body p-4">
            <h5 class="fw-bold mb-3" style="color:var(--primary);">Your Tags</h5>
            <?php if (empty($tags)): ?><p class="text-muted">No tags yet. Create one to get started.</p>
            <?php else: ?>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($tags as $tag): ?>
                <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill border" style="border-color:<?= $tag['color'] ?>40 !important;background:<?= $tag['color'] ?>10;">
                    <span style="width:12px;height:12px;border-radius:50%;background:<?= $tag['color'] ?>;display:inline-block"></span>
                    <span class="fw-semibold"><?= htmlspecialchars($tag['name']) ?></span>
                    <form action="<?= base_url('dashboard/settings/tags/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete this tag?')">
                        <?= csrf_field() ?><input type="hidden" name="id" value="<?= $tag['id'] ?>"><button type="submit" class="btn btn-sm btn-link text-danger p-0"><i class="fa-solid fa-xmark"></i></button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div></div>
    </div>
</div>
<?= $this->endSection() ?>
