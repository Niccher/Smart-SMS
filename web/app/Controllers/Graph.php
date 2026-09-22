<?php

namespace App\Controllers;

use App\Models\UploadModel;
use App\Models\InsightModel;

class Graph extends BaseController
{
    public function index()
    {
        helper('mpesa_date');
        $mod_uploads  = new UploadModel();
        $mod_insights = new InsightModel();

        $lastDate      = $mod_uploads->getLatestTransactionDate() ?? date('Y-m-d H:i:s');
        $analytics     = $mod_uploads->getAnalyticsData30Days($lastDate);
        $ai_observations = $mod_insights->getAIObservations();

        // Fetch latest 20 transactions for the table below the graph
        $recent = $mod_uploads->getFilteredTransactions([], 20, 0);

        // Dashboard metrics
        $metrics = $mod_uploads->getDashboardMetrics30Days($lastDate);
        $sent    = $mod_uploads->getSentSummary30Days($lastDate);
        $received = $mod_uploads->getReceivedSummary30Days($lastDate);

        // Fetch total transaction fees and total loans
        $db = \Config\Database::connect();
        $totals = $db->table('tbl_Sms')
            ->select('SUM(sms_fee) as total_fees, SUM(CASE WHEN sms_is_loan = 1 THEN sms_amount ELSE 0 END) as total_loans')
            ->where('sms_is_finance', 1)
            ->get()
            ->getRow();

        $totalFees = (float)($totals->total_fees ?? 0);
        $totalLoans = (float)($totals->total_loans ?? 0);

        $data = [
            'analytics'        => $analytics,
            'ai_observations'  => $ai_observations,
            'recent'           => $recent,
            'metrics'          => $metrics,
            'sent'             => $sent,
            'received'         => $received,
            'total_fees'       => $totalFees,
            'total_loans'      => $totalLoans,
            'bg_color'         => '#B1B8ED',
        ];

        return view('Dash/graph', $data);
    }

    public function categoryDetails()
    {
        $category = $this->request->getVar('category');
        if (empty($category)) {
            return $this->response->setJSON([]);
        }

        $db = \Config\Database::connect();
        $userId = auth()->user()->id;
        $tokenType = \CodeIgniter\Shield\Authentication\Authenticators\AccessTokens::ID_TYPE_ACCESS_TOKEN;

        // Get this user's tokens to scope queries correctly
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
        if (empty($rawTokens)) {
            return $this->response->setJSON([]);
        }

        $escapedTokens = array_map(fn($t) => $db->escape($t), $rawTokens);
        $inClause = implode(',', $escapedTokens);

        // Fetch counterparties and their sum for this category
        $results = $db->query("
            SELECT COALESCE(NULLIF(sms_counterparty, ''), 'Unknown') as counterparty, 
                   COUNT(*) as trans_count, 
                   SUM(sms_amount) as total_amount
            FROM tbl_Sms
            WHERE sms_owner IN ($inClause)
              AND COALESCE(sms_category, 'Unclassified') = ?
            GROUP BY COALESCE(NULLIF(sms_counterparty, ''), 'Unknown')
            ORDER BY total_amount DESC
            LIMIT 30
        ", [$category])->getResultArray();

        return $this->response->setJSON($results);
    }
}
