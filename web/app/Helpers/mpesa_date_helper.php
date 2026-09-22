<?php

if (!function_exists('format_mpesa_date')) {
    /**
     * Standardizes date to: Year, Month Name, Day of Week Name, Time AM/PM
     * Example: 2026, March 13, Friday 07:10 PM
     */
    function format_mpesa_date($time): string {
        if (empty($time)) return 'N/A';

        // Guard against corrupted epoch year values like 2026 seconds (< 946684800 is before year 2000)
        if (is_numeric($time) && (int)$time < 946684800) {
            return 'N/A';
        }
        
        // Handle millisecond timestamp (common in M-Pesa JS uploads)
        if (is_numeric($time) && $time > 1000000000000) {
            $timestamp = (int)($time / 1000);
        } else {
            $timestamp = is_numeric($time) ? (int)$time : strtotime($time);
        }

        if (!$timestamp || $timestamp < 946684800) return 'N/A';

        return date('Y, F, l h:i A', $timestamp);
    }
}

if (!function_exists('format_date_display')) {
    /**
     * Standardizes date to: Day Abbr, Month Abbr, Day, Year - Time
     * Example: Mon, Aug 10, 2026 08:04 AM
     */
    function format_date_display($time): string {
        if (empty($time)) return 'N/A';

        // Guard against corrupted epoch year values like 2026 seconds (< 946684800 is before year 2000)
        if (is_numeric($time) && (int)$time < 946684800) {
            return 'N/A';
        }

        // Handle millisecond timestamp (common in M-Pesa JS uploads)
        if (is_numeric($time) && $time > 1000000000000) {
            $timestamp = (int)($time / 1000);
        } else {
            $timestamp = is_numeric($time) ? (int)$time : strtotime($time);
        }

        if (!$timestamp || $timestamp < 946684800) return 'N/A';

        return date('D, M d, Y h:i A', $timestamp);
    }
}

if (!function_exists('time_elapsed_string')) {
    function time_elapsed_string($datetime, $full = false): string {
        if (empty($datetime)) return 'N/A';
        $timestamp = is_numeric($datetime) ? (int)$datetime : strtotime($datetime);
        if (!$timestamp) return 'N/A';

        $now = time();
        $diff = $now - $timestamp;

        if ($diff < 60) return 'just now';

        $units = [
            31536000 => 'year',
            2592000  => 'month',
            604800   => 'week',
            86400    => 'day',
            3600     => 'hour',
            60       => 'minute',
        ];

        foreach ($units as $secs => $unit) {
            $count = floor($diff / $secs);
            if ($count >= 1) {
                $suffix = $count > 1 ? 's' : '';
                return $full ? "$count $unit$suffix ago" : "$count $unit$suffix ago";
            }
        }
        return 'just now';
    }
}
