<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPerformanceIndexesToTblSmsAndLoot extends Migration
{
    public function up(): void
    {
        $this->createIndexSafe('tbl_Sms', 'idx_sms_owner_cat_date', 'sms_owner(64), sms_category, sms_trans_date');
        $this->createIndexSafe('tbl_Sms', 'idx_sms_loot_source', 'sms_loot_source(64)');
        $this->createIndexSafe('tbl_Loot', 'idx_loot_device_owner', 'loot_Device(64), loot_Owner(64)');
    }

    public function down(): void
    {
        $this->dropIndexSafe('tbl_Sms', 'idx_sms_owner_cat_date');
        $this->dropIndexSafe('tbl_Sms', 'idx_sms_loot_source');
        $this->dropIndexSafe('tbl_Loot', 'idx_loot_device_owner');
    }

    private function createIndexSafe(string $table, string $indexName, string $columns): void
    {
        try {
            $check = $this->db->query(
                "SELECT COUNT(*) as cnt FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?",
                [$table, $indexName]
            )->getRow();

            if (empty($check->cnt)) {
                $this->db->query("CREATE INDEX `{$indexName}` ON `{$table}` ({$columns})");
            }
        } catch (\Throwable $e) {
            log_message('warning', "Could not create index {$indexName} on {$table}: " . $e->getMessage());
        }
    }

    private function dropIndexSafe(string $table, string $indexName): void
    {
        try {
            $check = $this->db->query(
                "SELECT COUNT(*) as cnt FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?",
                [$table, $indexName]
            )->getRow();

            if (!empty($check->cnt)) {
                $this->db->query("DROP INDEX `{$indexName}` ON `{$table}`");
            }
        } catch (\Throwable $e) {
            log_message('warning', "Could not drop index {$indexName} on {$table}: " . $e->getMessage());
        }
    }
}
