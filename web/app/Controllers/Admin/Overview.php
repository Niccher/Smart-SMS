<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Overview extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $db = \Config\Database::connect();
        $mlBase = (string) config('MlBackend')->baseUrl;

        // ── System stats ─────────────────────────────────────
        $users = $db->table('users')->countAllResults();
        $uploads = $db->tableExists('tbl_Loot') ? $db->table('tbl_Loot')->countAllResults() : 0;
        $sms = $db->tableExists('tbl_Sms') ? $db->table('tbl_Sms')->countAllResults() : 0;
        $transactions = $db->tableExists('tbl_Analyzed_Transactions') ? $db->table('tbl_Analyzed_Transactions')->countAllResults() : 0;
        $processingJobs = $db->tableExists('tbl_Processing_Jobs') ? $db->table('tbl_Processing_Jobs')->countAllResults() : 0;
        $queuedJobs = $db->tableExists('tbl_Processing_Jobs') ? $db->table('tbl_Processing_Jobs')->where('status', 'queued')->countAllResults() : 0;

        // DB size
        $dbSize = $db->query("
            SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
        ")->getRow();

        // ── Maintenance status ────────────────────────────────
        $maintenance = $db->table('tbl_Settings')
            ->whereIn('`key`', ['maintenance_mode', 'maintenance_scheduled'])
            ->get()
            ->getResultArray();
        $maintenanceMode = false;
        $maintenanceScheduled = false;
        foreach ($maintenance as $s) {
            if ($s['key'] === 'maintenance_mode') $maintenanceMode = filter_var($s['value'], FILTER_VALIDATE_BOOLEAN);
            if ($s['key'] === 'maintenance_scheduled') $maintenanceScheduled = filter_var($s['value'], FILTER_VALIDATE_BOOLEAN);
        }

        // ── ML backend status ─────────────────────────────────
        $ml = $this->checkMl($mlBase);

        // ── Recent processing jobs ────────────────────────────
        $recentJobs = [];
        if ($db->tableExists('tbl_Processing_Jobs')) {
            $recentJobs = $db->table('tbl_Processing_Jobs')
                ->orderBy('id', 'DESC')
                ->limit(8)
                ->get()
                ->getResultArray();
        }

        // ── Recent registered users ───────────────────────────
        $recentUsers = $db->table('users')
            ->select('users.id, users.username, users.active, users.created_at, ag.group')
            ->join('auth_groups_users ag', 'ag.user_id = users.id', 'left')
            ->orderBy('users.id', 'DESC')
            ->limit(8)
            ->get()
            ->getResultArray();

        $data = [
            'bg_color' => '#B1B8ED',
            'stats' => [
                'users' => $users,
                'uploads' => $uploads,
                'sms' => $sms,
                'transactions' => $transactions,
                'processing_jobs' => $processingJobs,
                'queued_jobs' => $queuedJobs,
                'db_size_mb' => $dbSize->size_mb ?? '0',
            ],
            'maintenance_mode' => $maintenanceMode,
            'maintenance_scheduled' => $maintenanceScheduled,
            'ml' => $ml,
            'recent_jobs' => $recentJobs,
            'recent_users' => $recentUsers,
        ];

        return view('Admin/Overview/index', $data);
    }

    private function checkMl(string $mlBase): array
    {
        try {
            $client = \Config\Services::curlrequest();
            $start = microtime(true);
            $resp = $client->get($mlBase . '/admin/status', ['timeout' => 5]);
            $latency = round((microtime(true) - $start) * 1000);
            $body = json_decode($resp->getBody(), true);

            return [
                'reachable' => true,
                'status' => $body['status'] ?? 'unknown',
                'llama' => $body['llama'] ?? 'unknown',
                'db_configured' => $body['db_configured'] ?? false,
                'llm_model' => $body['app']['llm_model'] ?? 'unknown',
                'llm_provider' => $body['app']['llm_provider'] ?? 'unknown',
                'batch_size' => $body['app']['batch_size'] ?? '?',
                'max_tokens' => $body['app']['llm_max_tokens'] ?? '?',
                'poll_interval' => $body['app']['poll_interval'] ?? '?',
                'latency_ms' => $latency,
                'models' => $body['models'] ?? [],
                'error' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'reachable' => false,
                'error' => $e->getMessage(),
                'latency_ms' => 0,
            ];
        }
    }
}
