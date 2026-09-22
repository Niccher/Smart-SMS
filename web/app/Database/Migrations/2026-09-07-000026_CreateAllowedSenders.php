<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Squashed baseline migration for tbl_Allowed_Senders.
 *
 * Source: 2026-08-10-030000_CreateAllowedSenders (unchanged)
 * Global allowlist of senders always treated as finance-related.
 */
class CreateAllowedSenders extends Migration
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
            'sender' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('sender');
        $this->forge->addKey('category');
        $this->forge->createTable('tbl_Allowed_Senders', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_Allowed_Senders', true);
    }
}
