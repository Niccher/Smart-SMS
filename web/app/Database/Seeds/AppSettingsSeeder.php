<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed default global application settings into tbl_Settings.
 *
 * Keys follow the pattern <section>_<name> so the admin panel can
 * group them. All values are idempotent — existing rows are left
 * untouched so operator overrides survive re-deploys.
 */
class AppSettingsSeeder extends Seeder
{
    private const DEFAULTS = [
        // ── App identity ─────────────────────────────────────────────────
        [
            'key'         => 'app_name',
            'value'       => 'M-Pesa Analyzer',
            'type'        => 'string',
            'description' => 'Application display name shown in emails and the UI.',
        ],
        [
            'key'         => 'app_url',
            'value'       => '',
            'type'        => 'string',
            'description' => 'Canonical public URL of the app (e.g. https://app.example.com). Used in email links.',
        ],
        [
            'key'         => 'app_email_from',
            'value'       => 'noreply@analyzer.com',
            'type'        => 'string',
            'description' => 'Default "From" address for all outgoing emails.',
        ],
        [
            'key'         => 'app_email_from_name',
            'value'       => 'M-Pesa Analyzer',
            'type'        => 'string',
            'description' => 'Display name used in the "From" header of outgoing emails.',
        ],
        [
            'key'         => 'app_timezone',
            'value'       => 'Africa/Nairobi',
            'type'        => 'string',
            'description' => 'Default timezone for date/time display and cron scheduling.',
        ],

        // ── Maintenance mode ──────────────────────────────────────────────
        [
            'key'         => 'maintenance_mode',
            'value'       => 'false',
            'type'        => 'boolean',
            'description' => 'When true the app shows a maintenance page to non-admin users.',
        ],
        [
            'key'         => 'maintenance_message',
            'value'       => 'We are performing scheduled maintenance. We will be back shortly.',
            'type'        => 'string',
            'description' => 'Message shown on the maintenance page.',
        ],

        // ── Data retention ────────────────────────────────────────────────
        [
            'key'         => 'data_retention_months',
            'value'       => '24',
            'type'        => 'integer',
            'description' => 'Number of months of SMS/transaction data to retain. Older rows are pruned by the data:retention cron job.',
        ],
        [
            'key'         => 'log_retention_days',
            'value'       => '90',
            'type'        => 'integer',
            'description' => 'Number of days to keep audit, cron, and email log rows.',
        ],

        // ── Backups ───────────────────────────────────────────────────────
        [
            'key'         => 'backup_retention_days',
            'value'       => '7',
            'type'        => 'integer',
            'description' => 'Number of days to keep local SQL backup files before deletion.',
        ],
        [
            'key'         => 'backup_path',
            'value'       => 'writable/backups',
            'type'        => 'string',
            'description' => 'Relative path (from app root) where SQL backup files are stored.',
        ],

        // ── Registration ──────────────────────────────────────────────────
        [
            'key'         => 'registration_enabled',
            'value'       => 'true',
            'type'        => 'boolean',
            'description' => 'Allow new users to self-register. Set to false to lock down to invite-only.',
        ],
        [
            'key'         => 'registration_requires_approval',
            'value'       => 'false',
            'type'        => 'boolean',
            'description' => 'When true newly registered accounts are inactive until an admin approves them.',
        ],

        // ── Analysis / ML ─────────────────────────────────────────────────
        [
            'key'         => 'ml_auto_process',
            'value'       => 'true',
            'type'        => 'boolean',
            'description' => 'Automatically trigger ML analysis after each SMS upload.',
        ],
        [
            'key'         => 'ml_max_upload_size_mb',
            'value'       => '50',
            'type'        => 'integer',
            'description' => 'Maximum allowed SMS file upload size in megabytes.',
        ],

        // ── Email / SMTP ──────────────────────────────────────────────────
        [
            'key'         => 'smtp_host',
            'value'       => '',
            'type'        => 'string',
            'description' => 'SMTP server hostname. Leave blank to use the CI4 email config / env vars.',
        ],
        [
            'key'         => 'smtp_port',
            'value'       => '587',
            'type'        => 'integer',
            'description' => 'SMTP server port.',
        ],
        [
            'key'         => 'smtp_user',
            'value'       => '',
            'type'        => 'string',
            'description' => 'SMTP authentication username.',
        ],
        [
            'key'         => 'smtp_crypto',
            'value'       => 'tls',
            'type'        => 'string',
            'description' => 'SMTP encryption protocol (tls or ssl).',
        ],
    ];

    public function run(): void
    {
        $db       = \Config\Database::connect();
        $now      = date('Y-m-d H:i:s');
        $inserted = 0;
        $skipped  = 0;

        foreach (self::DEFAULTS as $row) {
            $exists = $db->table('tbl_Settings')
                ->where('key', $row['key'])
                ->get()->getRow();

            if ($exists) {
                $skipped++;
                continue;
            }

            $db->table('tbl_Settings')->insert([
                'key'         => $row['key'],
                'value'       => $row['value'],
                'type'        => $row['type'],
                'description' => $row['description'],
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            $inserted++;
        }

        echo "AppSettingsSeeder: inserted {$inserted}, skipped {$skipped}." . PHP_EOL;
    }
}
