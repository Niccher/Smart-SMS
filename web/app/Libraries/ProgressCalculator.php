<?php

namespace App\Libraries;

class ProgressCalculator
{
    /**
     * Calculates windowed spending progress for a budget or spending goal.
     *
     * @param string $category Target category name
     * @param string $period 'monthly' or 'weekly'
     * @param float $targetAmount Target spending limit
     * @param bool $rollover Whether unused balance rolls over from last period
     * @return array Calculated progress details [spent, carry_over, effective_limit, percentage, remaining, over_limit]
     */
    public static function calculateProgress(string $category, string $period, float $targetAmount, bool $rollover = false): array
    {
        $db  = \Config\Database::connect();
        $now = time();

        if ($period === 'weekly') {
            $windowStart = strtotime('last monday', $now);
            if (date('N', $now) == 1) $windowStart = strtotime('today', $now);
        } else {
            $windowStart = mktime(0, 0, 0, (int)date('m'), 1);
        }

        $allSms = $db->table('tbl_Sms s')
            ->select('s.sms_time, s.sms_body, sc.direction as cl_direction')
            ->join('tbl_Sms_Classification sc', 'sc.sms_id = s.id', 'left')
            ->get()->getResult();

        $carryOver = 0.0;
        if ($rollover && $period === 'monthly') {
            $lastMonthStart = mktime(0, 0, 0, (int)date('m') - 1, 1);
            $lastMonthEnd   = mktime(23, 59, 59, (int)date('m'), 0);
            foreach ($allSms as $sms) {
                $ts = is_numeric($sms->sms_time) && $sms->sms_time > 1000000000000
                    ? (int)($sms->sms_time / 1000)
                    : (is_numeric($sms->sms_time) ? (int)$sms->sms_time : strtotime((string)$sms->sms_time));
                if ($ts < $lastMonthStart || $ts > $lastMonthEnd) continue;
                $dir  = strtolower($sms->cl_direction ?? '');
                $body = strtolower(base64_decode($sms->sms_body));
                if (self::matchesCategory($category, $dir, $body)) {
                    if (preg_match('/ksh\.?\s*([\d,]+\.?\d{0,2})/i', $body, $m)) {
                        $carryOver -= (float)str_replace(',', '', $m[1]);
                    }
                }
            }
            $carryOver = max(0, $targetAmount + $carryOver);
        }

        $spent = 0.0;
        foreach ($allSms as $sms) {
            $ts = is_numeric($sms->sms_time) && $sms->sms_time > 1000000000000
                ? (int)($sms->sms_time / 1000)
                : (is_numeric($sms->sms_time) ? (int)$sms->sms_time : strtotime((string)$sms->sms_time));
            if ($ts < $windowStart) continue;
            $dir  = strtolower($sms->cl_direction ?? '');
            $body = strtolower(base64_decode($sms->sms_body));
            if (self::matchesCategory($category, $dir, $body)) {
                if (preg_match('/ksh\.?\s*([\d,]+\.?\d{0,2})/i', $body, $m)) {
                    $spent += (float)str_replace(',', '', $m[1]);
                }
            }
        }

        $effectiveLimit = $targetAmount + $carryOver;
        $percentage     = $effectiveLimit > 0 ? min(round(($spent / $effectiveLimit) * 100, 1), 100) : 0;

        return [
            'spent'           => $spent,
            'carry_over'      => $carryOver,
            'effective_limit' => $effectiveLimit,
            'percentage'      => $percentage,
            'remaining'       => max(0, $effectiveLimit - $spent),
            'over_limit'      => $spent > $effectiveLimit,
        ];
    }

    private static function matchesCategory(string $category, string $dir, string $body): bool
    {
        $catLower = strtolower($category);
        if ($catLower === 'total outflow') return $dir === 'outgoing';
        if ($catLower === 'received') return $dir === 'incoming';
        if ($catLower === 'paybill') return $dir === 'outgoing' && strpos($body, 'paybill') !== false;
        if ($catLower === 'till') return $dir === 'outgoing' && strpos($body, 'till') !== false;
        if ($catLower === 'sent to mobile') return $dir === 'outgoing';
        if ($catLower === 'withdrawal') return $dir === 'outgoing';
        if ($catLower === 'fuliza') return strpos($body, 'fuliza') !== false && strpos($body, 'taken') !== false;
        return $dir === 'outgoing' || $dir === 'incoming';
    }
}
