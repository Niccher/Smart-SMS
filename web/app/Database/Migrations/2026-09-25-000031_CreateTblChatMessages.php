<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration for tbl_Chat_Messages.
 * Stores conversation history between users and AI financial assistant across Web and Mobile.
 */
class CreateTblChatMessages extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'role' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'comment'    => 'user | assistant',
            ],
            'message' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'platform' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'webapp',
                'null'       => false,
                'comment'    => 'webapp | mobile',
            ],
            'device_info' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Client User-Agent or device model (e.g. TECNO CM6)',
            ],
            'app_version' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
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
            'tokens_used' => [
                'type' => 'INT',
                'null' => true,
            ],
            'latency_ms' => [
                'type' => 'INT',
                'null' => true,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['user_id', 'created_at'], false, false, 'idx_chat_user_created');
        $this->forge->addKey('platform', false, false, 'idx_chat_platform');
        $this->forge->createTable('tbl_Chat_Messages', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_Chat_Messages', true);
    }
}
