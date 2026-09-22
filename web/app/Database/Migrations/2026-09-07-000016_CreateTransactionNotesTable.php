<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Squashed baseline migration for tbl_Transaction_Notes.
 *
 * Source: 2026-07-25-120000_CreateTransactionNotesTable (unchanged)
 */
class CreateTransactionNotesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'sms_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'trans_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'note'       => ['type' => 'TEXT'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tbl_Transaction_Notes', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_Transaction_Notes', true);
    }
}
