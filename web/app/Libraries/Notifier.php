<?php

namespace App\Libraries;

class Notifier
{
    private const DEFAULT_TRIGGERS = [
        'signup'            => true,
        'password_reset'    => true,
        'report'            => true,
        'ml_complete'       => true,
        'loot_uploaded'     => true,
        'new_device'        => true,
        'user_deleted_data' => true,
        'user_account_deleted' => true,
        'user_upgraded'     => true,
        'user_downgraded'   => true,
        'user_activated'    => true,
        'user_deactivated'  => true,
        'maintenance_started' => false,
        'maintenance_stopped' => false,
    ];

    /**
     * Trigger metadata (label + description) for the admin UI.
     */
    private const TRIGGER_META = [
        'signup'            => ['label' => 'Welcome Email', 'group' => 'User Lifecycle', 'description' => 'Send a welcome email when a new user signs up.'],
        'password_reset'    => ['label' => 'Password Reset / Magic Link', 'group' => 'Security', 'description' => 'Send password reset / magic link emails.'],
        'report'            => ['label' => 'Scheduled Reports', 'group' => 'Reporting', 'description' => 'Send scheduled spending reports.'],
        'ml_complete'       => ['label' => 'ML Analysis Complete', 'group' => 'Analysis', 'description' => 'Notify a user when their ML analysis finishes and results are ready.'],
        'loot_uploaded'     => ['label' => 'Backup Loot Uploaded', 'group' => 'Analysis', 'description' => 'Notify a user when they upload a new SMS backup.'],
        'new_device'        => ['label' => 'New Device Connected', 'group' => 'Security', 'description' => 'Notify a user when a new device connects to their account.'],
        'user_deleted_data' => ['label' => 'Data Deleted', 'group' => 'User Lifecycle', 'description' => 'Notify a user when they request to delete their data.'],
        'user_account_deleted' => ['label' => 'Account Deleted', 'group' => 'User Lifecycle', 'description' => 'Notify a user when their account is permanently deleted.'],
        'user_upgraded'     => ['label' => 'Role Upgraded', 'group' => 'Admin', 'description' => 'Notify a user when their role is upgraded (e.g. to admin).'],
        'user_downgraded'   => ['label' => 'Role Downgraded', 'group' => 'Admin', 'description' => 'Notify a user when their role is downgraded (e.g. from admin).'],
        'user_activated'    => ['label' => 'Account Activated', 'group' => 'User Lifecycle', 'description' => 'Notify a user when their account is activated.'],
        'user_deactivated'  => ['label' => 'Account Deactivated', 'group' => 'User Lifecycle', 'description' => 'Notify a user when their account is deactivated.'],
        'maintenance_started' => ['label' => 'Maintenance Started', 'group' => 'System', 'description' => 'Notify all users when maintenance mode starts.'],
        'maintenance_stopped' => ['label' => 'Maintenance Stopped', 'group' => 'System', 'description' => 'Notify all users when maintenance mode stops.'],
    ];

    /**
     * Trigger → email template mapping.
     */
    private const TRIGGER_TEMPLATES = [
        'signup'              => 'Emails/signup',
        'password_reset'      => 'Emails/password_reset',
        'report'              => 'Emails/report',
        'ml_complete'         => 'Emails/ml_complete',
        'loot_uploaded'       => 'Emails/loot_uploaded',
        'new_device'          => 'Emails/new_device',
        'user_deleted_data'   => 'Emails/user_deleted_data',
        'user_account_deleted' => 'Emails/user_account_deleted',
        'user_upgraded'       => 'Emails/user_upgraded',
        'user_downgraded'     => 'Emails/user_downgraded',
        'user_activated'      => 'Emails/user_activated',
        'user_deactivated'    => 'Emails/user_deactivated',
        'maintenance_started' => 'Emails/maintenance_started',
        'maintenance_stopped' => 'Emails/maintenance_stopped',
    ];

    private const DEFAULT_CONFIG = [
        'smtp_host'   => '',
        'smtp_port'   => 587,
        'smtp_user'   => '',
        'smtp_pass'   => '',
        'smtp_crypto' => 'tls',
        'from_email'  => 'noreply@example.com',
        'from_name'   => 'Mpesa Analyzer',
        'enabled'     => false,
    ];

    /**
     * Load the email/SMTP configuration from tbl_Settings (email_* keys).
     */
    public static function config(): array
    {
        $db = \Config\Database::connect();
        $rows = $db->table('tbl_Settings')
            ->where('`key` LIKE', 'email_%')
            ->get()
            ->getResultArray();

        $config = self::DEFAULT_CONFIG;
        foreach ($rows as $row) {
            $key = str_replace('email_', '', $row['key']);
            if (array_key_exists($key, $config)) {
                $value = $row['value'];
                if (is_bool($config[$key])) {
                    $value = (bool)$value;
                }
                if (is_int($config[$key])) {
                    $value = (int)$value;
                }
                $config[$key] = $value;
            }
        }

        // If from_email is unset or still default example.com, and smtp_user is an email address, match them
        if ((empty($config['from_email']) || strpos($config['from_email'], 'example.com') !== false) && filter_var($config['smtp_user'], FILTER_VALIDATE_EMAIL)) {
            $config['from_email'] = $config['smtp_user'];
        }

        return $config;
    }

    /**
     * Load the email trigger toggles (email_triggers JSON key).
     */
    public static function triggers(): array
    {
        $db = \Config\Database::connect();
        $row = $db->table('tbl_Settings')
            ->where('`key`', 'email_triggers')
            ->get()
            ->getRow();

        $saved = $row ? json_decode($row->value ?? '{}', true) : [];

        return array_merge(self::DEFAULT_TRIGGERS, is_array($saved) ? $saved : []);
    }

    /**
     * Metadata (key, label, description) for user-defined triggers.
     *
     * @return array<int, array{key: string, label: string, description: string}>
     */
    public static function customTriggers(): array
    {
        $db = \Config\Database::connect();
        $row = $db->table('tbl_Settings')
            ->where('`key`', 'email_triggers_custom')
            ->get()
            ->getRow();

        $saved = $row ? json_decode($row->value ?? '[]', true) : [];

        if (!is_array($saved)) {
            return [];
        }

        $out = [];
        foreach ($saved as $item) {
            if (!is_array($item) || empty($item['key'])) {
                continue;
            }
            $out[] = [
                'key'         => trim((string) $item['key']),
                'label'       => trim((string) ($item['label'] ?? $item['key'])),
                'description' => trim((string) ($item['description'] ?? '')),
            ];
        }

        return $out;
    }

    /**
     * Whether a given trigger is enabled.
     */
    public static function isTriggerEnabled(string $trigger): bool
    {
        $triggers = self::triggers();

        return !empty($triggers[$trigger]);
    }

    /**
     * Trigger metadata (label/group/description) for built-in and custom triggers.
     *
     * @return array<int, array{key: string, label: string, group: string, description: string, is_custom: bool}>
     */
    public static function triggerMeta(): array
    {
        $meta = [];
        foreach (self::TRIGGER_META as $key => $info) {
            $meta[] = [
                'key'         => $key,
                'label'       => $info['label'],
                'group'       => $info['group'],
                'description' => $info['description'],
                'is_custom'   => false,
            ];
        }

        foreach (self::customTriggers() as $custom) {
            $meta[] = [
                'key'         => $custom['key'],
                'label'       => $custom['label'],
                'group'       => 'Custom',
                'description' => $custom['description'],
                'is_custom'   => true,
            ];
        }

        return $meta;
    }

    /**
     * Whether the global email switch is on and an SMTP host is configured.
     */
    public static function isReady(): bool
    {
        $config = self::config();

        return $config['enabled'] && $config['smtp_host'] !== '';
    }

      /**
      * Send an email to a single recipient using a template.
      *
      * @param array $data  Variables passed to the view, including the "as-at" timestamp.
     * @param string $trigger  Logical trigger name (e.g. 'signup') recorded in the email log.
     * @return array{status: string, message: string}
     */
    public static function sendTemplate(string $to, string $subject, string $template, array $data = [], string $trigger = ''): array
    {
        $templateKey = $template;
        $trackingNumber = 'MPA-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));

        $data = array_merge($data, [
            'title'          => $subject,
            'sentAt'         => date('Y-m-d H:i:s T'),
            'emailId'        => $templateKey,
            '_recipient'     => $to,
            'trackingNumber' => $trackingNumber,
        ]);

        // Render the HTML body (a template that gets embedded in the layout).
        $contentView = self::templatePath($template);
        if ($contentView === '') {
            return ['status' => 'error', 'message' => 'Unknown email template: ' . $template];
        }

        $content = view($contentView, $data, ['debug' => false]);

        // Emails are HTML-only: the styled layout embeds the content view.
        $html = view('Emails/layout', array_merge($data, ['content' => $content]), ['debug' => false]);

        $logTrigger = $trigger !== '' ? $trigger : self::templateBaseName($template);
        return self::sendRaw($to, $subject, $html, '', $logTrigger, $trackingNumber);
    }

    /**
     * Derive a short trigger name from a view path (e.g. "Emails/signup" → "signup").
     *
     * @internal
     */
    private static function templateBaseName(string $template): string
    {
        return basename(str_replace('\\', '/', $template));
    }

    /**
     * Map a trigger key to its template base (without _text), or '' if unknown.
     *
     * @internal
     */
    private static function templatePath(string $template, bool $text = false): string
    {
        $suffix = $text ? '_text' : '';

        // Full view path like "Emails/signup".
        $view = $template . $suffix;
        $file = APPPATH . 'Views/' . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';

        return is_file($file) ? $view : '';
    }

    /**
     * Send an email using the configured SMTP settings with HTML + plain-text.
     *
     * @return array{status: string, message: string}
     */
    public static function send(string $to, string $subject, string $message, bool $html = false): array
    {
        return self::sendRaw($to, $subject, $html ? $message : '', $html ? '' : $message);
    }

    /**
     * Send a styled test email to verify SMTP configuration.
     *
     * @return array{status: string, message: string}
     */
    public static function sendTestEmail(string $to): array
    {
        $config = self::config();

        $data = [
            'to'         => $to,
            'smtpHost'   => $config['smtp_host'],
            'smtpPort'   => $config['smtp_port'],
            'smtpCrypto' => $config['smtp_crypto'],
            'fromEmail'  => $config['from_email'],
        ];

        return self::sendTemplate($to, 'Mpesa Analyzer Test Email', 'Emails/test', $data);
    }

    /**
     * Record an email send attempt in tbl_Email_Log.
     */
    public static function logEmail(string $trigger, string $to, string $subject, string $status, string $detail = ''): void
    {
        try {
            $db = \Config\Database::connect();
            if (!$db->tableExists('tbl_Email_Log')) {
                return;
            }
            $db->table('tbl_Email_Log')->insert([
                'trigger'    => $trigger,
                'to_email'   => $to,
                'subject'    => $subject,
                'status'     => $status,
                'message'    => $detail,
                'sent_at'    => gmdate('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // Logging must never break email sending.
        }
    }

    /**
     * Raw SMTP sender shared by both legacy send() and template-based sendTemplate().
     *
     * @return array{status: string, message: string}
     */
    /**
     * Raw SMTP sender shared by both legacy send() and template-based sendTemplate().
     * Auto-queues failed emails for deferred background retry via cron.
     *
     * @return array{status: string, message: string}
     */
    private static function sendRaw(string $to, string $subject, string $html = '', string $text = '', string $trigger = '', string $trackingNumber = ''): array
    {
        $result = self::sendRawDirect($to, $subject, $html, $text, $trigger, $trackingNumber);

        // If email failed to send or SMTP is unconfigured/disabled, queue it for deferred retry via cron
        if ($result['status'] !== 'success') {
            self::queueEmail($trigger, $to, $subject, $html, $text, $result['message']);
        }

        return $result;
    }

    /**
     * Direct raw SMTP sender without auto-queueing (used by processQueue to prevent infinite loops).
     *
     * @return array{status: string, message: string}
     */
    public static function sendRawDirect(string $to, string $subject, string $html = '', string $text = '', string $trigger = '', string $trackingNumber = ''): array
    {
        $config = self::config();

        if (!$config['enabled']) {
            $msg = 'Email notifications are disabled in the admin settings.';
            self::logEmail($trigger, $to, $subject, 'error', $msg);
            return ['status' => 'error', 'message' => $msg];
        }

        if ($config['smtp_host'] === '') {
            $msg = 'SMTP host is not configured. Add it in Admin > Email Notifications.';
            self::logEmail($trigger, $to, $subject, 'error', $msg);
            return ['status' => 'error', 'message' => $msg];
        }

        $hasHtml = $html !== '';
        $hasText = $text !== '';

        $mail = \Config\Services::email([
            'protocol'   => 'smtp',
            'SMTPHost'   => $config['smtp_host'],
            'SMTPUser'   => $config['smtp_user'],
            'SMTPPass'   => $config['smtp_pass'],
            'SMTPPort'   => (int)$config['smtp_port'],
            'SMTPCrypto' => $config['smtp_crypto'] === 'none' ? '' : $config['smtp_crypto'],
            'SMTPTimeout' => 20,
            'wordWrap'   => true,
            'charset'    => 'UTF-8',
            'newline'    => "\r\n",
            'CRLF'       => "\r\n",
            'mailType'   => $hasHtml ? 'html' : 'text',
        ], false);

        $mail->setCRLF("\r\n");
        $mail->setNewline("\r\n");

        $fromEmail = $config['from_email'];
        if (($fromEmail === '' || strpos($fromEmail, 'example.com') !== false) && filter_var($config['smtp_user'], FILTER_VALIDATE_EMAIL)) {
            $fromEmail = $config['smtp_user'];
        }
        $fromName = $config['from_name'] ?: 'Mpesa Analyzer';

        $mail->setFrom($fromEmail, $fromName);
        $mail->setTo($to);
        $mail->setSubject($subject);

        if ($hasHtml && $hasText) {
            $mail->setMessage($html);
            $mail->setAltMessage($text);
        } elseif ($hasHtml) {
            $mail->setMessage($html);
        } elseif ($hasText) {
            $mail->setMessage($text);
        } else {
            $mail->setMessage('(empty)');
        }

        $logMsg = 'Email sent to ' . $to . '.';
        if ($trackingNumber !== '') {
            $logMsg .= ' [Tracking: ' . $trackingNumber . ']';
        }

        if ($mail->send()) {
            self::logEmail($trigger, $to, $subject, 'success', $logMsg);
            return ['status' => 'success', 'message' => $logMsg];
        }

        $debug = $mail->printDebugger([]);
        $rawDebug = is_array($debug) ? implode("\n", $debug) : (string)$debug;
        $cleanDebug = trim(strip_tags($rawDebug));
        $msg = $cleanDebug ?: 'Unknown SMTP connection error.';

        if ($trackingNumber !== '') {
            $msg = '[Tracking: ' . $trackingNumber . '] ' . $msg;
        }

        self::logEmail($trigger, $to, $subject, 'error', $msg);

        if (strpos($config['from_email'], 'example.com') !== false) {
            $domain = preg_replace('/^mail\./i', '', $config['smtp_host']);
            $msg .= "\n\n💡 Tip: Your 'From Email' is currently '" . $config['from_email'] . "'. Mail servers (like " . $config['smtp_host'] . ") typically reject sending from '@example.com'. Change your 'From Email' to an authorized address under @" . ($domain ?: 'yourdomain.com') . " (e.g. info@" . ($domain ?: 'yourdomain.com') . ").";
        } elseif (strpos($config['smtp_host'], 'gmail.com') !== false || strpos($config['smtp_host'], 'google') !== false) {
            $msg .= "\n\n💡 Tip for Gmail: Google requires an App Password (Google Account → Security → 2-Step Verification → App passwords).";
        }

        return ['status' => 'error', 'message' => 'Failed to send email: ' . $msg];
    }

    /**
     * Send an email to multiple recipients using a template (batch-friendly).
     *
     * @param array $recipients  Array of ['email' => ..., 'data' => [...]] or just ['email1@...', ...]
     * @param string $trigger  Logical trigger name (e.g. 'maintenance_started') recorded in the email log.
     * @return array{status: string, sent: int, failed: int, message: string}
     */
    public static function sendToMany(string $subject, string $template, array $recipients, string $trigger = ''): array
    {
        $sent = 0;
        $failed = 0;
        $logTrigger = $trigger !== '' ? $trigger : self::templateBaseName($template);

        foreach ($recipients as $recipient) {
            if (is_array($recipient)) {
                $to = $recipient['email'];
                unset($recipient['email']);
                $data = $recipient;
            } else {
                $to = (string) $recipient;
                $data = [];
            }

            $result = self::sendTemplate($to, $subject, $template, $data, $logTrigger);
            if ($result['status'] === 'success') {
                $sent++;
            } else {
                $failed++;
            }
        }

        return [
            'status' => 'success',
            'sent'   => $sent,
            'failed' => $failed,
            'message' => "Sent {$sent} email(s). Failed: {$failed}.",
        ];
    }

    /**
     * Send a trigger-based email to a single user.
     *
     * @param array $extraData  Extra variables passed to the template.
     * @return array{status: string, message: string}
     */
    public static function sendTrigger(string $to, string $trigger, array $extraData = []): array
    {
        if (!isset(self::TRIGGER_TEMPLATES[$trigger])) {
            return ['status' => 'error', 'message' => 'No template registered for trigger: ' . $trigger];
        }

        // Subject is stored in language strings for i18n; fall back to a sensible default.
        $subject = self::subjectFor($trigger, $extraData);

        return self::sendTemplate($to, $subject, self::TRIGGER_TEMPLATES[$trigger], $extraData, $trigger);
    }

    /**
     * Human-readable subject per trigger.
     */
    public static function subjectFor(string $trigger, array $data = []): string
    {
        $subjects = [
            'signup'              => 'Welcome to Mpesa Analyzer!',
            'password_reset'      => 'Your Login Link',
            'report'              => 'Your Mpesa Analyzer ' . ucfirst((string)($data['frequency'] ?? 'Monthly')) . ' Report',
            'ml_complete'         => 'Your Mpesa Analyzer analysis is ready',
            'loot_uploaded'       => 'We received your M-Pesa backup 📥',
            'new_device'          => 'New device connected to your account',
            'user_deleted_data'   => 'Your Mpesa Analyzer data has been deleted',
            'user_account_deleted' => 'Your Mpesa Analyzer account has been deleted',
            'user_upgraded'       => 'Your role has been upgraded',
            'user_downgraded'     => 'Your role has been changed',
            'user_activated'      => 'Your Mpesa Analyzer account is now active',
            'user_deactivated'    => 'Your Mpesa Analyzer account has been deactivated',
            'maintenance_started' => 'Mpesa Analyzer maintenance started',
            'maintenance_stopped' => 'Mpesa Analyzer maintenance is complete',
        ];

        return $subjects[$trigger] ?? 'Notification from Mpesa Analyzer';
    }

    /**
     * Retrieve a page of the email send log from tbl_Email_Log.
     *
     * @return array<int,array>
     */
    public static function logEntries(int $limit = 50, int $offset = 0): array
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('tbl_Email_Log')) {
            return [];
        }

        return $db->table('tbl_Email_Log')
            ->orderBy('id', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();
    }

    /**
     * Total number of entries in the email log.
     */
    public static function logCount(): int
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('tbl_Email_Log')) {
            return 0;
        }

        return (int) $db->table('tbl_Email_Log')->selectCount('id')->get()->getRow()->id ?? 0;
    }

    /**
     * Queue an unsent email for deferred background retry.
     */
    public static function queueEmail(string $trigger, string $to, string $subject, string $html = '', string $text = '', string $error = ''): bool
    {
        try {
            $db = \Config\Database::connect();
            if (!$db->tableExists('tbl_Email_Queue')) {
                return false;
            }

            // Check if identical pending email is already queued to avoid duplicates
            $existing = $db->table('tbl_Email_Queue')
                ->where('to_email', $to)
                ->where('subject', $subject)
                ->where('status', 'pending')
                ->get()
                ->getRow();

            if ($existing) {
                return true;
            }

            $db->table('tbl_Email_Queue')->insert([
                'trigger'      => $trigger,
                'to_email'     => $to,
                'subject'      => $subject,
                'body_html'    => $html,
                'body_text'    => $text,
                'status'       => 'pending',
                'attempts'     => 0,
                'max_attempts' => 5,
                'last_error'   => substr($error, 0, 1000),
                'scheduled_at' => gmdate('Y-m-d H:i:s'),
                'created_at'   => gmdate('Y-m-d H:i:s'),
            ]);

            return true;
        } catch (\Throwable $e) {
            log_message('error', 'Failed to queue email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Process pending emails from the queue (called via cron command: php spark email:process).
     *
     * @return array{processed: int, sent: int, failed: int, message: string}
     */
    public static function processQueue(int $limit = 20): array
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('tbl_Email_Queue')) {
            return ['processed' => 0, 'sent' => 0, 'failed' => 0, 'message' => 'tbl_Email_Queue table does not exist'];
        }

        $pending = $db->table('tbl_Email_Queue')
            ->whereIn('status', ['pending', 'failed'])
            ->where('attempts < max_attempts')
            ->orderBy('id', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        if (empty($pending)) {
            return ['processed' => 0, 'sent' => 0, 'failed' => 0, 'message' => 'No pending emails in queue'];
        }

        $processed = 0;
        $sent = 0;
        $failed = 0;

        foreach ($pending as $item) {
            $processed++;
            $id = (int)$item['id'];
            $attempts = (int)$item['attempts'] + 1;

            $db->table('tbl_Email_Queue')->where('id', $id)->update([
                'status'   => 'sending',
                'attempts' => $attempts,
            ]);

            $result = self::sendRawDirect(
                $item['to_email'],
                $item['subject'],
                $item['body_html'] ?? '',
                $item['body_text'] ?? '',
                $item['trigger']
            );

            if ($result['status'] === 'success') {
                $sent++;
                $db->table('tbl_Email_Queue')->where('id', $id)->update([
                    'status'     => 'sent',
                    'sent_at'    => gmdate('Y-m-d H:i:s'),
                    'last_error' => null,
                ]);
            } else {
                $failed++;
                $newStatus = $attempts >= (int)$item['max_attempts'] ? 'failed' : 'pending';
                $db->table('tbl_Email_Queue')->where('id', $id)->update([
                    'status'     => $newStatus,
                    'last_error' => substr($result['message'], 0, 1000),
                ]);
            }
        }

        return [
            'processed' => $processed,
            'sent'      => $sent,
            'failed'    => $failed,
            'message'   => "Processed {$processed} queued email(s). Sent: {$sent}, Failed: {$failed}.",
        ];
    }

    /**
     * Total number of entries in the email queue.
     */
    public static function queueCount(?string $status = null): int
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('tbl_Email_Queue')) {
            return 0;
        }

        $builder = $db->table('tbl_Email_Queue');
        if ($status) {
            $builder->where('status', $status);
        }

        return (int) $builder->selectCount('id')->get()->getRow()->id ?? 0;
    }

    /**
     * Retrieve queue entries.
     */
    public static function queueEntries(int $limit = 50, int $offset = 0): array
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('tbl_Email_Queue')) {
            return [];
        }

        return $db->table('tbl_Email_Queue')
            ->orderBy('id', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();
    }
}
