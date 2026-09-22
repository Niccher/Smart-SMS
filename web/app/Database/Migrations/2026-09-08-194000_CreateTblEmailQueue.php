<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblEmailQueue extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'trigger'      => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => ''],
            'to_email'     => ['type' => 'VARCHAR', 'constraint' => 255, 'default' => ''],
            'subject'      => ['type' => 'VARCHAR', 'constraint' => 255, 'default' => ''],
            'body_html'    => ['type' => 'LONGTEXT', 'null' => true],
            'body_text'    => ['type' => 'TEXT', 'null' => true],
            'status'       => ['type' => 'ENUM', 'constraint' => ['pending', 'sending', 'sent', 'failed'], 'default' => 'pending'],
            'attempts'     => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true, 'default' => 0],
            'max_attempts' => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true, 'default' => 5],
            'last_error'   => ['type' => 'TEXT', 'null' => true],
            'scheduled_at' => ['type' => 'DATETIME', 'null' => true],
            'sent_at'      => ['type' => 'DATETIME', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('trigger');
        $this->forge->addKey('to_email');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        $this->forge->createTable('tbl_Email_Queue', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_Email_Queue', true);
    }
}
