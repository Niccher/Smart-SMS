<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Squashed baseline migration for tbl_Analyzed_Transactions.
 *
 * Final schema derived from:
 *   2026-06-22-000003_CreateAnalyzedTransactions
 *   2026-07-15-170004_AddOrigSmsIntIdToAnalyzedTransactions  (orig_sms_int_id)
 */
class CreateTblAnalyzedTransactions extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'orig_sms_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'orig_sms_int_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'trans_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
            ],
            'counterparty' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'trans_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('orig_sms_id');
        $this->forge->addKey('trans_date');
        $this->forge->createTable('tbl_Analyzed_Transactions', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_Analyzed_Transactions', true);
    }
}
