<?php

namespace App\Libraries;

class LlmRecurringAnalyzer
{
    /**
     * Enhances detected transaction patterns using heuristics and structured LLM rule fallback.
     *
     * @param array $patterns Raw detected patterns from RecurringPaymentModel
     * @return array Enhanced patterns with suggested_label, suggested_category, frequency, and type
     */
    public static function analyze(array $patterns): array
    {
        if (empty($patterns)) {
            return [];
        }

        $enhanced = [];
        foreach ($patterns as $p) {
            $cp = trim($p['counterparty'] ?? '');
            $desc = trim($p['description'] ?? '');
            $amount = (float)($p['amount'] ?? 0);

            // Apply smart rule-based label & category suggestions
            $suggestion = self::inferFromRules($cp, $desc, $amount);

            $enhanced[] = array_merge($p, [
                'suggested_label'    => $suggestion['label'],
                'suggested_category' => $suggestion['category'],
                'type'               => $suggestion['type'],
                'confidence'         => $suggestion['confidence'],
            ]);
        }

        return $enhanced;
    }

    private static function inferFromRules(string $cp, string $desc, float $amount): array
    {
        $cpUpper = strtoupper($cp);
        $descUpper = strtoupper($desc);

        if (strpos($cpUpper, 'NETFLIX') !== false) {
            return ['label' => 'Netflix Subscription', 'category' => 'Entertainment', 'type' => 'subscription', 'confidence' => 0.98];
        }
        if (strpos($cpUpper, 'SPOTIFY') !== false) {
            return ['label' => 'Spotify Music', 'category' => 'Entertainment', 'type' => 'subscription', 'confidence' => 0.98];
        }
        if (strpos($cpUpper, 'SAFARICOM') !== false && (strpos($cpUpper, 'POSTPAID') !== false || strpos($cpUpper, 'HOME') !== false || strpos($cpUpper, 'FIBER') !== false)) {
            return ['label' => 'Safaricom Home Fiber / Postpaid', 'category' => 'Utilities', 'type' => 'utility-bill', 'confidence' => 0.95];
        }
        if (strpos($cpUpper, 'KPLC') !== false || strpos($descUpper, 'TOKENS') !== false) {
            return ['label' => 'KPLC Electricity Tokens', 'category' => 'Utilities', 'type' => 'utility-bill', 'confidence' => 0.96];
        }
        if (strpos($cpUpper, 'ZUKU') !== false) {
            return ['label' => 'Zuku Fiber Internet', 'category' => 'Utilities', 'type' => 'utility-bill', 'confidence' => 0.95];
        }
        if (strpos($cpUpper, 'TALA') !== false || strpos($cpUpper, 'BRANCH') !== false || strpos($cpUpper, 'M-SHWARI') !== false || strpos($cpUpper, 'KCB MPESA') !== false) {
            return ['label' => $cp . ' Loan Repayment', 'category' => 'Fintech', 'type' => 'loan-repayment', 'confidence' => 0.92];
        }
        if (strpos($descUpper, 'AIRTIME') !== false) {
            return ['label' => 'Regular Airtime Purchase', 'category' => 'Mobile Money', 'type' => 'airtime', 'confidence' => 0.90];
        }

        // Generic fallback with humanized title
        $cleanLabel = ucwords(strtolower($cp ?: $desc ?: 'Recurring Payment'));
        return [
            'label'      => $cleanLabel,
            'category'   => !empty($desc) ? $desc : 'Utilities',
            'type'       => 'regular-transfer',
            'confidence' => 0.75,
        ];
    }
}
