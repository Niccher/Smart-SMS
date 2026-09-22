<?php

namespace App\Commands;

use App\Libraries\Notifier;
use App\Models\UploadModel;
use App\Models\UserSettingModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class WeeklyDigest extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'weekly:digest';
    protected $description = 'Generate and send weekly financial spending digest emails to opted-in users.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $userSettingsModel = new UserSettingModel();
        $uploadModel       = new UploadModel();

        $users = $db->table('users')->get()->getResultArray();
        $sentCount = 0;

        foreach ($users as $user) {
            $userId = (int)$user['id'];
            $settings = $userSettingsModel->getSettings($userId);

            $digestEnabled = (bool)($settings['notify_weekly_digest'] ?? false);
            $emailAlerts   = (bool)($settings['notify_email_alerts'] ?? true);

            if (!$digestEnabled || !$emailAlerts) {
                continue;
            }

            $email = $user['email'] ?? null;
            if (!$email) {
                continue;
            }

            $stats = $uploadModel->getWeeklyDigestStats($userId);

            $res = Notifier::sendTrigger($email, 'weekly_digest', [
                'user'              => $user['username'] ?? $user['email'],
                'totalTransactions' => $stats['totalTransactions'] ?? 0,
                'totalSpent'        => $stats['totalSpent'] ?? 0.0,
                'topCategory'       => $stats['topCategory'] ?? 'N/A',
                'avgDaily'          => $stats['avgDaily'] ?? 0.0,
            ]);

            if (($res['status'] ?? '') === 'sent') {
                $sentCount++;
                CLI::write("Sent weekly financial digest email to {$email}.", 'green');
            }
        }

        CLI::write("Weekly digest execution complete. Delivered {$sentCount} emails.", 'green');
    }
}
