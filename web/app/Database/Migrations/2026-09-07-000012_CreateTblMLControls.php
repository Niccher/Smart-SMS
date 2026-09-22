<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Squashed baseline migration for tbl_ML_Controls.
 *
 * Source: 2026-07-15-190002_CreateTblMLControls (unchanged)
 */
class CreateTblMLControls extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'control_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'control_value' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addPrimaryKey('control_key');
        $this->forge->createTable('tbl_ML_Controls', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_ML_Controls', true);
    }
}
