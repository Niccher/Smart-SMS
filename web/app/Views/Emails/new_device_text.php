New Device Connected

A new device was linked to your Mpesa Analyzer account.

Device Details:
- Device Name: <?= esc($deviceName ?? 'Unknown Device') ?>
- Device Type: <?= esc($deviceType ?? 'Mobile') ?>
- Platform: <?= esc($platform ?? 'Unknown') ?>
- IP Address: <?= esc($ipAddress ?? 'Unknown') ?>
- Location (approx.): <?= esc($location ?? 'Unknown') ?>
- Connected At: <?= esc($connectedAt ?? date('Y-m-d H:i:s T')) ?>

If this was you, no action is needed. If you don't recognize this device, please secure your account immediately.

Review Devices: <?= esc(base_url('settings/security')) ?>

Email ID: new_device
Email sent:  <?= $sentAt ?? date('Y-m-d H:i:s T') ?>