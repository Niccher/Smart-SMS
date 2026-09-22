<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Squashed baseline migration for tbl_Category_Rules.
 *
 * Source: 2026-08-12-000001_CreateTblCategoryRules (unchanged)
 * Category override rules (keyword -> correct_category).
 */
class CreateTblCategoryRules extends Migration
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
            'keyword' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'correct_category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('keyword');
        $this->forge->createTable('tbl_Category_Rules', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_Category_Rules', true);
    }
}
