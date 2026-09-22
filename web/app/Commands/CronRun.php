<?php

namespace App\Commands;

use App\Libraries\CronLogger;
use App\Libraries\CronRunner;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CronRun extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'cron:run';
    protected $description = 'Run any enabled scheduled cron jobs that are due. Executed every minute by the in-container cron daemon.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        $jobs = $db->table('tbl_Settings')
            ->where('`key` LIKE', 'cron_%')
            ->get()
            ->getResultArray();

        $executed = 0;
        $skipped = 0;

        foreach ($jobs as $row) {
            $job = json_decode($row['value'] ?? '{}', true);
            if (empty($job) || empty($job['enabled']) || empty($job['type']) || empty($job['schedule'])) {
                continue;
            }

            if (!CronRunner::cronMatches($job['schedule'])) {
                $skipped++;
                continue;
            }

            $res = CronRunner::run($job['type']);

            $db->table('tbl_Settings')->upsert([
                'key' => $row['key'],
                'value' => json_encode(array_merge($job, [
                    'last_run' => date('Y-m-d H:i:s'),
                    'last_status' => $res['status'],
                    'last_output' => $res['output'],
                ])),
                'type' => 'json',
                'description' => $row['description'],
            ]);

            $name = $job['name'] ?? $row['key'];
            CLI::write("[{$name}] {$res['status']} - " . strtok($res['output'], "\n"), $res['status'] === 'success' ? 'green' : 'red');
            $executed++;

            CronLogger::log(str_replace('cron_', '', $row['key']), $name, $job['type'] ?? '', $res['status'], $res['output'], 'scheduler');
        }

        CLI::write("Cron check complete. Executed: {$executed}, scanned: " . count($jobs) . '.', 'green');
    }
}
