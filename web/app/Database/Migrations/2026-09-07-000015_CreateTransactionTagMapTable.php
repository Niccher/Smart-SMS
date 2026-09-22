<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Squashed baseline migration for tbl_Transaction_Tag_Map.
 *
 * Source: 2026-07-25-110000_CreateTransactionTagMapTable (unchanged)
 */
class CreateTransactionTagMapTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tag_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'sms_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'trans_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('tag_id', 'tbl_Transaction_Tags', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tbl_Transaction_Tag_Map', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_Transaction_Tag_Map', true);
    }
}
