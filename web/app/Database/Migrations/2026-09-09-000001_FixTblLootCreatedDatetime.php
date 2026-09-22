<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixTblLootCreatedDatetime extends Migration
{
    public function up(): void
    {
        try {
            // 1. Temporarily modify to VARCHAR(50) so any string fits without type errors
            $this->forge->modifyColumn('tbl_Loot', [
                'loot_Created' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
            ]);

            // 2. Restore the true upload timestamp from tbl_Loot_Summary if it was truncated to '2026' or near 0
            $this->db->query("
                UPDATE tbl_Loot l
                LEFT JOIN tbl_Loot_Summary ls ON ls.loot_Uuid = l.loot_Uuid
                SET l.loot_Created = COALESCE(ls.loot_Created, NOW())
                WHERE l.loot_Created IS NULL 
                   OR LENGTH(l.loot_Created) <= 5 
                   OR l.loot_Created = '2026' 
                   OR l.loot_Created = '0'
            ");

            // 3. Formally alter the column to DATETIME NULL
            $this->forge->modifyColumn('tbl_Loot', [
                'loot_Created' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
        } catch (\Throwable $e) {
            log_message('warning', 'FixTblLootCreatedDatetime migration warning: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        try {
            $this->forge->modifyColumn('tbl_Loot', [
                'loot_Created' => [
                    'type' => 'BIGINT',
                    'null' => true,
                ],
            ]);
        } catch (\Throwable $e) {
            log_message('warning', 'FixTblLootCreatedDatetime down warning: ' . $e->getMessage());
        }
    }
}
