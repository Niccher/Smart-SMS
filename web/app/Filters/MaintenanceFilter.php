<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class MaintenanceFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $db = \Config\Database::connect();
        $settings = $db->table('tbl_Settings')
            ->whereIn('`key`', [
                'maintenance_mode',
                'maintenance_message',
                'maintenance_scheduled',
                'maintenance_schedule_start',
                'maintenance_schedule_end',
            ])
            ->get()
            ->getResultArray();

        $config = [];
        foreach ($settings as $s) {
            $config[$s['key']] = $s['value'];
        }

        // Record start/stop transitions (manual toggle or scheduled window).
        \App\Libraries\MaintenanceLogger::sync($config);

        $isMaintenance = filter_var($config['maintenance_mode'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $expectedEnd = $config['maintenance_schedule_end'] ?? '';

        // Scheduled maintenance window (when the manual toggle is off)
        if (!$isMaintenance && filter_var($config['maintenance_scheduled'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $start = $this->normalize($config['maintenance_schedule_start'] ?? '');
            $end = $this->normalize($config['maintenance_schedule_end'] ?? '');
            $now = gmdate('Y-m-d H:i:s'); // window times are stored as UTC

            if ($start !== '' && $end !== '' && $now >= $start && $now <= $end) {
                $isMaintenance = true;
                $expectedEnd = $end;
            }
        }

        if (!$isMaintenance) {
            return;
        }

        // Only superadmins can access the site during maintenance.
        try {
            $user = auth()->user();
            if ($user !== null && $user->inGroup('superadmin')) {
                return;
            }
        } catch (\Throwable $e) {
            // No active session/auth available; treat as a non-admin visitor.
        }

        $data = [
            'message'      => $config['maintenance_message'] ?? 'We are performing scheduled maintenance. Please check back soon.',
            'expected_end' => $expectedEnd,
        ];

        $response = service('response');
        $response->setStatusCode(503);
        $response->setBody(view('errors/html/error_503', $data));

        return $response;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    private function normalize(string $value): string
    {
        return str_replace('T', ' ', $value);
    }
}
