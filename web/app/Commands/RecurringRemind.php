<?php

namespace App\Commands;

use App\Libraries\Notifier;
use App\Models\RecurringPaymentModel;
use App\Models\UserSettingModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class RecurringRemind extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'recurring:remind';
    protected $description = 'Scan tracked recurring bills due in 3 days and dispatch email reminders to opted-in users.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $userSettingsModel = new UserSettingModel();
        $recurringModel    = new RecurringPaymentModel();

        $users = $db->table('users')->get()->getResultArray();
        $sentCount = 0;

        foreach ($users as $user) {
            $userId = (int)$user['id'];
            $settings = $userSettingsModel->getSettings($userId);

            // Check if user has recurring bill notifications enabled (default true)
            $remindersEnabled = (bool)($settings['notify_recurring_due'] ?? true);
            $emailAlerts      = (bool)($settings['notify_email_alerts'] ?? true);

            if (!$remindersEnabled || !$emailAlerts) {
                continue;
            }

            $upcoming = $recurringModel->getUpcoming($userId, 3);
            if (empty($upcoming)) {
                continue;
            }

            $email = $user['email'] ?? null;
            if (!$email) {
                continue;
            }

            $res = Notifier::sendTrigger($email, 'recurring_due', [
                'user'     => $user['username'] ?? $user['email'],
                'upcoming' => $upcoming,
                'count'    => count($upcoming),
            ]);

            if (($res['status'] ?? '') === 'sent') {
                $sentCount++;
                CLI::write("Dispatched 3-day recurring bill reminder to {$email} (" . count($upcoming) . " bills due).", 'green');
            }
        }

        CLI::write("Recurring bill reminder scan complete. Dispatched {$sentCount} reminder emails.", 'green');
    }
}
