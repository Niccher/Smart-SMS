<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Squashed baseline migration for tbl_LLM_Calls.
 *
 * Source: 2026-08-24-210000_CreateTblLLMCalls (unchanged)
 * Per-call audit log for every LLM API invocation.
 */
class CreateTblLLMCalls extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'job_id' => [
                'type'    => 'INT',
                'unsigned' => true,
                'null'    => true,
                'comment' => 'tbl_Processing_Jobs.id — null for background poller calls',
            ],
            'call_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'comment'    => 'classify | extract',
            ],
            'model' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'provider' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'prompt_tokens' => [
                'type' => 'INT',
                'null' => true,
            ],
            'reply_tokens' => [
                'type' => 'INT',
                'null' => true,
            ],
            'latency_ms' => [
                'type'    => 'INT',
                'null'    => true,
                'comment' => 'Wall-clock time for the HTTP call in milliseconds',
            ],
            'batch_size' => [
                'type'    => 'INT',
                'null'    => true,
                'comment' => 'Number of SMS messages sent in this call',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'ok',
                'comment'    => 'ok | error | fallback',
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('job_id', false, false, 'idx_llmc_job');
        $this->forge->addKey('call_type', false, false, 'idx_llmc_type');
        $this->forge->addKey('created_at', false, false, 'idx_llmc_created');
        $this->forge->createTable('tbl_LLM_Calls', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_LLM_Calls', true);
    }
}
