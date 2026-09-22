<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Squashed baseline migration for tbl_Transaction_Tags.
 *
 * Source: 2026-07-25-100000_CreateTransactionTagsTable (unchanged)
 */
class CreateTransactionTagsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'color'      => ['type' => 'VARCHAR', 'constraint' => 7, 'default' => '#5D5FEF'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tbl_Transaction_Tags', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_Transaction_Tags', true);
    }
}
