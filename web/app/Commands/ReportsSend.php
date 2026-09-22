<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ReportsSend extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'reports:send';
    protected $description = 'Send scheduled spending reports to users who have them enabled.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        if (!$db->tableExists('tbl_User_Settings')) {
            CLI::write('No user settings table found.', 'yellow');

            return;
        }

        $subscribers = $db->table('tbl_User_Settings')
            ->where('report_schedule_enabled', 1)
            ->get()
            ->getResultArray();

        if (empty($subscribers)) {
            CLI::write('No users have scheduled reports enabled.', 'green');

            return;
        }

        if (!\App\Libraries\Notifier::isTriggerEnabled('report')) {
            CLI::write('Report emails are disabled in admin Email Notifications settings.', 'yellow');

            return;
        }

        $sent = 0;
        foreach ($subscribers as $sub) {
            $frequency = $sub['report_schedule_frequency'] ?? 'monthly';
            $email = $sub['report_schedule_email'] ?? '';

            if (empty($email)) {
                CLI::write('User settings row #' . $sub['id'] . ' has no report email, skipping.', 'yellow');
                continue;
            }

            if (!$this->isDue($frequency, $sub)) {
                continue;
            }

            $user = \auth()->getProvider()->findById($sub['user_id']);

            $result = \App\Libraries\Notifier::sendTrigger($email, 'report', [
                'frequency' => $frequency,
                'period'    => date('F Y'),
            ]);

            if ($result['status'] === 'success') {
                CLI::write('Report sent to ' . $email . ' (' . $frequency . ').', 'green');
                $sent++;
                $db->table('tbl_User_Settings')->where('id', $sub['id'])->update(['report_schedule_last_sent' => date('Y-m-d')]);
            } else {
                CLI::write('Failed to send report to ' . $email . ': ' . $result['message'], 'red');
            }
        }

        CLI::write('Reports done. Sent: ' . $sent . ' of ' . count($subscribers) . ' subscribers.', 'green');
    }

    private function isDue(string $frequency, array $sub): bool
    {
        $today = date('Y-m-d');
        $lastSent = $sub['report_schedule_last_sent'] ?? null;

        if ($lastSent === $today) {
            return false;
        }

        $day = (int)($sub['report_schedule_day'] ?? 1);

        switch ($frequency) {
            case 'weekly':
                return date('N') === ($day === 7 ? '7' : (string)$day);

            case 'monthly':
                return date('j') === (string)$day;

            case 'quarterly':
                return in_array(date('n'), [1, 4, 7, 10], true) && date('j') === (string)$day;

            case 'yearly':
                return date('m-d') === sprintf('%02d-%02d', 1, $day);

            default:
                return false;
        }
    }
}
