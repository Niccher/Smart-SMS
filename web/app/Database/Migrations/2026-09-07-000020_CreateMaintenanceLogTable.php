<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Squashed baseline migration for tbl_Maintenance_Log.
 *
 * Source: 2026-08-09-000000_CreateMaintenanceLogTable (unchanged)
 */
class CreateMaintenanceLogTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'action'     => ['type' => 'ENUM', 'constraint' => ['start', 'stop'], 'default' => 'start'],
            'source'     => ['type' => 'ENUM', 'constraint' => ['manual', 'cron'], 'default' => 'manual'],
            'message'    => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('created_at');
        $this->forge->createTable('tbl_Maintenance_Log', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_Maintenance_Log', true);
    }
}
