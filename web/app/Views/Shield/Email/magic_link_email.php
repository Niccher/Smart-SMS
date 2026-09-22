<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= lang('Auth.magicLinkSubject') ?></title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #F2F5F8; margin: 0; padding: 20px; color: #2D3436; }
        .email-card { max-width: 520px; margin: 0 auto; background: #FFFFFF; border-radius: 6px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border: 1px solid #E2E8F0; }
        .email-header { background-color: #438EB9; padding: 24px; text-align: center; color: #FFFFFF; }
        .email-header h2 { margin: 0; font-size: 20px; font-weight: 600; letter-spacing: 0.5px; }
        .email-body { padding: 32px 24px; text-align: center; }
        .btn-action { background-color: #438EB9; color: #FFFFFF !important; font-size: 16px; font-weight: 600; padding: 14px 32px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 20px 0; box-shadow: 0 4px 12px rgba(67, 142, 185, 0.3); }
        .btn-action:hover { background-color: #337ab7; }
        .email-footer { background-color: #F8FAFC; padding: 16px 24px; border-top: 1px solid #E2E8F0; font-size: 12px; color: #636E72; line-height: 1.5; text-align: left; }
    </style>
</head>
<body>
    <div class="email-card">
        <div class="email-header">
            <h2>M-Pesa Analyzer</h2>
        </div>
        <div class="email-body">
            <h3 style="margin-top: 0; color: #2D3436; font-size: 18px;"><?= lang('Auth.magicLinkSubject') ?></h3>
            <p style="font-size: 14px; color: #636E72; line-height: 1.6;">Click the button below to log in directly to your M-Pesa Analyzer account without entering a password.</p>
            <a href="<?= url_to('verify-magic-link') ?>?token=<?= $token ?>" class="btn-action"><?= lang('Auth.login') ?></a>
            <p style="font-size: 12px; color: #94A3B8; margin-top: 16px;">If you didn't request this link, you can safely ignore this email.</p>
        </div>
        <div class="email-footer">
            <strong><?= lang('Auth.emailInfo') ?></strong><br>
            <?= lang('Auth.emailIpAddress') ?> <?= esc($ipAddress) ?><br>
            <?= lang('Auth.emailDevice') ?> <?= esc($userAgent) ?><br>
            <?= lang('Auth.emailDate') ?> <?= esc($date) ?>
        </div>
    </div>
</body>
</html>
