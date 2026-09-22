<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Squashed baseline migration for tbl_User_Devices.
 *
 * Source: 2026-06-22-000008_CreateTblUserDevices (unchanged)
 */
class CreateTblUserDevices extends Migration
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
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'device_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'device_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('device_token');
        $this->forge->addKey(['user_id', 'device_token']);
        $this->forge->createTable('tbl_User_Devices', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_User_Devices', true);
    }
}
