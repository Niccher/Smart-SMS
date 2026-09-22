<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Squashed baseline migration for tbl_Budgets.
 *
 * Final schema derived from:
 *   2026-08-12-000000_CreateTblBudgets
 *   2026-08-23-180000_AddBudgetThresholdAlerts  (threshold_alert_80, threshold_alert_100, alert_sent_at)
 */
class CreateTblBudgets extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'amount_limit' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
                'null'       => false,
            ],
            'period' => [
                'type'       => 'ENUM',
                'constraint' => ['monthly', 'weekly'],
                'default'    => 'monthly',
                'null'       => false,
            ],
            'rollover' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'threshold_alert_80' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
            ],
            'threshold_alert_100' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
            ],
            'alert_sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('tbl_Budgets', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_Budgets', true);
    }
}
