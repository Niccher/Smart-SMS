<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?>Forgot Password <?= $this->endSection() ?>

<?= $this->section('main') ?>
    <div class="auth-header">
        <div class="brand-icon"><i class="fa-solid fa-key"></i></div>
        <h1>Forgot Password</h1>
        <p>Enter your email to receive a login / password reset link</p>
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

    <form action="<?= base_url('magic-link') ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="floatingEmailInput" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="floatingEmailInput" name="email" autocomplete="email" placeholder="you@domain.com" value="<?= old('email', auth()->user()->email ?? '') ?>" required>
        </div>

        <button type="submit" class="btn-primary">
            <i class="fa-solid fa-paper-plane me-2"></i>Send Reset Link
        </button>
    </form>

    <div class="auth-footer">
        <p class="mb-2">Remember your password? <a href="<?= base_url('login') ?>">Sign In</a></p>
        <p class="mb-0 small">Don't have an account? <a href="<?= base_url('register') ?>">Create account</a></p>
    </div>
<?= $this->endSection() ?>
