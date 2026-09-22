<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DbInfo extends BaseController
{
    public function index()
    {
        helper('number');

        $db = \Config\Database::connect();
        $schema = $db->database;

        // Server info
        $serverRow = $db->query('SELECT VERSION() AS v, @@version_comment AS comment')->getRowArray();
        $schemaRow = $db->query(
            'SELECT default_character_set_name AS charset, default_collation_name AS collation
             FROM information_schema.schemata WHERE schema_name = ?',
            [$schema]
        )->getRowArray();

        $server = [
            'version'   => $serverRow['v'] ?? 'Unknown',
            'comment'   => $serverRow['comment'] ?? '',
            'host'      => $db->hostname,
            'port'      => $db->port,
            'driver'    => $db->DBDriver,
            'charset'   => $schemaRow['charset'] ?? ($db->charset ?? ''),
            'collation' => $schemaRow['collation'] ?? '',
            'database'  => $schema,
        ];

        // Overall stats
        $stats = $db->query(
            "SELECT
                COUNT(*) AS table_count,
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb,
                ROUND(SUM(data_length) / 1024 / 1024, 2) AS data_mb,
                ROUND(SUM(index_length) / 1024 / 1024, 2) AS index_mb,
                SUM(table_rows) AS approx_rows
            FROM information_schema.tables
            WHERE table_schema = ?",
            [$schema]
        )->getRowArray();

        // Storage engine breakdown
        $engines = $db->query(
            "SELECT engine AS engine_name, COUNT(*) AS table_count,
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
            FROM information_schema.tables
            WHERE table_schema = ?
            GROUP BY engine
            ORDER BY size_mb DESC",
            [$schema]
        )->getResultArray();

        // Field counts + field lists per table (single queries)
        $fieldCountMap = [];
        foreach ($db->query(
            'SELECT table_name AS name, COUNT(*) AS field_count
             FROM information_schema.columns
             WHERE table_schema = ?
             GROUP BY table_name',
            [$schema]
        )->getResultArray() as $fc) {
            $fieldCountMap[$fc['name']] = (int) $fc['field_count'];
        }

        $fieldListMap = [];
        foreach ($db->query(
            "SELECT table_name AS name,
                    GROUP_CONCAT(column_name ORDER BY ordinal_position SEPARATOR ', ') AS field_list
            FROM information_schema.columns
            WHERE table_schema = ?
            GROUP BY table_name",
            [$schema]
        )->getResultArray() as $ft) {
            $fieldListMap[$ft['name']] = $ft['field_list'];
        }

        // Tables ordered by size
        $tables = $db->query(
            "SELECT
                table_name AS name,
                table_rows AS row_count,
                engine AS engine_name,
                table_collation AS collation,
                (data_length + index_length) AS size_bytes,
                data_length AS data_bytes,
                index_length AS index_bytes
            FROM information_schema.tables
            WHERE table_schema = ?
            ORDER BY size_bytes DESC",
            [$schema]
        )->getResultArray();

        $totalBytes = 0;
        foreach ($tables as &$t) {
            $t['field_count'] = $fieldCountMap[$t['name']] ?? 0;
            $t['field_list'] = $fieldListMap[$t['name']] ?? '';
            $t['size_human']  = number_to_size($t['size_bytes'] ?? 0);
            $t['data_human']  = number_to_size($t['data_bytes'] ?? 0);
            $t['index_human'] = number_to_size($t['index_bytes'] ?? 0);
            $totalBytes += (int) $t['size_bytes'];
        }
        unset($t);

        $data = [
            'bg_color' => '#B1B8ED',
            'server' => $server,
            'stats' => $stats,
            'engines' => $engines,
            'tables' => $tables,
            'total_size_human' => number_to_size($totalBytes),
            'total_rows' => number_format((float) ($stats['approx_rows'] ?? 0)),
        ];

        return view('Admin/DbInfo/index', $data);
    }
}
