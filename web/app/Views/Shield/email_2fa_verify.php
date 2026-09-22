<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?><?= lang('Auth.email2FATitle') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>
    <div class="auth-header">
        <div class="brand-icon"><i class="fa-solid fa-key"></i></div>
        <h1><?= lang('Auth.emailEnterCode') ?></h1>
        <p><?= lang('Auth.emailConfirmCode') ?></p>
    </div>

    <?php if (session('error') !== null) : ?>
        <div class="alert alert-error"><?= session('error') ?></div>
    <?php endif ?>

    <form action="<?= url_to('auth-action-verify') ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="floatingTokenInput" class="form-label">2FA Verification Code</label>
            <input type="number" class="form-control text-center fs-4 fw-bold" id="floatingTokenInput" name="token" placeholder="000000"
                inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" required style="letter-spacing: 6px;" />
        </div>

        <button type="submit" class="btn-primary">
            <i class="fa-solid fa-check-double me-2"></i><?= lang('Auth.confirm') ?>
        </button>
    </form>

    <div class="auth-footer">
        <p class="mb-1"><a href="<?= url_to('login') ?>"><i class="fa-solid fa-arrow-left me-1"></i>Back to Sign In</a></p>
    </div>
<?= $this->endSection() ?>
