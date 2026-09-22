<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= lang('Auth.emailActivateSubject') ?></title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #F2F5F8; margin: 0; padding: 20px; color: #2D3436; }
        .email-card { max-width: 520px; margin: 0 auto; background: #FFFFFF; border-radius: 6px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border: 1px solid #E2E8F0; }
        .email-header { background-color: #438EB9; padding: 24px; text-align: center; color: #FFFFFF; }
        .email-header h2 { margin: 0; font-size: 20px; font-weight: 600; letter-spacing: 0.5px; }
        .email-body { padding: 32px 24px; text-align: center; }
        .activation-code { font-size: 32px; font-weight: 800; letter-spacing: 6px; color: #87B87F; background-color: #EAF5E8; padding: 16px 24px; border-radius: 6px; display: inline-block; margin: 20px 0; border: 1px dashed #87B87F; }
        .email-footer { background-color: #F8FAFC; padding: 16px 24px; border-top: 1px solid #E2E8F0; font-size: 12px; color: #636E72; line-height: 1.5; text-align: left; }
    </style>
</head>
<body>
    <div class="email-card">
        <div class="email-header">
            <h2>M-Pesa Analyzer</h2>
        </div>
        <div class="email-body">
            <p style="font-size: 15px; margin-bottom: 8px;"><?= lang('Auth.emailActivateMailBody') ?></p>
            <div class="activation-code"><?= esc($code) ?></div>
            <p style="font-size: 13px; color: #636E72;">Enter this activation code to complete your registration.</p>
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
