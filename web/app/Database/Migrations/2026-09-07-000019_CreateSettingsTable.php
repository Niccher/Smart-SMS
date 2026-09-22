<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Squashed baseline migration for tbl_Settings (global key/value store).
 *
 * Source: 2026-08-08-000000_CreateSettingsTable (unchanged)
 */
class CreateSettingsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'key'         => ['type' => 'VARCHAR', 'constraint' => 100],
            'value'       => ['type' => 'TEXT', 'null' => true],
            'type'        => ['type' => 'ENUM', 'constraint' => ['string', 'boolean', 'integer', 'json'], 'default' => 'string'],
            'description' => ['type' => 'TEXT', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('key');
        $this->forge->createTable('tbl_Settings', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_Settings', true);
    }
}
