<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?><?= lang('Auth.email2FATitle') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>
    <div class="auth-header">
        <div class="brand-icon"><i class="fa-solid fa-shield-halved"></i></div>
        <h1><?= lang('Auth.email2FATitle') ?></h1>
        <p><?= lang('Auth.confirmEmailAddress') ?></p>
    </div>

    <?php if (session('error')) : ?>
        <div class="alert alert-error"><?= session('error') ?></div>
    <?php endif ?>

    <form action="<?= url_to('auth-action-handle') ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="floatingEmailInput" class="form-label"><?= lang('Auth.email') ?></label>
            <input type="email" class="form-control" id="floatingEmailInput" name="email"
                inputmode="email" autocomplete="email" placeholder="you@domain.com"
                value="<?= old('email', auth()->user()->email ?? '') ?>" required />
        </div>

        <button type="submit" class="btn-primary">
            <i class="fa-solid fa-paper-plane me-2"></i><?= lang('Auth.send') ?>
        </button>
    </form>

    <div class="auth-footer">
        <p class="mb-1"><a href="<?= url_to('login') ?>"><i class="fa-solid fa-arrow-left me-1"></i>Back to Sign In</a></p>
    </div>
<?= $this->endSection() ?>
