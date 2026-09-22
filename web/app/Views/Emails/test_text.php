✅ Email Settings Verified!

Your SMTP configuration is working correctly. This email uses the same styled
template system as all other Mpesa Analyzer notifications.

Email Details:
  Recipient    : <?= esc($to ?? '') ?>
  SMTP Host    : <?= esc($smtpHost ?? 'smtp.gmail.com') ?>
  Port         : <?= esc($smtpPort ?? 587) ?>
  Encryption   : <?= esc(ucfirst($smtpCrypto ?? 'tls')) ?>
  From Address : <?= esc($fromEmail ?? '') ?>

Email ID: <?= esc($emailId ?? '') ?>  As-at: <?= esc($asAt ?? '') ?>

Sent from the Mpesa Analyzer admin panel. All email notifications — including
signup, password resets, spending reports, and system alerts — use this same
styled template system.
