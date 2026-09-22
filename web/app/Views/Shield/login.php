<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?>Sign In <?= $this->endSection() ?>

<?= $this->section('main') ?>
    <div class="auth-header">
        <div class="brand-icon"><i class="fa-solid fa-wallet"></i></div>
        <h1>Welcome Back</h1>
        <p>Sign in to your analytics dashboard</p>
    </div>

    <?php if (session('error')) : ?>
        <div class="alert alert-error"><?= session('error') ?></div>
    <?php elseif (session('errors')) : ?>
        <div class="alert alert-error">
            <?php foreach (session('errors') as $error) : ?>
                <?= $error ?><br>
            <?php endforeach ?>
        </div>
    <?php endif ?>

    <?php if (session('message')) : ?>
        <div class="alert alert-success"><?= session('message') ?></div>
    <?php endif ?>

    <form action="<?= base_url('login') ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="floatingEmailInput" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="floatingEmailInput" name="email" inputmode="email" autocomplete="email" placeholder="you@domain.com" value="<?= old('email') ?>" required>
        </div>

        <div class="form-group">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="floatingPasswordInput" class="form-label mb-0">Password</label>
                <a href="<?= base_url('magic-link') ?>" class="small text-decoration-none" style="color: var(--primary); font-weight: 600;">Forgot Password?</a>
            </div>
            <input type="password" class="form-control" id="floatingPasswordInput" name="password" inputmode="text" autocomplete="current-password" placeholder="Enter your password" required>
        </div>

        <?php if (setting('Auth.sessionConfig')['allowRemembering']): ?>
            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="rememberCheck" <?php if (old('remember')): ?> checked <?php endif ?>>
                    <label class="form-check-label small" for="rememberCheck" style="color: var(--text-muted);">Keep me signed in</label>
                </div>
            </div>
        <?php endif; ?>

        <button type="submit" class="btn-primary">
            <i class="fa-solid fa-arrow-right-to-bracket me-2"></i>Sign In
        </button>
    </form>

    <div class="auth-footer">
        <p class="mb-2">Don't have an account? <a href="<?= base_url('register') ?>">Create account</a></p>
        <p class="mb-0 text-muted small"><a href="<?= base_url('auth/a/show') ?>" class="text-secondary text-decoration-none"><i class="fa-solid fa-key me-1"></i>Account Verification / 2FA</a></p>
    </div>
<?= $this->endSection() ?>
