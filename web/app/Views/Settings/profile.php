<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Profile Settings - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-user me-2"></i>Profile Settings</h2>
        <p class="text-secondary mb-0">Update your account details and password.</p>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?= $this->include('Layouts/_control_center_nav', ['activeTab' => 'profile']) ?>

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
    <div class="col-lg-6">
        <div class="card settings-card h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4" style="color: var(--primary);">Account Details</h5>
                <form action="<?= base_url('dashboard/settings/profile/save') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username</label>
                        <input type="text" class="form-control" name="username"
                               value="<?= old('username', $user->username ?? '') ?>"
                               placeholder="Your display name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address</label>
                        <div class="input-group">
                            <input type="email" class="form-control" name="email"
                                   value="<?= old('email', $user->email ?? '') ?>"
                                   placeholder="your@email.com" disabled>
                            <span class="input-group-text" title="Email cannot be edited for now"><i class="fa-solid fa-lock text-muted"></i></span>
                        </div>
                        <div class="form-text text-muted mt-1">
                            <i class="fa-solid fa-circle-info me-1"></i> Email cannot be edited for now. Contact support to change it.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Member Since</label>
                        <p class="text-muted mb-0"><?= $user->created_at ?? 'N/A' ?></p>
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card settings-card h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4" style="color: var(--primary);"><i class="fa-solid fa-lock me-2"></i>Change Password</h5>
                <form action="<?= base_url('dashboard/settings/profile/save') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Current Password</label>
                        <input type="password" class="form-control" name="current_password" placeholder="Enter current password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Password</label>
                        <input type="password" class="form-control" name="new_password" placeholder="At least 8 characters" minlength="8">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Confirm New Password</label>
                        <input type="password" class="form-control" name="confirm_password" placeholder="Repeat new password">
                    </div>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-semibold">
                        <i class="fa-solid fa-key me-1"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Danger Zone: Account & Data Deletion -->
<div class="row g-4 mt-2">
    <div class="col-lg-6">
        <div class="card settings-card border-start border-4 border-warning h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3 text-warning"><i class="fa-solid fa-eraser me-2"></i>Delete Uploaded Data</h5>
                <p class="text-muted small mb-4">Delete all your uploaded SMS messages and financial summaries, but keep your username and login credentials active.</p>
                <form action="<?= base_url('process/delete_data') ?>" method="POST" id="deleteDataForm">
                    <?= csrf_field() ?>
                    <button type="button" id="deleteDataBtn" class="btn btn-outline-warning rounded-pill px-4 fw-bold btn-sm">
                        <i class="fa-solid fa-eraser me-1"></i> Delete My Data
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card settings-card border-start border-4 border-danger h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3 text-danger"><i class="fa-solid fa-trash-can me-2"></i>Delete Profile Account</h5>
                <p class="text-muted small mb-4">Permanently remove your account, linked device tokens, rules, and all associated files. This cannot be undone.</p>
                <form action="<?= base_url('process/delete_account') ?>" method="POST" id="deleteAccountForm">
                    <?= csrf_field() ?>
                    <button type="button" id="deleteAccountBtn" class="btn btn-outline-danger rounded-pill px-4 fw-bold btn-sm">
                        <i class="fa-solid fa-trash-can me-1"></i> Delete Account Permanently
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    function submitForm(formId) {
        document.getElementById(formId).submit();
    }

    // Delete Data
    const deleteDataBtn = document.getElementById('deleteDataBtn');
    if (deleteDataBtn) {
        deleteDataBtn.addEventListener('click', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Delete all your data?',
                text: 'This will permanently remove all your uploaded SMS data. This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete my data',
                cancelButtonText: 'Cancel'
            }).then(function (result) {
                if (result.isConfirmed) submitForm('deleteDataForm');
            });
        });
    }

    // Delete Account — requires typing DELETE
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');
    if (deleteAccountBtn) {
        deleteAccountBtn.addEventListener('click', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Delete your account permanently?',
                html: 'This will permanently remove your account and all data.<br><br>'
                    + 'To proceed, type <strong>DELETE</strong> below.',
                icon: 'warning',
                input: 'text',
                inputPlaceholder: 'Type DELETE to confirm',
                inputAttributes: { autocapitalize: 'off', autocorrect: 'off', autocomplete: 'off' },
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Delete my account',
                cancelButtonText: 'Cancel',
                inputValidator: function (value) {
                    if (value !== 'DELETE') {
                        return 'Please type DELETE to confirm';
                    }
                }
            }).then(function (result) {
                if (result.isConfirmed) submitForm('deleteAccountForm');
            });
        });
    }
});
</script>
<?= $this->endSection() ?>
