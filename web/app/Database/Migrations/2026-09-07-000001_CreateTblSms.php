<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Squashed baseline migration for tbl_Sms.
 *
 * Final schema derived from:
 *   2026-06-22-000002_CreateTblSms
 *   2026-07-15-170001_AddFinanceColumnsToTblSms   (finance cols)
 *   2026-07-15-200000_DropSmsCategoryColumn        (removed sms_category)
 *   2026-08-10-010001_AddUserIdToTblSms            (sms_user_id + unique key)
 */
class CreateTblSms extends Migration
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
            'sms_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'sms_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'sms_thread_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'sms_time' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'sms_seen' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'sms__id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'sms_body' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'sms_loot_source' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'sms_owner' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'sms_user_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'sms_device' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            // Finance analysis columns
            'sms_direction' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'sent | received | none',
            ],
            'sms_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
            ],
            'sms_balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
            ],
            'sms_counterparty' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'sms_transaction_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'sms_category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => 'Unclassified',
            ],
            'sms_is_transactional' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => true,
            ],
            // Classification denorm cols (CanonicalizeSmsAnalysis)
            'sms_is_finance' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => true,
            ],
            'sms_method' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'sms_confidence' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,4',
                'null'       => true,
            ],
            'sms_trans_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'sms_trans_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            // Device tracking cols (AddDeviceTrackingColumns)
            'device_first_seen_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'device_last_seen_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'device_session_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            // Finance extraction cols (AddExtractedFinanceFields)
            'sms_fee' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
                'null'       => true,
            ],
            'sms_is_reversal' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => true,
            ],
            'sms_is_loan' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => true,
            ],
            // AI advisor insight cols (AddAdvisorInsightFields)
            'sms_counterparty_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'sms_is_abnormal' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => true,
            ],
            'sms_normality_assessment' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'sms_savings_impact' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'sms_advisor_insight' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('sms_time');
        $this->forge->addKey('sms_number');
        $this->forge->addKey('sms_owner');
        $this->forge->addKey('sms_user_id');
        $this->forge->addUniqueKey(['sms_device', 'sms__id'], 'uq_tbl_Sms_sms_device_sms__id');
        $this->forge->createTable('tbl_Sms', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_Sms', true);
    }
}
