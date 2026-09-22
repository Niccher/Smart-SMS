<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?><?= lang('Auth.emailActivateTitle') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>
    <div class="auth-header">
        <div class="brand-icon"><i class="fa-solid fa-user-check"></i></div>
        <h1><?= lang('Auth.emailActivateTitle') ?></h1>
        <p><?= lang('Auth.emailActivateBody') ?></p>
    </div>

    <?php if (session('error')) : ?>
        <div class="alert alert-error"><?= session('error') ?></div>
    <?php endif ?>

    <form action="<?= site_url('auth/a/verify') ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="floatingTokenInput" class="form-label">Activation Code</label>
            <input type="text" class="form-control text-center fs-4 fw-bold" id="floatingTokenInput" name="token" placeholder="000000" inputmode="numeric"
                pattern="[0-9]*" autocomplete="one-time-code" value="<?= old('token') ?>" required style="letter-spacing: 6px;" />
        </div>

        <button type="submit" class="btn-primary">
            <i class="fa-solid fa-paper-plane me-2"></i><?= lang('Auth.send') ?>
        </button>
    </form>

    <div class="auth-footer">
        <p class="mb-1"><a href="<?= url_to('login') ?>"><i class="fa-solid fa-arrow-left me-1"></i>Back to Sign In</a></p>
    </div>
<?= $this->endSection() ?>
