<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Squashed baseline migration for tbl_Processing_Jobs.
 *
 * Final schema derived from:
 *   2026-07-15-180000_CreateProcessingJobs
 *   2026-07-15-190003_AddMetadataToProcessingJobs  (metadata JSON)
 */
class CreateProcessingJobs extends Migration
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
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'queued',
            ],
            'started_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'duration_seconds' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'messages_processed' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'errors' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'metadata' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['user_id', 'status']);
        $this->forge->createTable('tbl_Processing_Jobs', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_Processing_Jobs', true);
    }
}
