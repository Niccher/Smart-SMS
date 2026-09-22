<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?>Check Email <?= $this->endSection() ?>

<?= $this->section('main') ?>
    <div class="auth-header">
        <div class="brand-icon"><i class="fa-solid fa-envelope-circle-check"></i></div>
        <h1>Check Your Inbox</h1>
        <p>We've sent a magic link to your email.</p>
    </div>

    <div style="text-align: center; margin: 28px 0;">
        <div style="font-size: 3.2rem; color: var(--primary); margin-bottom: 16px;">
            <i class="fa-solid fa-envelope-open-text"></i>
        </div>
        <p style="color: var(--text-muted); line-height: 1.7; font-size: 0.93rem;">
            Click the link in the email to log in securely.<br>
            If you don't see it, check your spam folder.
        </p>
    </div>

    <div class="auth-footer" style="margin-top: 20px;">
        <a href="<?= url_to('login') ?>" class="btn-primary" style="display:inline-block; text-decoration:none; text-align:center; width: auto; padding: 12px 32px;">
            <i class="fa-solid fa-arrow-left me-2"></i>Back to Login
        </a>
    </div>
<?= $this->endSection() ?>
