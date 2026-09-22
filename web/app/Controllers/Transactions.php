<?php

namespace App\Controllers;

use App\Models\UploadModel;

class Transactions extends BaseController
{
    protected int $perPage = 25;

    public function index()
    {
        helper(['mpesa_date', 'form']);
        $db = \Config\Database::connect();
        $mod_uploads = new UploadModel();

        $page    = (int)($this->request->getGet('page') ?? 1);
        $page    = max(1, $page);
        $offset  = ($page - 1) * $this->perPage;

        $filters = [
            'category'         => $this->request->getGet('category') ?? '',
            'search'           => $this->request->getGet('search') ?? '',
            'sender'           => $this->request->getGet('sender') ?? '',
            'date_from'        => $this->request->getGet('date_from') ?? '',
            'date_to'          => $this->request->getGet('date_to') ?? '',
            'min_amount'       => $this->request->getGet('min_amount') ?? '',
            'max_amount'       => $this->request->getGet('max_amount') ?? '',
            'transaction_type' => $this->request->getGet('transaction_type') ?? '',
        ];

        // Fetch unique senders belonging to this user's tokens
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

        $sendersList = [];
        if (!empty($rawTokens)) {
            $escapedTokens = array_map(fn($t) => $db->escape($t), $rawTokens);
            $inClause = implode(',', $escapedTokens);
            $senderRows = $db->query("
                SELECT DISTINCT sms_number FROM tbl_Sms
                WHERE sms_is_finance = 1
                  AND sms_owner IN ($inClause)
                  AND sms_number IS NOT NULL
                  AND sms_number != ''
                ORDER BY sms_number ASC
            ")->getResultArray();
            $sendersList = array_column($senderRows, 'sms_number');
        }

        // Count total
        $total = $mod_uploads->countFilteredTransactions($filters);

        // Fetch paginated transactions
        $transactions = $mod_uploads->getFilteredTransactions($filters, $this->perPage, $offset);

        $totalPages = (int)ceil($total / $this->perPage);

        // Fetch LLM categories for the Fix dropdown
        $llmCategories = $db->table('tbl_Sms')
            ->select('sms_category')
            ->distinct()
            ->where('sms_category IS NOT NULL')
            ->where("sms_category != ''")
            ->get()
            ->getResultArray();
        $llmCategories = array_unique(array_column($llmCategories, 'sms_category'));
        sort($llmCategories);

        $data = [
            'transactions'   => $transactions,
            'total'          => $total,
            'page'           => $page,
            'perPage'        => $this->perPage,
            'totalPages'     => $totalPages,
            'filters'        => $filters,
            'senders'        => $sendersList,
            'llm_categories' => $llmCategories,
            'category'       => $filters['category'],
            'bg_color'       => '#B1B8ED'
        ];

        return view('Dash/transactions', $data);
    }

    public function export()
    {
        $mod_uploads = new UploadModel();

        $filters = [
            'category'         => $this->request->getGet('category') ?? '',
            'search'           => $this->request->getGet('search') ?? '',
            'sender'           => $this->request->getGet('sender') ?? '',
            'date_from'        => $this->request->getGet('date_from') ?? '',
            'date_to'          => $this->request->getGet('date_to') ?? '',
            'min_amount'       => $this->request->getGet('min_amount') ?? '',
            'max_amount'       => $this->request->getGet('max_amount') ?? '',
            'transaction_type' => $this->request->getGet('transaction_type') ?? '',
        ];

        $transactions = $mod_uploads->getFilteredTransactions($filters, 10000, 0);

        $filename = "transactions_export_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        fputcsv($output, ['Date & Time', 'Category', 'Direction', 'Counterparty', 'Amount (Ksh)', 'Fee (Ksh)', 'Reversal', 'Loan', 'Sender', 'Message Body']);

        foreach ($transactions as $tx) {
            $body = base64_decode($tx->sms_body);
            $body = trim(preg_replace('/\s+/', ' ', $body));

            fputcsv($output, [
                $tx->sms_time,
                $tx->sms_category ?? 'Unclassified',
                $tx->sms_direction ?? 'none',
                $tx->sms_counterparty ?? 'Unknown',
                number_format((float)($tx->sms_amount ?? 0), 2, '.', ''),
                number_format((float)($tx->sms_fee ?? 0), 2, '.', ''),
                !empty($tx->sms_is_reversal) ? 'Yes' : 'No',
                !empty($tx->sms_is_loan) ? 'Yes' : 'No',
                $tx->sms_number ?? '',
                $body
            ]);
        }

        fclose($output);
        exit;
    }
}
