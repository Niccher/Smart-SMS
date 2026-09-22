<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Squashed baseline migration for tbl_Sms_Processing.
 *
 * Source: 2026-07-15-170003_CreateTblSmsProcessing (unchanged)
 */
class CreateTblSmsProcessing extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'sms_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'pending',
            ],
            'attempt_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'last_error' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'processed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('sms_id');
        $this->forge->addKey('status');
        $this->forge->createTable('tbl_Sms_Processing', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_Sms_Processing', true);
    }
}
