<?php

namespace App\Libraries;

class CronRunner
{
    /**
     * All available cron job types (canonical list shared by the UI and the scheduler).
     */
    public static function types(): array
    {
        return [
            'llm:process' => [
                'group' => 'Spark Commands',
                'label' => 'Process Pending LLM Jobs',
                'desc' => 'Triggers the ML backend for each queued analysis job (php spark llm:process).',
            ],
            'session:gc' => [
                'group' => 'Spark Commands',
                'label' => 'Clean Expired Sessions',
                'desc' => 'Removes expired sessions, remember tokens and stale token logins (php spark session:gc).',
            ],
            'uploads:cleanup' => [
                'group' => 'Spark Commands',
                'label' => 'Clean Orphaned Uploads',
                'desc' => 'Removes orphaned upload files and old loot records (php spark uploads:cleanup).',
            ],
            'data:retention' => [
                'group' => 'Spark Commands',
                'label' => 'Enforce Data Retention',
                'desc' => 'Deletes uploaded files and DB rows older than the admin-set retention window (php spark data:retention).',
            ],
            'reports:send' => [
                'group' => 'Spark Commands',
                'label' => 'Send Scheduled Reports',
                'desc' => 'Sends scheduled spending reports to users who have them enabled (php spark reports:send).',
            ],
            'email:process' => [
                'group' => 'Spark Commands',
                'label' => 'Process Deferred Email Queue',
                'desc' => 'Processes pending email queue entries and retries failed email sends (php spark email:process).',
            ],
            'db:backup' => [
                'group' => 'Database',
                'label' => 'Create Database Backup',
                'desc' => 'Dumps the full database to writable/backups as a SQL file.',
            ],
            'db:check' => [
                'group' => 'Database',
                'label' => 'Check MySQL Health',
                'desc' => 'Reports MySQL version, uptime, connections, size and table count.',
            ],
            'ml:check' => [
                'group' => 'Checks',
                'label' => 'Check ML Backend',
                'desc' => 'Pings the ML backend /admin/status endpoint and reports its state.',
            ],
            'maintenance:check' => [
                'group' => 'Checks',
                'label' => 'Check Maintenance Mode',
                'desc' => 'Reports whether maintenance mode is currently active or scheduled.',
            ],
        ];
    }

    /**
     * Execute a job type and return [status, output].
     */
    public static function run(string $type): array
    {
        if (!array_key_exists($type, self::types())) {
            return ['status' => 'error', 'output' => "Unknown job type: {$type}"];
        }

        try {
            switch ($type) {
                case 'llm:process':
                case 'session:gc':
                case 'uploads:cleanup':
                case 'data:retention':
                case 'reports:send':
                case 'email:process':
                    return self::runSpark($type);

                case 'db:backup':
                    return self::backupDatabase();

                case 'db:check':
                    return self::checkDatabase();

                case 'ml:check':
                    return self::checkMlBackend();

                case 'maintenance:check':
                    return self::checkMaintenance();

                default:
                    return ['status' => 'error', 'output' => "Unhandled job type: {$type}"];
            }
        } catch (\Throwable $e) {
            return ['status' => 'error', 'output' => $e->getMessage()];
        }
    }

    /**
     * Run a spark command inside the container and capture its output.
     */
    private static function runSpark(string $type): array
    {
        $output = [];
        $code = 1;
        $cmd = 'cd ' . escapeshellarg(FCPATH) . ' && php spark ' . escapeshellarg($type) . ' 2>&1';
        exec($cmd, $output, $code);

        return [
            'status' => $code === 0 ? 'success' : 'error',
            'output' => implode("\n", $output),
        ];
    }

    private static function backupDatabase(): array
    {
        $db = \Config\Database::connect();
        $schema = $db->database;

        $dir = WRITEPATH . 'backups';
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return ['status' => 'error', 'output' => "Could not create backup directory: {$dir}"];
        }

        $file = $dir . '/db_' . $schema . '_' . date('Ymd_His') . '.sql';
        $out = "-- Mpesa Analyzer Database Backup\n";
        $out .= "-- Schema: {$schema}\n";
        $out .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";

        $tables = $db->listTables();
        $tableCount = 0;

        foreach ($tables as $table) {
            $create = $db->query("SHOW CREATE TABLE `{$table}`")->getRowArray();
            if (!$create) {
                continue;
            }

            $out .= "-- ------------------------------------------------------------\n";
            $out .= "-- Table: {$table}\n";
            $out .= "-- ------------------------------------------------------------\n";
            $out .= $create['Create Table'] . ";\n\n";
            $tableCount++;

            $rows = $db->table($table)->get()->getResultArray();
            if (empty($rows)) {
                continue;
            }

            $fields = array_keys($rows[0]);
            $colList = '`' . implode('`, `', $fields) . '`';

            foreach (array_chunk($rows, 200) as $chunk) {
                $values = [];
                foreach ($chunk as $row) {
                    $cells = [];
                    foreach ($fields as $field) {
                        $val = $row[$field] ?? null;
                        $cells[] = $val === null ? 'NULL' : $db->escape($val);
                    }
                    $values[] = '(' . implode(', ', $cells) . ')';
                }
                $out .= "INSERT INTO `{$table}` ({$colList}) VALUES\n" . implode(",\n", $values) . ";\n\n";
            }
        }

        file_put_contents($file, $out);

        // Retention: keep the latest 30 backups.
        $files = glob($dir . '/db_*.sql') ?: [];
        usort($files, static fn ($a, $b) => filemtime($b) <=> filemtime($a));
        $removed = 0;
        foreach (array_slice($files, 30) as $old) {
            if (@unlink($old)) {
                $removed++;
            }
        }

        $sizeKb = round(filesize($file) / 1024, 2);

        return [
            'status' => 'success',
            'output' => "Backup created: {$file}\nTables dumped: {$tableCount}\nSize: {$sizeKb} KB\nOld backups removed: {$removed}",
        ];
    }

    private static function checkDatabase(): array
    {
        $db = \Config\Database::connect();

        $version = $db->query('SELECT VERSION() AS v')->getRow()->v ?? 'Unknown';
        $schema = $db->database;

        $uptimeRow = $db->query("SHOW GLOBAL STATUS LIKE 'Uptime'")->getRowArray();
        $threadsRow = $db->query("SHOW GLOBAL STATUS LIKE 'Threads_connected'")->getRowArray();
        $maxThreadsRow = $db->query("SHOW GLOBAL STATUS LIKE 'Max_used_connections'")->getRowArray();

        $uptime = isset($uptimeRow['Value']) ? self::formatUptime((int) $uptimeRow['Value']) : 'Unknown';
        $threads = $threadsRow['Value'] ?? '?';
        $maxThreads = $maxThreadsRow['Value'] ?? '?';

        $tables = count($db->listTables());
        $size = $db->query(
            "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS mb
             FROM information_schema.tables WHERE table_schema = DATABASE()"
        )->getRow()->mb ?? '0';

        return [
            'status' => 'success',
            'output' => implode("\n", [
                'MySQL status: OK',
                "Version: {$version}",
                "Database: {$schema}",
                "Uptime: {$uptime}",
                "Connections: {$threads} (max used: {$maxThreads})",
                "Tables: {$tables}",
                "Total size: {$size} MB",
            ]),
        ];
    }

    private static function checkMlBackend(): array
    {
        $mlBase = (string) config('MlBackend')->baseUrl;
        $client = \Config\Services::curlrequest();

        $start = microtime(true);
        $resp = $client->get($mlBase . '/admin/status', ['timeout' => 10]);
        $latencyMs = round((microtime(true) - $start) * 1000);
        $body = json_decode($resp->getBody(), true);

        $lines = [
            "ML backend reachable ({$mlBase})",
            "Latency: {$latencyMs} ms",
            'Status: ' . ($body['status'] ?? 'unknown'),
            'Llama: ' . ($body['llama'] ?? 'unknown'),
            'DB configured: ' . ($body['db_configured'] ?? false ? 'yes' : 'no'),
            'Model: ' . ($body['app']['llm_model'] ?? 'unknown'),
            'Provider: ' . ($body['app']['llm_provider'] ?? 'unknown'),
        ];

        return ['status' => 'success', 'output' => implode("\n", $lines)];
    }

    private static function checkMaintenance(): array
    {
        $db = \Config\Database::connect();

        $rows = $db->table('tbl_Settings')
            ->whereIn('`key`', ['maintenance_mode', 'maintenance_scheduled', 'maintenance_schedule_start', 'maintenance_schedule_end'])
            ->get()
            ->getResultArray();

        $config = [];
        foreach ($rows as $r) {
            $config[$r['key']] = $r['value'];
        }

        $active = filter_var($config['maintenance_mode'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $scheduled = filter_var($config['maintenance_scheduled'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $start = str_replace('T', ' ', $config['maintenance_schedule_start'] ?? '');
        $end = str_replace('T', ' ', $config['maintenance_schedule_end'] ?? '');
        $now = gmdate('Y-m-d H:i:s');

        $lines = ['Maintenance mode: ' . ($active ? 'ACTIVE' : 'off')];
        if ($scheduled) {
            $inWindow = $start !== '' && $end !== '' && $now >= $start && $now <= $end;
            $lines[] = 'Scheduled window: ' . ($inWindow ? 'active now' : 'not active');
            $lines[] = "Window: {$start} to {$end} (UTC)";
        }

        return ['status' => 'success', 'output' => implode("\n", $lines)];
    }

    /**
     * Match a 5-field cron expression against a given time (defaults to now).
     */
    public static function cronMatches(string $schedule, ?int $timestamp = null): bool
    {
        $ts = $timestamp ?? time();
        $parts = preg_split('/\s+/', trim($schedule));
        if (count($parts) !== 5) {
            return false;
        }

        [$minute, $hour, $day, $month, $dow] = $parts;

        if (!self::fieldMatches($minute, (int) date('i', $ts))) return false;
        if (!self::fieldMatches($hour, (int) date('G', $ts))) return false;
        if (!self::fieldMatches($day, (int) date('j', $ts))) return false;
        if (!self::fieldMatches($month, (int) date('n', $ts))) return false;

        // Day of week: cron allows 0-7 where 0 and 7 are Sunday.
        $dowValue = (int) date('w', $ts); // 0 (Sun) - 6 (Sat)
        if (!self::dowMatches($dow, $dowValue)) return false;

        return true;
    }

    /**
     * Human-readable label for a cron expression (for common presets).
     */
    public static function describe(string $schedule): string
    {
        $map = [
            '* * * * *' => 'Every minute',
            '*/5 * * * *' => 'Every 5 minutes',
            '*/10 * * * *' => 'Every 10 minutes',
            '*/15 * * * *' => 'Every 15 minutes',
            '*/30 * * * *' => 'Every 30 minutes',
            '0 * * * *' => 'Every hour',
            '0 2 * * *' => 'Daily at 2:00 AM',
            '0 6 * * *' => 'Daily at 6:00 AM',
            '0 6 * * 1' => 'Weekly on Monday at 6:00 AM',
            '0 3 * * 0' => 'Weekly on Sunday at 3:00 AM',
        ];

        return $map[$schedule] ?? ('Cron: ' . $schedule);
    }

    private static function fieldMatches(string $field, int $value): bool
    {
        foreach (explode(',', $field) as $part) {
            $part = trim($part);

            if ($part === '*') {
                return true;
            }
            if (preg_match('#^\*/(\d+)$#', $part, $m)) {
                $step = (int) $m[1];
                if ($step > 0 && $value % $step === 0) {
                    return true;
                }
                continue;
            }
            if (preg_match('#^(\d+)-(\d+)$#', $part, $m)) {
                if ($value >= (int) $m[1] && $value <= (int) $m[2]) {
                    return true;
                }
                continue;
            }
            if (ctype_digit($part) && (int) $part === $value) {
                return true;
            }
        }

        return false;
    }

    private static function dowMatches(string $field, int $value): bool
    {
        // Normalize 7 -> 0 (both are Sunday in cron).
        foreach (explode(',', $field) as $part) {
            $part = trim($part);

            if ($part === '*') {
                return true;
            }
            if (preg_match('#^\*/(\d+)$#', $part, $m)) {
                $step = (int) $m[1];
                if ($step > 0 && $value % $step === 0) {
                    return true;
                }
                continue;
            }
            if (preg_match('#^(\d+)-(\d+)$#', $part, $m)) {
                $lo = (int) $m[1];
                $hi = (int) $m[2];
                // Cron range may be like 5-7 meaning Fri-Sun(7); normalize 7 to 0.
                if ($hi === 7) {
                    if ($value === 0 || ($value >= $lo && $value <= 6)) {
                        return true;
                    }
                    continue;
                }
                if ($value >= $lo && $value <= $hi) {
                    return true;
                }
                continue;
            }
            $d = (int) $part === 7 ? 0 : (int) $part;
            if ($d === $value) {
                return true;
            }
        }

        return false;
    }

    private static function formatUptime(int $seconds): string
    {
        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        $mins = intdiv($seconds % 3600, 60);

        if ($days > 0) {
            return "{$days}d {$hours}h {$mins}m";
        }

        return "{$hours}h {$mins}m";
    }
}
