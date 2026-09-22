<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Squashed baseline migration for tbl_LLM_Prompts.
 *
 * Source: 2026-07-15-190001_CreateTblLLMPrompts (unchanged)
 */
class CreateTblLLMPrompts extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'prompt_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'version' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'default'    => '',
            ],
            'body' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['prompt_key', 'version'], 'uq_key_version');
        $this->forge->addKey(['prompt_key', 'is_active'], false, false, 'idx_key_active');
        $this->forge->createTable('tbl_LLM_Prompts', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_LLM_Prompts', true);
    }
}
