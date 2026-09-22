<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Squashed baseline migration for tbl_Blocked_Senders.
 *
 * Source: 2026-08-10-020000_CreateBlockedSenders (unchanged)
 */
class CreateBlockedSenders extends Migration
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
                'null'       => false,
            ],
            'sender' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['user_id', 'sender']);
        $this->forge->addKey('sender');
        $this->forge->createTable('tbl_Blocked_Senders', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_Blocked_Senders', true);
    }
}
