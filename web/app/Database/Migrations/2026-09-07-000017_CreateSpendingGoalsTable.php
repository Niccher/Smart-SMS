<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Squashed baseline migration for tbl_Spending_Goals.
 *
 * Source: 2026-07-25-130000_CreateSpendingGoalsTable (unchanged)
 */
class CreateSpendingGoalsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'category'      => ['type' => 'VARCHAR', 'constraint' => 100],
            'label'         => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'target_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
            'period'        => ['type' => 'ENUM', 'constraint' => ['monthly', 'weekly'], 'default' => 'monthly'],
            'rollover'      => ['type' => 'BOOLEAN', 'default' => false],
            'active'        => ['type' => 'BOOLEAN', 'default' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tbl_Spending_Goals', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_Spending_Goals', true);
    }
}
