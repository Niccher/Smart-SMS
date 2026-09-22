<?php

namespace App\Models;

use CodeIgniter\Model;

class RecurringPaymentModel extends Model
{
    protected $table      = 'tbl_Recurring_Transactions';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'label', 'counterparty', 'amount', 'frequency',
        'category', 'direction', 'day_of_period', 'active',
        'last_occurrence', 'next_expected', 'created_at', 'updated_at'
    ];
    protected $useTimestamps = true;

    public function getForUser(int $userId): array
    {
        return $this->where('user_id', $userId)->orderBy('next_expected', 'ASC')->findAll();
    }

    public function getUpcoming(int $userId, int $days = 30): array
    {
        $cutoff = date('Y-m-d', strtotime("+{$days} days"));
        return $this->where('user_id', $userId)
            ->where('active', 1)
            ->where('next_expected <=', $cutoff)
            ->orderBy('next_expected', 'ASC')
            ->findAll();
    }

    public function detectRecurring(int $userId): array
    {
        $db = \Config\Database::connect();
        $tokenType = \CodeIgniter\Shield\Authentication\Authenticators\AccessTokens::ID_TYPE_ACCESS_TOKEN;
        $rawTokens = [];
        $tokenRows = $db->query("
            SELECT DISTINCT s.sms_owner AS tk FROM tbl_Sms s
            INNER JOIN auth_identities i ON i.secret = SHA2(s.sms_owner, 256)
            WHERE i.user_id = ? AND i.type = ?
        ", [$userId, $tokenType])->getResult();
        foreach ($tokenRows as $r) {
            if (!empty($r->tk)) $rawTokens[] = $r->tk;
        }
        if (empty($rawTokens)) return [];

        $rows = $db->table('tbl_Analyzed_Transactions a')
            ->select('a.counterparty, a.amount, a.description, a.trans_date, COUNT(*) as occurrences')
            ->join('tbl_Sms s', 's.id = a.orig_sms_int_id')
            ->whereIn('s.sms_owner', $rawTokens)
            ->where('a.trans_date >=', date('Y-m-d', strtotime('-6 months')))
            ->groupBy('a.counterparty, a.amount, a.description')
            ->having('occurrences >=', 3)
            ->orderBy('occurrences', 'DESC')
            ->limit(20)
            ->get()
            ->getResultArray();

        return $rows;
    }
}
