<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Squashed baseline migration for tbl_Sms_Classification.
 *
 * Final schema derived from:
 *   2026-07-15-190000_CreateSmsClassification
 *   2026-08-10-010003_AddUserIdToTblSmsClassification  (user_id)
 */
class CreateSmsClassification extends Migration
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
            'sms_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'user_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'sender' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'default'    => 'MPESA',
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'direction' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'incoming | outgoing | none',
            ],
            'is_finance' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'method' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'default'    => 'pattern',
            ],
            'confidence' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,4',
                'default'    => 1.0000,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('sms_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('sender');
        $this->forge->addKey('is_finance');
        $this->forge->addForeignKey('sms_id', 'tbl_Sms', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tbl_Sms_Classification', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_Sms_Classification', true);
    }
}
