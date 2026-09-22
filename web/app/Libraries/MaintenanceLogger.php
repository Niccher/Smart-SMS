<?php

namespace App\Libraries;

/**
 * Tracks maintenance mode transitions and records them in tbl_Maintenance_Log.
 *
 * The effective maintenance state is either the manual toggle
 * (maintenance_mode) or an active scheduled window. Whenever the effective
 * state changes, a start/stop entry is written with a source of either
 * 'manual' (toggled by an admin) or 'cron' (scheduled window auto activation).
 */
class MaintenanceLogger
{
    /**
     * Compare the effective maintenance state against the last recorded one
     * and log a transition when it changed.
     *
     * @param array $config tbl_Settings config (maintenance_* keys)
     */
    public static function sync(array $config): void
    {
        $db = \Config\Database::connect();

        $mode = filter_var($config['maintenance_mode'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $active = $mode || self::inWindow($config);
        $newState = $active ? 'on' : 'off';

        $stateRow = $db->table('tbl_Settings')->where('`key`', 'maintenance_state')->get()->getRow();

        // First run: baseline the current state without recording a transition.
        if (!$stateRow) {
            $db->table('tbl_Settings')->insert([
                'key' => 'maintenance_state',
                'value' => $newState,
                'type' => 'string',
                'description' => 'Current maintenance state (off/on)',
            ]);
            return;
        }

        $current = $stateRow->value ?? 'off';

        if ($current === $newState) {
            return;
        }

        // Optimistic conditional update: only the first request to flip the
        // state wins and records the transition (avoids duplicate log entries).
        $db->table('tbl_Settings')
            ->where('`key`', 'maintenance_state')
            ->where('value', $current)
            ->update(['value' => $newState]);

        if ($db->affectedRows() !== 1) {
            return;
        }

        $sourceRow = $db->table('tbl_Settings')->where('`key`', 'maintenance_source')->get()->getRow();
        $lastSource = $sourceRow->value ?? 'manual';

        // Starting: manual if the toggle is on, otherwise the scheduled window
        // activated automatically. Stopping: keep the source that started it.
        $source = $active ? ($mode ? 'manual' : 'cron') : $lastSource;

        if (!$sourceRow) {
            $db->table('tbl_Settings')->insert([
                'key' => 'maintenance_source',
                'value' => $source,
                'type' => 'string',
                'description' => 'Source of the current maintenance state',
            ]);
        } else {
            $db->table('tbl_Settings')->where('`key`', 'maintenance_source')->update(['value' => $source]);
        }

        $db->table('tbl_Maintenance_Log')->insert([
            'action' => $active ? 'start' : 'stop',
            'source' => $source,
            'message' => $config['maintenance_message'] ?? '',
            'created_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    public static function inWindow(array $config): bool
    {
        if (!filter_var($config['maintenance_scheduled'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return false;
        }

        $start = str_replace('T', ' ', (string) ($config['maintenance_schedule_start'] ?? ''));
        $end = str_replace('T', ' ', (string) ($config['maintenance_schedule_end'] ?? ''));
        $now = gmdate('Y-m-d H:i:s');

        return $start !== '' && $end !== '' && $now >= $start && $now <= $end;
    }
}
