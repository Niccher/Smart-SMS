<?php

namespace App\Models;

use CodeIgniter\Model;

class UserSettingModel extends Model
{
    protected $table      = 'tbl_User_Settings';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'user_id', 'currency', 'date_format', 'time_format',
        'default_budget_period', 'budget_alert_threshold',
        'dashboard_widgets', 'notify_email_alerts',
        'notify_budget_alerts', 'notify_low_balance',
        'notify_unusual_activity', 'export_default_format',
        'report_schedule_enabled', 'report_schedule_frequency',
        'report_schedule_email', 'report_schedule_day',
        'report_schedule_format',
    ];

    protected $useTimestamps = true;

    protected $defaults = [
        'currency'                => 'KES',
        'date_format'             => 'Y-m-d',
        'time_format'             => 'H:i',
        'default_budget_period'   => 'monthly',
        'budget_alert_threshold'  => 80,
        'dashboard_widgets'       => null,
        'notify_email_alerts'     => true,
        'notify_budget_alerts'    => true,
        'notify_low_balance'      => true,
        'notify_unusual_activity' => true,
        'export_default_format'   => 'csv',
        'report_schedule_enabled' => false,
        'report_schedule_frequency' => 'monthly',
        'report_schedule_email'   => null,
        'report_schedule_day'     => 1,
        'report_schedule_format'  => 'pdf',
    ];

    public function getSettings(int $userId): array
    {
        $row = $this->where('user_id', $userId)->first();
        if (!$row) {
            return $this->defaults;
        }
        $settings = $this->defaults;
        foreach ($settings as $key => $default) {
            if (isset($row[$key]) && $row[$key] !== null) {
                $settings[$key] = $row[$key];
            }
        }
        $settings['dashboard_widgets'] = $row['dashboard_widgets'] ?? null;
        return $settings;
    }

    public function saveSettings(int $userId, array $data): bool
    {
        $data['user_id'] = $userId;
        $existing = $this->where('user_id', $userId)->first();
        if ($existing) {
            return $this->update($existing['id'], $data);
        }
        return $this->insert($data) !== false;
    }

    public function getDateFormat(int $userId): string
    {
        return $this->getSettings($userId)['date_format'] ?? 'Y-m-d';
    }

    public function getCurrency(int $userId): string
    {
        return $this->getSettings($userId)['currency'] ?? 'KES';
    }

    public function getDashboardWidgets(int $userId): ?array
    {
        $raw = $this->where('user_id', $userId)->first();
        if ($raw && $raw['dashboard_widgets']) {
            $decoded = json_decode($raw['dashboard_widgets'], true);
            return is_array($decoded) ? $decoded : null;
        }
        return null;
    }
}
