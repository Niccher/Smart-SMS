<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Telemetry extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $metrics = $this->collectAllMetrics();

        $data = [
            'title'     => 'Container Telemetry - SuperAdmin',
            'bg_color'  => '#B1B8ED',
            'metrics'   => $metrics,
        ];

        return view('Admin/Telemetry/index', $data);
    }

    public function live()
    {
        return $this->response->setJSON($this->collectAllMetrics());
    }

    private function collectAllMetrics(): array
    {
        return [
            'status'    => 'ok',
            'timestamp' => date('c'),
            'web'       => $this->getWebAppMetrics(),
            'mysql'     => $this->getMySqlMetrics(),
            'ml'        => $this->getMlMetrics(),
        ];
    }

    private static ?array $cachedSpecs = null;
    private static ?array $cachedMySqlStatic = null;

    /**
     * Gather cached static specs (CPU core count, host RAM total, disk space)
     */
    private function getSystemSpecs(): array
    {
        if (self::$cachedSpecs !== null) {
            return self::$cachedSpecs;
        }

        $cpuCores = 1;
        if (is_readable('/proc/cpuinfo')) {
            $cpuinfo = @file_get_contents('/proc/cpuinfo');
            $cpuCores = max(1, substr_count((string)$cpuinfo, 'processor'));
        }

        $memTotalMb = 0;
        if (is_readable('/proc/meminfo')) {
            $meminfo = @file_get_contents('/proc/meminfo');
            if ($meminfo && preg_match('/MemTotal:\s+(\d+)\s+kB/', $meminfo, $m)) {
                $memTotalMb = round((int)$m[1] / 1024, 1);
            }
        }

        $containerMemLimitMb = null;
        if (is_readable('/sys/fs/cgroup/memory.max')) {
            $max = trim((string) @file_get_contents('/sys/fs/cgroup/memory.max'));
            if (is_numeric($max) && (float)$max < 9223372036854771712) {
                $containerMemLimitMb = round($max / 1048576, 1);
            }
        }
        if ($containerMemLimitMb === null && is_readable('/sys/fs/cgroup/memory/memory.limit_in_bytes')) {
            $max = trim((string) @file_get_contents('/sys/fs/cgroup/memory/memory.limit_in_bytes'));
            if (is_numeric($max) && (float)$max < 9223372036854771712) {
                $containerMemLimitMb = round($max / 1048576, 1);
            }
        }

        $rootTotalMb = @disk_total_space('/') ? round(@disk_total_space('/') / 1048576, 1) : 0;

        self::$cachedSpecs = [
            'cpu_cores'          => $cpuCores,
            'mem_total_mb'       => $memTotalMb,
            'container_limit_mb' => $containerMemLimitMb,
            'root_total_mb'      => $rootTotalMb,
        ];

        return self::$cachedSpecs;
    }

    /**
     * Gather WebApp Container (PHP/System) metrics with low overhead.
     */
    private function getWebAppMetrics(): array
    {
        $specs = $this->getSystemSpecs();
        $cpuCores = $specs['cpu_cores'];
        $memTotalMb = $specs['mem_total_mb'];
        $containerMemLimitMb = $specs['container_limit_mb'];
        $rootTotalMb = $specs['root_total_mb'];

        // 1. CPU & Load
        $load = function_exists('sys_getloadavg') ? sys_getloadavg() : [0, 0, 0];
        $loadPct = $cpuCores > 0 ? min(100, round(($load[0] / $cpuCores) * 100, 1)) : 0;

        // 2. Dynamic Memory Consumption
        $memAvailMb = 0;
        $memUsedMb = 0;
        if (is_readable('/proc/meminfo')) {
            $meminfo = @file_get_contents('/proc/meminfo');
            if ($meminfo) {
                if (preg_match('/MemAvailable:\s+(\d+)\s+kB/', $meminfo, $m) || preg_match('/MemFree:\s+(\d+)\s+kB/', $meminfo, $m)) {
                    $memAvailMb = round((int)$m[1] / 1024, 1);
                    $memUsedMb = max(0, round($memTotalMb - $memAvailMb, 1));
                }
            }
        }

        // cgroup container memory usage
        $containerMemUsedMb = null;
        if (is_readable('/sys/fs/cgroup/memory.current')) {
            $cur = trim((string) @file_get_contents('/sys/fs/cgroup/memory.current'));
            if (is_numeric($cur)) $containerMemUsedMb = round($cur / 1048576, 1);
        } elseif (is_readable('/sys/fs/cgroup/memory/memory.usage_in_bytes')) {
            $cur = trim((string) @file_get_contents('/sys/fs/cgroup/memory/memory.usage_in_bytes'));
            if (is_numeric($cur)) $containerMemUsedMb = round($cur / 1048576, 1);
        }

        // 3. PHP Engine Memory
        $phpMemAllocatedMb = round(memory_get_usage(true) / 1048576, 1);
        $phpMemPeakMb      = round(memory_get_peak_usage(true) / 1048576, 1);
        $phpMemLimit       = ini_get('memory_limit');

        // 4. Disk Storage
        $rootFreeMb  = @disk_free_space('/') ? round(@disk_free_space('/') / 1048576, 1) : 0;
        $rootUsedMb  = max(0, $rootTotalMb - $rootFreeMb);
        $rootUsedPct = $rootTotalMb > 0 ? round(($rootUsedMb / $rootTotalMb) * 100, 1) : 0;

        // 5. Uptime
        $uptimeSeconds = 0;
        if (is_readable('/proc/uptime')) {
            $up = @file_get_contents('/proc/uptime');
            if ($up) $uptimeSeconds = (int) explode(' ', trim($up))[0];
        }

        // 6. Active sessions count (cached for 30s to prevent disk thrashing)
        $sessionCount = 0;
        try {
            $sessionCount = (int) cache()->remember('telemetry_active_sessions', 30, function() {
                $sessionPath = WRITEPATH . 'session';
                if (is_dir($sessionPath)) {
                    $files = @scandir($sessionPath);
                    if ($files) return max(0, count($files) - 2);
                }
                return 0;
            });
        } catch (\Throwable) {
            $sessionCount = 0;
        }

        $cpuIdlePct = max(0, round(100 - $loadPct, 1));
        $effectiveUsedMb = $containerMemUsedMb ?? $memUsedMb;
        $containerUsedPct = ($containerMemLimitMb && $containerMemLimitMb > 0)
            ? min(100, round(($effectiveUsedMb / $containerMemLimitMb) * 100, 1))
            : ($memTotalMb > 0 ? round(($memUsedMb / $memTotalMb) * 100, 1) : 0);

        $containerFreeMb = ($containerMemLimitMb && $containerMemLimitMb > 0)
            ? max(0, round($containerMemLimitMb - $effectiveUsedMb, 1))
            : ($memTotalMb > 0 ? max(0, round($memTotalMb - $memUsedMb, 1)) : 0);

        // OOM Risk warning if container is approaching Railway's limit (>80%)
        $oomWarning = ($containerMemLimitMb && $containerMemLimitMb > 0) && ($containerUsedPct >= 80);

        return [
            'status'             => 'online',
            'uptime_seconds'     => $uptimeSeconds,
            'uptime_formatted'   => $this->formatUptime($uptimeSeconds),
            'cpu' => [
                'cores'    => $cpuCores,
                'load_1m'  => round($load[0], 2),
                'load_5m'  => round($load[1], 2),
                'load_15m' => round($load[2], 2),
                'load_pct' => $loadPct,
                'idle_pct' => $cpuIdlePct,
            ],
            'memory' => [
                'host_total_mb'      => $memTotalMb,
                'host_used_mb'       => $memUsedMb,
                'host_avail_mb'      => $memAvailMb,
                'host_used_pct'      => $memTotalMb > 0 ? round(($memUsedMb / $memTotalMb) * 100, 1) : 0,
                'container_used_mb'  => $effectiveUsedMb,
                'container_limit_mb' => $containerMemLimitMb,
                'container_free_mb'  => $containerFreeMb,
                'container_used_pct' => $containerUsedPct,
                'container_oom_warning' => $oomWarning,
                'php_allocated_mb'   => $phpMemAllocatedMb,
                'php_peak_mb'        => $phpMemPeakMb,
                'php_limit'          => $phpMemLimit,
            ],
            'disk' => [
                'total_mb' => $rootTotalMb,
                'used_mb'  => $rootUsedMb,
                'free_mb'  => $rootFreeMb,
                'used_pct' => $rootUsedPct,
                'free_pct' => max(0, round(100 - $rootUsedPct, 1)),
            ],
            'runtime' => [
                'php_version' => PHP_VERSION,
                'sapi'        => php_sapi_name(),
                'server'      => $_SERVER['SERVER_SOFTWARE'] ?? 'Nginx / Web Container',
                'sessions'    => $sessionCount,
            ]
        ];
    }

    /**
     * Gather MySQL Database metrics with minimal query footprint.
     */
    private function getMySqlMetrics(): array
    {
        try {
            $start = microtime(true);
            $db = \Config\Database::connect();
            $schema = $db->database;

            // 1. Static MySQL variables (cached for 10 minutes to avoid reading ~600 rows repeatedly)
            if (self::$cachedMySqlStatic === null) {
                try {
                    $vars = cache()->remember('telemetry_mysql_static_vars', 600, function() use ($db) {
                        $rows = $db->query("SHOW VARIABLES WHERE Variable_name IN ('max_connections', 'innodb_buffer_pool_size', 'version', 'version_comment')")->getResultArray();
                        $map = [];
                        foreach ($rows as $r) {
                            $map[$r['Variable_name']] = $r['Value'];
                        }
                        return $map;
                    });
                    self::$cachedMySqlStatic = $vars ?: [];
                } catch (\Throwable) {
                    self::$cachedMySqlStatic = [];
                }
            }
            $variables = self::$cachedMySqlStatic;

            // 2. Targeted GLOBAL STATUS (only 9 critical metrics instead of dumping ~500 rows)
            $statusRows = $db->query(
                "SHOW GLOBAL STATUS WHERE Variable_name IN (
                    'Uptime', 'Questions', 'Queries', 'Threads_connected', 'Threads_running',
                    'Max_used_connections', 'Innodb_buffer_pool_bytes_data',
                    'Slow_queries', 'Aborted_connects'
                )"
            )->getResultArray();
            $queryLatencyMs = round((microtime(true) - $start) * 1000, 1);

            $status = [];
            foreach ($statusRows as $row) {
                $status[$row['Variable_name']] = $row['Value'];
            }

            // 3. Storage & Tables info (cached for 60 seconds to avoid locking metadata)
            $statsRow = [];
            try {
                $statsRow = cache()->remember('telemetry_mysql_db_stats_' . $schema, 60, function() use ($db, $schema) {
                    return $db->query(
                        "SELECT
                            COUNT(*) AS table_count,
                            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb,
                            ROUND(SUM(data_length) / 1024 / 1024, 2) AS data_mb,
                            ROUND(SUM(index_length) / 1024 / 1024, 2) AS index_mb,
                            COALESCE(SUM(table_rows), 0) AS approx_rows
                        FROM information_schema.tables
                        WHERE table_schema = ?",
                        [$schema]
                    )->getRowArray() ?: [];
                });
            } catch (\Throwable) {}

            $uptime = (int) ($status['Uptime'] ?? 0);
            $questions = (int) ($status['Questions'] ?? $status['Queries'] ?? 0);
            $qps = $uptime > 0 ? round($questions / $uptime, 2) : 0;

            // Connections
            $connected = (int) ($status['Threads_connected'] ?? 0);
            $running   = (int) ($status['Threads_running'] ?? 0);
            $maxConn   = (int) ($variables['max_connections'] ?? 151);
            $connPct   = $maxConn > 0 ? min(100, round(($connected / $maxConn) * 100, 1)) : 0;
            $freeConn  = max(0, $maxConn - $connected);

            // InnoDB Buffer Pool
            $bpSize = (int) ($variables['innodb_buffer_pool_size'] ?? 134217728);
            $bpData = (int) ($status['Innodb_buffer_pool_bytes_data'] ?? 0);
            $bpSizeMb = round($bpSize / 1048576, 1);
            $bpDataMb = round($bpData / 1048576, 1);
            $bpUsedPct = $bpSize > 0 ? min(100, round(($bpData / $bpSize) * 100, 1)) : 0;
            $bpFreeMb = max(0, round($bpSizeMb - $bpDataMb, 1));

            return [
                'status'           => 'online',
                'latency_ms'       => $queryLatencyMs,
                'version'          => $variables['version'] ?? 'Unknown',
                'version_comment'  => $variables['version_comment'] ?? '',
                'uptime_seconds'   => $uptime,
                'uptime_formatted' => $this->formatUptime($uptime),
                'database'         => $schema,
                'tables_count'     => (int) ($statsRow['table_count'] ?? 0),
                'database_size_mb' => (float) ($statsRow['size_mb'] ?? 0),
                'approx_rows'      => (int) ($statsRow['approx_rows'] ?? 0),
                'connections' => [
                    'connected'    => $connected,
                    'free'         => $freeConn,
                    'running'      => $running,
                    'max'          => $maxConn,
                    'used_pct'     => $connPct,
                    'max_used'     => (int) ($status['Max_used_connections'] ?? $connected),
                    'aborted'      => (int) ($status['Aborted_connects'] ?? 0),
                ],
                'throughput' => [
                    'questions'    => $questions,
                    'qps'          => $qps,
                    'slow_queries' => (int) ($status['Slow_queries'] ?? 0),
                ],
                'buffer_pool' => [
                    'size_mb'      => $bpSizeMb,
                    'data_mb'      => $bpDataMb,
                    'free_mb'      => $bpFreeMb,
                    'used_pct'     => $bpUsedPct,
                ]
            ];
        } catch (\Throwable $e) {
            return [
                'status'     => 'offline',
                'error'      => $e->getMessage(),
                'latency_ms' => 0,
            ];
        }
    }

    /**
     * Gather ML Engine Container metrics from FastAPI.
     */
    private function getMlMetrics(): array
    {
        $baseUrl = rtrim((string) config('MlBackend')->baseUrl, '/');
        $url = $baseUrl . '/admin/telemetry';

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

            $start = microtime(true);
            $res = curl_exec($ch);
            $latencyMs = round((microtime(true) - $start) * 1000, 1);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);

            if ($curlErr || $httpCode !== 200) {
                // Fallback to basic /health or /admin/status if /telemetry endpoint isn't deployed yet
                return $this->getMlFallbackMetrics($baseUrl, $curlErr ?: ("HTTP " . $httpCode));
            }

            $data = json_decode($res, true);
            if (!is_array($data) || ($data['status'] ?? '') !== 'ok') {
                return $this->getMlFallbackMetrics($baseUrl, 'Invalid telemetry JSON response');
            }

            $data['status']     = 'online';
            $data['latency_ms'] = $latencyMs;
            $data['uptime_formatted'] = $this->formatUptime((int)($data['uptime_seconds'] ?? 0));

            return $data;
        } catch (\Throwable $e) {
            return [
                'status'     => 'offline',
                'error'      => $e->getMessage(),
                'latency_ms' => 0,
            ];
        }
    }

    private function getMlFallbackMetrics(string $baseUrl, string $originalError): array
    {
        try {
            $ch = curl_init($baseUrl . '/admin/status');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $res) {
                $status = json_decode($res, true);
                return [
                    'status' => 'online',
                    'latency_ms' => 50,
                    'cpu' => ['cores' => 1, 'load_1m' => 0, 'load_pct' => 0],
                    'memory' => ['host_total_mb' => 0, 'host_used_mb' => 0, 'container_used_mb' => 0],
                    'gpu' => ['has_gpu' => false, 'mode' => 'CPU Inference (AVX/OpenMP)'],
                    'inference' => [
                        'engine' => $status['app']['llm_engine'] ?? 'local_gguf',
                        'active_model' => basename($status['app']['model_path'] ?? 'qwen2.5-1.5b-instruct-q4_k_m.gguf'),
                        'llama_running' => ($status['llama'] ?? '') === 'ok',
                        'ctx_size' => $status['app']['llm_ctx_size'] ?? 16384,
                    ],
                    'queue' => ['active_jobs' => 0, 'queued_jobs' => 0, 'completed_today' => 0]
                ];
            }
        } catch (\Throwable) {}

        return [
            'status'     => 'offline',
            'error'      => $originalError,
            'latency_ms' => 0,
        ];
    }

    private function formatUptime(int $seconds): string
    {
        if ($seconds <= 0) return '0s';
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $mins = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        $parts = [];
        if ($days > 0) $parts[] = "{$days}d";
        if ($hours > 0) $parts[] = "{$hours}h";
        if ($mins > 0) $parts[] = "{$mins}m";
        if ($days == 0 && count($parts) < 2) $parts[] = "{$secs}s";

        return implode(' ', $parts);
    }
}
