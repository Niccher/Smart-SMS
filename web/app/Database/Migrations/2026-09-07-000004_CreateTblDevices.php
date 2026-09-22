<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Squashed baseline migration for tbl_Devices.
 *
 * Final schema derived from:
 *   2026-06-22-000007_CreateTblDevices
 *   2026-08-10-000000_AddDeviceFingerprintFields  (permission-free fingerprint cols)
 *   2026-08-10-010005_AddUserIdToTblDevices        (device_user_id)
 */
class CreateTblDevices extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'device_Uuid'         => ['type' => 'VARCHAR', 'constraint' => 100,  'null' => true],
            'device_user_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'device_ip'           => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'device_Device'       => ['type' => 'VARCHAR', 'constraint' => 255,  'null' => true],
            'device_Created_At'   => ['type' => 'DATETIME', 'null' => true],
            'device_Product'      => ['type' => 'VARCHAR', 'constraint' => 100,  'null' => true],
            'device_Bootloader'   => ['type' => 'VARCHAR', 'constraint' => 100,  'null' => true],
            'device_Type'         => ['type' => 'VARCHAR', 'constraint' => 50,   'null' => true],
            'device_Tags'         => ['type' => 'TEXT', 'null' => true],
            'device_Host'         => ['type' => 'VARCHAR', 'constraint' => 255,  'null' => true],
            'device_Display'      => ['type' => 'VARCHAR', 'constraint' => 255,  'null' => true],
            'device_Hardware'     => ['type' => 'VARCHAR', 'constraint' => 100,  'null' => true],
            'device_Fingerprint'  => ['type' => 'TEXT', 'null' => true],
            'device_Manufacturer' => ['type' => 'VARCHAR', 'constraint' => 100,  'null' => true],
            'device_Brand'        => ['type' => 'VARCHAR', 'constraint' => 100,  'null' => true],
            'device_Board'        => ['type' => 'VARCHAR', 'constraint' => 100,  'null' => true],
            'device_User'         => ['type' => 'VARCHAR', 'constraint' => 100,  'null' => true],
            'device_Model'        => ['type' => 'VARCHAR', 'constraint' => 100,  'null' => true],
            'device_Time'         => ['type' => 'BIGINT', 'null' => true],
            'device_Serial'       => ['type' => 'VARCHAR', 'constraint' => 100,  'null' => true],
            // Permission-free fingerprint signals (DeviceFingerprint.kt)
            'device_AndroidId'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'device_AppCertHash'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'device_AppVersion'       => ['type' => 'VARCHAR', 'constraint' => 50,  'null' => true],
            'device_FirstInstallTime' => ['type' => 'VARCHAR', 'constraint' => 50,  'null' => true],
            'device_LastUpdateTime'   => ['type' => 'VARCHAR', 'constraint' => 50,  'null' => true],
            'device_Sensors'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'device_ScreenWidth'      => ['type' => 'INT', 'null' => true],
            'device_ScreenHeight'     => ['type' => 'INT', 'null' => true],
            'device_DensityDpi'       => ['type' => 'INT', 'null' => true],
            'device_Xdpi'             => ['type' => 'FLOAT', 'null' => true],
            'device_Ydpi'             => ['type' => 'FLOAT', 'null' => true],
            'device_Locale'           => ['type' => 'VARCHAR', 'constraint' => 50,  'null' => true],
            'device_Timezone'         => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'device_CpuCount'         => ['type' => 'INT', 'null' => true],
            'device_Abis'             => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'device_StorageTotal'     => ['type' => 'BIGINT', 'null' => true],
            'device_StorageAvailable' => ['type' => 'BIGINT', 'null' => true],
            'device_BatteryCapacity'  => ['type' => 'INT', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('device_Uuid');
        $this->forge->addKey('device_user_id');
        $this->forge->addKey('device_ip');
        $this->forge->addKey('device_AndroidId');
        $this->forge->createTable('tbl_Devices', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_Devices', true);
    }
}
