<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\CronLogger;
use App\Libraries\CronRunner;
use CodeIgniter\API\ResponseTrait;

class Crons extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $jobs = $this->loadJobs();

        return view('Admin/Crons/index', [
            'bg_color' => '#B1B8ED',
            'cron_jobs' => $jobs,
            'cron_runs' => CronLogger::recent(100),
            'run_type_counts' => CronLogger::typeCounts(),
            'job_types' => CronRunner::types(),
            'job_sections' => $this->jobSections(),
            'job_type_counts' => $this->jobTypeCounts($jobs),
            'presets' => [
                ['value' => '* * * * *', 'label' => 'Every minute'],
                ['value' => '*/5 * * * *', 'label' => 'Every 5 minutes'],
                ['value' => '*/10 * * * *', 'label' => 'Every 10 minutes'],
                ['value' => '*/15 * * * *', 'label' => 'Every 15 minutes'],
                ['value' => '*/30 * * * *', 'label' => 'Every 30 minutes'],
                ['value' => '0 * * * *', 'label' => 'Every hour'],
                ['value' => '0 2 * * *', 'label' => 'Daily at 2:00 AM'],
                ['value' => '0 6 * * *', 'label' => 'Daily at 6:00 AM'],
                ['value' => '0 6 * * 1', 'label' => 'Weekly on Monday at 6:00 AM'],
                ['value' => '0 3 * * 0', 'label' => 'Weekly on Sunday at 3:00 AM'],
            ],
        ]);
    }

    public function save()
    {
        $name = trim((string) $this->request->getPost('name'));
        $type = trim((string) $this->request->getPost('type'));
        $schedule = trim((string) $this->request->getPost('schedule'));
        $description = trim((string) $this->request->getPost('description'));
        $enabled = (bool) $this->request->getPost('enabled');
        $key = trim((string) $this->request->getPost('job_key'));

        if ($name === '') {
            return $this->respond(['status' => 'error', 'message' => 'Job name is required.'], 400);
        }

        if (!array_key_exists($type, CronRunner::types())) {
            return $this->respond(['status' => 'error', 'message' => 'Invalid job type.'], 400);
        }

        if (!self::validSchedule($schedule)) {
            return $this->respond(['status' => 'error', 'message' => 'Invalid cron schedule. Use 5 fields, e.g. */5 * * * *.'], 400);
        }

        $slug = url_title($name, '-', true);
        if ($slug === '') {
            $slug = 'job-' . time();
        }

        $db = \Config\Database::connect();

        // Preserve existing run history when editing.
        $existing = [];
        if ($key !== '' && $key !== $slug) {
            $row = $db->table('tbl_Settings')->where('`key`', 'cron_' . $key)->get()->getRow();
            if ($row) {
                $existing = json_decode($row->value ?? '{}', true) ?: [];
                if ($key !== $slug) {
                    $db->table('tbl_Settings')->where('`key`', 'cron_' . $key)->delete();
                }
            }
        } elseif ($key !== '' && $key === $slug) {
            $row = $db->table('tbl_Settings')->where('`key`', 'cron_' . $key)->get()->getRow();
            if ($row) {
                $existing = json_decode($row->value ?? '{}', true) ?: [];
            }
        }

        $job = [
            'name' => $name,
            'type' => $type,
            'schedule' => $schedule,
            'description' => $description,
            'enabled' => $enabled,
            'last_run' => $existing['last_run'] ?? null,
            'last_status' => $existing['last_status'] ?? null,
            'last_output' => $existing['last_output'] ?? null,
        ];

        $db->table('tbl_Settings')->upsert([
            'key' => 'cron_' . $slug,
            'value' => json_encode($job),
            'type' => 'json',
            'description' => 'Cron job: ' . $name,
        ]);

        return $this->respond([
            'status' => 'success',
            'message' => 'Cron job saved.',
            'job_key' => $slug,
        ]);
    }

    public function delete()
    {
        $key = trim((string) $this->request->getPost('job_key'));
        if ($key === '') {
            return $this->respond(['status' => 'error', 'message' => 'Missing job key.'], 400);
        }

        $db = \Config\Database::connect();
        $db->table('tbl_Settings')->where('`key`', 'cron_' . $key)->delete();

        return $this->respond(['status' => 'success', 'message' => 'Cron job deleted.']);
    }

    public function toggle()
    {
        $key = trim((string) $this->request->getPost('job_key'));
        $enabled = (bool) $this->request->getPost('enabled');

        $db = \Config\Database::connect();
        $row = $db->table('tbl_Settings')->where('`key`', 'cron_' . $key)->get()->getRow();
        if (!$row) {
            return $this->respond(['status' => 'error', 'message' => 'Cron job not found.'], 404);
        }

        $job = json_decode($row->value ?? '{}', true) ?: [];
        $job['enabled'] = $enabled;

        $db->table('tbl_Settings')->where('`key`', 'cron_' . $key)->update([
            'value' => json_encode($job),
        ]);

        return $this->respond([
            'status' => 'success',
            'message' => $enabled ? 'Cron job enabled.' : 'Cron job disabled.',
        ]);
    }

    public function run()
    {
        $key = trim((string) $this->request->getPost('job_key'));

        $db = \Config\Database::connect();
        $row = $db->table('tbl_Settings')->where('`key`', 'cron_' . $key)->get()->getRow();
        if (!$row) {
            return $this->respond(['status' => 'error', 'message' => 'Cron job not found.'], 404);
        }

        $job = json_decode($row->value ?? '{}', true) ?: [];
        if (empty($job['type'])) {
            return $this->respond(['status' => 'error', 'message' => 'Job has no type configured.'], 400);
        }

        $res = CronRunner::run($job['type']);

        $job['last_run'] = date('Y-m-d H:i:s');
        $job['last_status'] = $res['status'];
        $job['last_output'] = $res['output'];

        $db->table('tbl_Settings')->where('`key`', 'cron_' . $key)->update([
            'value' => json_encode($job),
        ]);

        CronLogger::log($key, $job['name'] ?? $key, $job['type'], $res['status'], $res['output'], 'manual');

        return $this->respond([
            'status' => $res['status'],
            'message' => $res['status'] === 'success' ? 'Job ran successfully.' : 'Job finished with errors.',
            'output' => $res['output'],
            'last_run' => $job['last_run'],
        ]);
    }

    public function output()
    {
        $key = trim((string) $this->request->getPost('job_key'));

        $db = \Config\Database::connect();
        $row = $db->table('tbl_Settings')->where('`key`', 'cron_' . $key)->get()->getRow();
        if (!$row) {
            return $this->respond(['status' => 'error', 'message' => 'Cron job not found.'], 404);
        }

        $job = json_decode($row->value ?? '{}', true) ?: [];

        return $this->respond([
            'status' => 'success',
            'output' => $job['last_output'] ?? 'No output recorded yet.',
            'last_run' => $job['last_run'] ?? null,
            'last_status' => $job['last_status'] ?? null,
            'name' => $job['name'] ?? $key,
        ]);
    }

    public function history()
    {
        $key = trim((string) $this->request->getPost('job_key'));
        if ($key === '') {
            return $this->respond(['status' => 'error', 'message' => 'Missing job key.'], 400);
        }

        $db = \Config\Database::connect();
        $row = $db->table('tbl_Settings')->where('`key`', 'cron_' . $key)->get()->getRow();
        $job = $row ? (json_decode($row->value ?? '{}', true) ?: []) : [];

        $runs = CronLogger::forJob($key, 25);
        foreach ($runs as &$run) {
            $run['ran_at_local'] = self::localTime($run['ran_at'] ?? null);
        }
        unset($run);

        return $this->respond([
            'status' => 'success',
            'name' => $job['name'] ?? $key,
            'key' => $key,
            'runs' => $runs,
        ]);
    }

    /**
     * Load cron jobs from tbl_Settings (cron_* keys), sorted by name.
     */
    private function loadJobs(): array
    {
        $db = \Config\Database::connect();
        $rows = $db->table('tbl_Settings')
            ->where('`key` LIKE', 'cron_%')
            ->get()
            ->getResultArray();

        $jobs = [];
        foreach ($rows as $row) {
            $key = str_replace('cron_', '', $row['key']);
            $job = json_decode($row['value'] ?? '{}', true) ?: [];
            $job['key'] = $key;
            $jobs[] = $job;
        }

        usort($jobs, static fn ($a, $b) => strcmp($a['name'] ?? '', $b['name'] ?? ''));

        return $jobs;
    }

    /**
     * Grouped job types for the modal dropdown.
     */
    private function jobSections(): array
    {
        $sections = [];
        foreach (CronRunner::types() as $type => $meta) {
            $sections[$meta['group']][] = [
                'type' => $type,
                'label' => $meta['label'],
                'desc' => $meta['desc'],
            ];
        }

        return $sections;
    }

    /**
     * Count jobs by type (for the jobs tab filter).
     *
     * @param array $jobs List of jobs from loadJobs()
     * @return array<string,int> Map of job_type => count
     */
    private function jobTypeCounts(array $jobs): array
    {
        $counts = [];
        foreach ($jobs as $job) {
            $type = $job['type'] ?? '';
            if ($type === '') continue;
            $counts[$type] = ($counts[$type] ?? 0) + 1;
        }
        return $counts;
    }

    /**
     * Validate a 5-field cron expression (basic sanity check).
     */
    private static function validSchedule(string $schedule): bool
    {
        $parts = preg_split('/\s+/', trim($schedule));
        if (count($parts) !== 5) {
            return false;
        }

        foreach ($parts as $i => $part) {
            $max = [59, 23, 31, 12, 7][$i];
            if (!preg_match('#^(\*|[0-9]+)(\/([0-9]+))?(-([0-9]+))?$#', $part)) {
                return false;
            }
            foreach (explode(',', $part) as $segment) {
                if (preg_match('#^\*/([0-9]+)$#', $segment, $m) && ((int) $m[1] === 0 || (int) $m[1] > $max)) {
                    return false;
                }
                if (preg_match('#^[0-9]+$#', $segment) && (int) $segment > $max) {
                    return false;
                }
                if (preg_match('#^[0-9]+-[0-9]+$#', $segment)) {
                    [$lo, $hi] = explode('-', $segment);
                    if ((int) $lo > $max || (int) $hi > $max || (int) $hi < (int) $lo) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Convert a UTC timestamp to the local (Africa/Nairobi) display string.
     */
    private static function localTime(?string $ts): string
    {
        if ($ts === null || $ts === '') {
            return '';
        }

        try {
            $dt = new \DateTimeImmutable($ts, new \DateTimeZone('UTC'));
            return $dt->setTimezone(new \DateTimeZone('Africa/Nairobi'))->format('d M Y, g:i A');
        } catch (\Throwable $e) {
            return $ts;
        }
    }
}
