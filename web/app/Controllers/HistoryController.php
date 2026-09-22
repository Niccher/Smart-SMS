<?php

namespace App\Controllers;

class HistoryController extends BaseController
{
    public function index()
    {
        helper('mpesa_date');
        $hist = $this->getHistoryData();
        $db = \Config\Database::connect();
        
        $jobs = [];
        if ($db->tableExists('tbl_Processing_Jobs')) {
            $jobs = $db->table('tbl_Processing_Jobs')
                ->where('user_id', auth()->user()->id)
                ->get()
                ->getResultArray();
        }

        $data = array_merge($hist, [
            'active_tab' => 'uploads',
            'jobs'       => $jobs,
            'bg_color'   => '#B1B8ED'
        ]);

        return view('Dash/history', $data);
    }

    /**
     * ML Jobs tab: this user's processing jobs with full metadata.
     */
    public function jobs()
    {
        helper('mpesa_date');
        $hist = $this->getHistoryData();

        $data = array_merge($hist, [
            'active_tab' => 'jobs',
            'jobs'       => $this->fetchUserJobs(),
            'bg_color'   => '#B1B8ED'
        ]);

        return view('Dash/history', $data);
    }

    private function getHistoryData(): array
    {
        $db = \Config\Database::connect();
        $userId = auth()->user()->id;
        $tokenType = \CodeIgniter\Shield\Authentication\Authenticators\AccessTokens::ID_TYPE_ACCESS_TOKEN;

        $tokenRows = $db->query("
            SELECT DISTINCT s.sms_owner AS tk FROM tbl_Sms s
            INNER JOIN auth_identities i ON i.secret = SHA2(s.sms_owner, 256)
            WHERE i.user_id = ? AND i.type = ?
            UNION
            SELECT DISTINCT l.loot_Owner AS tk FROM tbl_Loot l
            INNER JOIN auth_identities i ON i.secret = SHA2(l.loot_Owner, 256)
            WHERE i.user_id = ? AND i.type = ?
        ", [$userId, $tokenType, $userId, $tokenType])->getResult();

        $rawTokens = array_values(array_filter(array_map(fn($r) => $r->tk ?? '', $tokenRows)));

        $batches = [];
        $totalSmsAll = 0;
        $totalClassifiedAll = 0;
        $totalAmountAll = 0;

        $stats = [
            'all_sms'        => 0,
            'finance_sms'    => 0,
            'non_finance_sms'=> 0,
            'unclassified'   => 0,
            'all_senders'    => 0,
            'finance_senders'=> 0,
            'non_finance_senders' => 0,
            'banks'          => 0,
            'sent'           => 0,
            'received'       => 0,
            'none'           => 0,
            'transactions'   => 0,
            'total_value'    => 0.0,
            'oldest'         => null,
            'newest'         => null,
        ];

        if (!empty($rawTokens)) {
            $escapedTokens = array_map(fn($t) => $db->escape($t), $rawTokens);
            $inClause = implode(',', $escapedTokens);

            if ($db->tableExists('tbl_Sms')) {
                $g = $db->query("
                    SELECT
                        COUNT(*) AS all_sms,
                        COALESCE(SUM(CASE WHEN sms_is_finance = 1 THEN 1 ELSE 0 END), 0) AS finance_sms,
                        COALESCE(SUM(CASE WHEN sms_is_finance IS NULL OR sms_is_finance = 0 THEN 1 ELSE 0 END), 0) AS non_finance_sms,
                        COUNT(DISTINCT sms_number) AS all_senders,
                        COUNT(DISTINCT CASE WHEN sms_is_finance = 1 THEN sms_number END) AS finance_senders,
                        COUNT(DISTINCT CASE WHEN sms_is_finance IS NULL OR sms_is_finance = 0 THEN sms_number END) AS non_finance_senders,
                        COALESCE(SUM(CASE WHEN sms_direction = 'incoming' THEN 1 ELSE 0 END), 0) AS received,
                        COALESCE(SUM(CASE WHEN sms_direction = 'outgoing' THEN 1 ELSE 0 END), 0) AS sent,
                        COALESCE(SUM(CASE WHEN sms_direction IS NULL OR sms_direction = 'none' THEN 1 ELSE 0 END), 0) AS none,
                        COALESCE(SUM(CASE WHEN sms_is_transactional = 1 AND sms_amount IS NOT NULL THEN 1 ELSE 0 END), 0) AS transactions,
                        COALESCE(SUM(sms_amount), 0) AS total_value,
                        MIN(sms_time) AS oldest,
                        MAX(sms_time) AS newest
                    FROM tbl_Sms
                    WHERE sms_owner IN ($inClause)
                ")->getRow();

                if ($g) {
                    $stats['all_sms']        = (int)$g->all_sms;
                    $stats['finance_sms']    = (int)$g->finance_sms;
                    $stats['non_finance_sms'] = (int)$g->non_finance_sms;
                    $stats['all_senders']    = (int)$g->all_senders;
                    $stats['finance_senders']= (int)$g->finance_senders;
                    $stats['non_finance_senders'] = (int)$g->non_finance_senders;
                    $stats['sent']           = (int)$g->sent;
                    $stats['received']       = (int)$g->received;
                    $stats['none']           = (int)$g->none;
                    $stats['transactions']   = (int)$g->transactions;
                    $stats['total_value']    = (float)$g->total_value;
                    $stats['oldest']         = $g->oldest;
                    $stats['newest']         = $g->newest;
                }
            }

            if ($db->tableExists('tbl_Sender_Profiles')) {
                $b = $db->query("
                    SELECT COUNT(DISTINCT sp_number) AS banks
                    FROM tbl_Sender_Profiles
                    WHERE sp_owner IN ($inClause)
                      AND sp_is_finance = 1
                      AND (
                          sp_category = 'Bank'
                          OR sp_name LIKE '%BANK%'
                          OR sp_name LIKE '%KCB%'
                          OR sp_name LIKE '%EQUITY%'
                          OR sp_name LIKE '%NCBA%'
                          OR sp_name LIKE '%COOP%'
                          OR sp_name LIKE '%ABSA%'
                          OR sp_name LIKE '%STANBIC%'
                          OR sp_name LIKE '%DTB%'
                          OR sp_name LIKE '%I%M%'
                      )
                ")->getRow();
                $stats['banks'] = (int)($b->banks ?? 0);
            }

            $stats['unclassified'] = max(0, $stats['all_sms'] - $stats['finance_sms'] - $stats['non_finance_sms']);

            $summaries = $db->query("
                SELECT ls.loot_Uuid, ls.loot_Created, ls.info_All
                FROM tbl_Loot_Summary ls
                INNER JOIN tbl_Sms s ON s.sms_loot_source = ls.loot_Uuid
                WHERE s.sms_owner IN ($inClause)
                GROUP BY ls.loot_Uuid, ls.loot_Created, ls.info_All
                ORDER BY ls.loot_Created DESC
                LIMIT 50
            ")->getResult();

            foreach ($summaries as $s) {
                $uuid = $s->loot_Uuid;
                $total = (int)$s->info_All;

                $classified = 0;
                if ($db->tableExists('tbl_Sms_Classification')) {
                    $cr = $db->query("
                        SELECT COUNT(*) as cnt FROM tbl_Sms_Classification sc
                        INNER JOIN tbl_Sms s ON s.id = sc.sms_id
                        WHERE sc.method = 'llm' AND s.sms_loot_source = ?
                    ", [$uuid])->getRow();
                    $classified = (int)$cr->cnt;
                }

                $catRows = $db->query("
                    SELECT COALESCE(sms_category, 'Unclassified') as cat, COUNT(*) as cnt
                    FROM tbl_Sms
                    WHERE sms_loot_source = ?
                    GROUP BY COALESCE(sms_category, 'Unclassified')
                ", [$uuid])->getResult();

                $categories = [];
                foreach ($catRows as $cr) {
                    $categories[$cr->cat] = (int)$cr->cnt;
                }

                $amtRow = $db->query("
                    SELECT COALESCE(SUM(sms_amount), 0) as total FROM tbl_Sms
                    WHERE sms_loot_source = ? AND sms_is_transactional = 1
                ", [$uuid])->getRow();
                $totalAmount = (float)($amtRow->total ?? 0);

                $cpRow = $db->query("
                    SELECT COUNT(DISTINCT sms_counterparty) as cnt FROM tbl_Sms
                    WHERE sms_loot_source = ? AND sms_counterparty IS NOT NULL AND sms_counterparty != ''
                ", [$uuid])->getRow();
                $counterparties = (int)($cpRow->cnt ?? 0);

                $totalSmsAll += $total;
                $totalClassifiedAll += $classified;
                $totalAmountAll += $totalAmount;

                $batches[] = [
                    'uuid'           => $uuid,
                    'created_at'     => $s->loot_Created,
                    'total'          => $total,
                    'classified'     => $classified,
                    'amount'         => $totalAmount,
                    'categories'     => $categories,
                    'counterparties' => $counterparties,
                ];
            }
        }

        return [
            'batches' => $batches,
            'stats' => $stats,
            'total_sms_all' => $totalSmsAll,
            'total_classified_all' => $totalClassifiedAll,
            'total_amount_all' => $totalAmountAll,
        ];
    }

    /**
     * Fetch the current user's ML processing jobs with decoded metadata.
     */
    private function fetchUserJobs(): array
    {
        $db = \Config\Database::connect();
        $userId = auth()->user()->id;

        if (!$db->tableExists('tbl_Processing_Jobs')) {
            return [];
        }

        $jobs = $db->table('tbl_Processing_Jobs')
            ->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->limit(100)
            ->get()
            ->getResultArray();

        // Resolve token usages for computing pricing
        $tokenUsages = [];
        if ($db->tableExists('tbl_LLM_Calls')) {
            $calls = $db->table('tbl_LLM_Calls')
                ->select('job_id, SUM(prompt_tokens) as p, SUM(reply_tokens) as r')
                ->groupBy('job_id')
                ->get()
                ->getResult();
            foreach ($calls as $c) {
                $tokenUsages[(int)$c->job_id] = [
                    'prompt' => (int)$c->p,
                    'reply' => (int)$c->r
                ];
            }
        }

        foreach ($jobs as $key => $j) {
            $md = $j['metadata'] ?? null;
            if (is_string($md) && $md !== '') {
                $md = json_decode($md, true);
            }
            $md = is_array($md) ? $md : [];
            $jobs[$key]['metadata'] = $md;

            // Pricing logic
            $jobTokens = $tokenUsages[(int)$j['id']] ?? ['prompt' => 0, 'reply' => 0];
            $cost = (($jobTokens['prompt'] / 1000000) * 0.15) + (($jobTokens['reply'] / 1000000) * 0.60);
            $jobs[$key]['cost'] = $cost;
            $jobs[$key]['tokens'] = $jobTokens;
        }

        return $jobs;
    }

    /**
     * Stop/cancel a queued or processing ML job.
     */
    public function stopJob()
    {
        $jobId = (int)$this->request->getPost('job_id');
        if ($jobId <= 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid Job ID.']);
        }

        $db = \Config\Database::connect();
        $userId = auth()->user()->id;

        $job = $db->table('tbl_Processing_Jobs')
            ->where('id', $jobId)
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();

        if (!$job) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Job not found or access denied.']);
        }

        if (!in_array($job['status'], ['queued', 'processing', 'starting'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Job is not in a cancellable state.']);
        }

        $md = $job['metadata'] ?? null;
        if (is_string($md) && $md !== '') {
            $md = json_decode($md, true);
        }
        $md = is_array($md) ? $md : [];
        $md['error'] = 'Job stopped/cancelled by user.';

        $startedAt = $job['started_at'] ?? $job['created_at'] ?? null;
        $duration = null;
        if (!empty($startedAt)) {
            $duration = max(0, time() - strtotime($startedAt . ' UTC'));
        }
        $md['duration_seconds'] = $duration;

        $db->table('tbl_Processing_Jobs')
            ->where('id', $jobId)
            ->update([
                'status' => 'failed',
                'completed_at' => gmdate('Y-m-d H:i:s'),
                'duration_seconds' => $duration,
                'metadata' => json_encode($md)
            ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Job successfully cancelled/stopped.'
        ]);
    }
}
