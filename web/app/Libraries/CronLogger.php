<?php

namespace App\Libraries;

/**
 * Records and reads cron job run history in tbl_Cron_Log.
 *
 * Every execution of a scheduled job (from the scheduler daemon or a manual
 * "Run now") is written here so the admin panel can show a run history and
 * the last output of each job.
 */
class CronLogger
{
    private const TABLE = 'tbl_Cron_Log';

    /**
     * Record a cron run.
     */
    public static function log(
        string $jobKey,
        string $jobName,
        string $jobType,
        string $status,
        string $output,
        string $trigger = 'scheduler'
    ): void {
        $db = \Config\Database::connect();
        if (!$db->tableExists(self::TABLE)) {
            return;
        }

        $db->table(self::TABLE)->insert([
            'job_key'  => $jobKey,
            'job_name' => $jobName,
            'job_type' => $jobType,
            'status'   => $status === 'success' ? 'success' : 'error',
            'output'   => $output,
            'trigger'  => $trigger === 'manual' ? 'manual' : 'scheduler',
            'ran_at'   => gmdate('Y-m-d H:i:s'),
        ]);

        // Retention: keep the latest 500 run records.
        $db->query(
            'DELETE FROM ' . $db->prefixTable(self::TABLE) . ' WHERE id NOT IN (SELECT id FROM (SELECT id FROM ' . $db->prefixTable(self::TABLE) . ' ORDER BY id DESC LIMIT 500) keep)'
        );
    }

    /**
     * Most recent runs across all jobs.
     *
     * @return array
     */
    public static function recent(int $limit = 100): array
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists(self::TABLE)) {
            return [];
        }

        return $db->table(self::TABLE)
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Count of runs grouped by job_type (for the filter dropdown).
     *
     * @return array<string,int>  Map of job_type => count
     */
    public static function typeCounts(): array
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists(self::TABLE)) {
            return [];
        }

        $rows = $db->table(self::TABLE)
            ->select('job_type, COUNT(*) AS cnt')
            ->groupBy('job_type')
            ->get()
            ->getResultArray();

        $counts = [];
        foreach ($rows as $row) {
            $type = (string)($row['job_type'] ?? '');
            $counts[$type] = (int)($row['cnt'] ?? 0);
        }
        return $counts;
    }

    /**
     * Most recent runs for a single job.
     *
     * @return array
     */
    public static function forJob(string $jobKey, int $limit = 25): array
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists(self::TABLE)) {
            return [];
        }

        return $db->table(self::TABLE)
            ->where('job_key', $jobKey)
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}
