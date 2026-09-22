<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Squashed baseline migration for tbl_Cron_Log.
 *
 * Source: 2026-08-09-010000_CreateCronLogTable (unchanged)
 */
class CreateCronLogTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'job_key'  => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => ''],
            'job_name' => ['type' => 'VARCHAR', 'constraint' => 191, 'default' => ''],
            'job_type' => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => ''],
            'status'   => ['type' => 'ENUM', 'constraint' => ['success', 'error'], 'default' => 'error'],
            'output'   => ['type' => 'TEXT', 'null' => true],
            'trigger'  => ['type' => 'ENUM', 'constraint' => ['scheduler', 'manual'], 'default' => 'scheduler'],
            'ran_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('job_key');
        $this->forge->addKey('ran_at');
        $this->forge->createTable('tbl_Cron_Log', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_Cron_Log', true);
    }
}
