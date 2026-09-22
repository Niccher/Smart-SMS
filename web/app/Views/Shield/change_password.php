<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?>Change Password <?= $this->endSection() ?>

<?= $this->section('main') ?>
    <div class="auth-header">
        <div class="brand-icon"><i class="fa-solid fa-lock"></i></div>
        <h1>Change Password</h1>
        <p>Please choose a new secure password</p>
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

    <form action="<?= url_to('force-reset') ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="floatingPasswordInput" class="form-label">New Password</label>
            <input type="password" class="form-control" id="floatingPasswordInput" name="password" inputmode="text" autocomplete="new-password" placeholder="Enter new password" required>
        </div>

        <div class="form-group">
            <label for="floatingPasswordConfirmInput" class="form-label">Confirm New Password</label>
            <input type="password" class="form-control" id="floatingPasswordConfirmInput" name="password_confirm" inputmode="text" autocomplete="new-password" placeholder="Confirm new password" required>
        </div>

        <button type="submit" class="btn-primary">
            <i class="fa-solid fa-shield-check me-2"></i>Update Password
        </button>
    </form>

    <div class="auth-footer">
        <p class="mb-1"><a href="<?= url_to('login') ?>"><i class="fa-solid fa-arrow-left me-1"></i>Back to Sign In</a></p>
    </div>
<?= $this->endSection() ?>
