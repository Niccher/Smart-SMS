<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Squashed baseline migration for tbl_Loot.
 *
 * Final schema derived from:
 *   2026-06-22-000005_CreateTblLoot
 *   2026-08-10-010002_AddUserIdToTblLoot   (loot_user_id)
 */
class CreateTblLoot extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'loot_Id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'loot_Name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'loot_Device' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'loot_Owner' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'loot_user_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'loot_ip' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'loot_Uuid' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'loot_Created' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('loot_Id');
        $this->forge->addKey('loot_Uuid');
        $this->forge->addKey('loot_Owner');
        $this->forge->addKey('loot_user_id');
        $this->forge->addKey('loot_ip');
        $this->forge->createTable('tbl_Loot', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_Loot', true);
    }
}
