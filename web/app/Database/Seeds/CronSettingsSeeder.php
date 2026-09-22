<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CronSettingsSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        $defaults = [
            'cron_llm_process' => [
                'name'        => 'Process Pending LLM Jobs',
                'type'        => 'llm:process',
                'schedule'    => '*/5 * * * *',
                'description' => 'Triggers the ML backend for queued analysis jobs',
                'enabled'     => true,
            ],
            'cron_reports_send' => [
                'name'        => 'Send Scheduled Reports',
                'type'        => 'reports:send',
                'schedule'    => '0 6 * * *',
                'description' => 'Sends scheduled spending reports to users who have them enabled',
                'enabled'     => true,
            ],
            'cron_recurring_remind' => [
                'name'        => 'Send Recurring Bill Reminders',
                'type'        => 'recurring:remind',
                'schedule'    => '0 8 * * *',
                'description' => 'Sends 3-day upcoming bill reminders to opted-in users',
                'enabled'     => true,
            ],
            'cron_weekly_digest' => [
                'name'        => 'Send Weekly Digest',
                'type'        => 'weekly:digest',
                'schedule'    => '0 9 * * 0',
                'description' => 'Sends Sunday weekly financial digest email to opted-in users',
                'enabled'     => true,
            ],
            'cron_db_backup' => [
                'name'        => 'Create Database Backup',
                'type'        => 'db:backup',
                'schedule'    => '0 2 * * *',
                'description' => 'Dumps full database to writable/backups as a SQL file',
                'enabled'     => true,
            ],
            'cron_data_retention' => [
                'name'        => 'Enforce Data Retention',
                'type'        => 'data:retention',
                'schedule'    => '0 3 * * *',
                'description' => 'Deletes uploaded files and rows older than retention window',
                'enabled'     => true,
            ],
            'cron_logs_prune' => [
                'name'        => 'Prune Old System Logs',
                'type'        => 'logs:prune',
                'schedule'    => '0 4 1 * *',
                'description' => 'Prunes audit, cron, and email logs older than 90 days',
                'enabled'     => true,
            ],
            'cron_session_gc' => [
                'name'        => 'Clean Expired Sessions',
                'type'        => 'session:gc',
                'schedule'    => '0 0 * * *',
                'description' => 'Removes expired sessions and stale token logins',
                'enabled'     => true,
            ],
        ];

        foreach ($defaults as $key => $config) {
            $existing = $db->table('tbl_Settings')->where('key', $key)->get()->getRow();
            if (!$existing) {
                $db->table('tbl_Settings')->insert([
                    'key'         => $key,
                    'value'       => json_encode($config),
                    'type'        => 'json',
                    'description' => $config['description'],
                ]);
            }
        }
    }
}
