<?php

if (!function_exists('user_settings')) {
    function user_settings(?string $key = null): mixed
    {
        $userId = auth()->user()->id ?? null;
        if (!$userId) {
            return $key ? null : [];
        }

        $model = new \App\Models\UserSettingModel();
        $settings = $model->getSettings($userId);

        if ($key) {
            return $settings[$key] ?? null;
        }
        return $settings;
    }
}

if (!function_exists('currency_symbol')) {
    function currency_symbol(?string $currency = null): string
    {
        $currency = $currency ?? user_settings('currency') ?? 'KES';
        $symbols = [
            'KES' => 'Ksh',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'TZS' => 'TSh',
            'UGX' => 'USh',
            'RWF' => 'FRw',
        ];
        return $symbols[$currency] ?? 'Ksh';
    }
}

if (!function_exists('format_user_date')) {
    function format_user_date(string $dateStr, ?string $format = null): string
    {
        $format = $format ?? user_settings('date_format') ?? 'Y-m-d';
        $timestamp = strtotime($dateStr);
        if (!$timestamp) return $dateStr;
        return date($format, $timestamp);
    }
}
