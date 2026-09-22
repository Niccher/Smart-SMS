<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?>Register <?= $this->endSection() ?>

<?= $this->section('main') ?>
    <div class="auth-header">
        <div class="brand-icon"><i class="fa-solid fa-wallet"></i></div>
        <h1>Create Account</h1>
        <p>Join the financial analysis community</p>
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

    <form action="<?= base_url('register') ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="floatingEmailInput" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="floatingEmailInput" name="email" inputmode="email" autocomplete="email" placeholder="you@domain.com" value="<?= old('email') ?>" required>
        </div>

        <div class="form-group">
            <label for="floatingUsernameInput" class="form-label">Username</label>
            <input type="text" class="form-control" id="floatingUsernameInput" name="username" inputmode="text" autocomplete="username" placeholder="Choose a username" value="<?= old('username') ?>" required>
        </div>

        <div class="row g-2">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="floatingPasswordInput" class="form-label">Password</label>
                    <input type="password" class="form-control" id="floatingPasswordInput" name="password" inputmode="text" autocomplete="new-password" placeholder="Min 8 characters" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="floatingPasswordConfirmInput" class="form-label">Confirm</label>
                    <input type="password" class="form-control" id="floatingPasswordConfirmInput" name="password_confirm" inputmode="text" autocomplete="new-password" placeholder="Repeat password" required>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-primary">
            <i class="fa-solid fa-user-plus me-2"></i>Sign Up
        </button>
    </form>

    <div class="auth-footer">
        <p class="mb-2">Already have an account? <a href="<?= base_url('login') ?>">Sign In</a></p>
        <p class="mb-0 small"><a href="<?= base_url('magic-link') ?>" class="text-muted text-decoration-none">Forgot Password?</a> &bull; <a href="<?= base_url('auth/a/show') ?>" class="text-muted text-decoration-none">Verify Account</a></p>
    </div>
<?= $this->endSection() ?>
