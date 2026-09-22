<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Squashed baseline migration for tbl_Loot_Summary.
 *
 * Final schema derived from:
 *   2026-06-22-000006_CreateTblLootSummary
 *   2026-07-14-220000_FixTblLootSummaryColumns     (info_Get_from_MPESA, info_All)
 *   2026-07-18-000000_AddLLMBreakdownColumns        (direction_breakdown, transaction_type_breakdown)
 *   2026-08-10-010004_AddUserIdToTblLootSummary     (user_id)
 */
class CreateTblLootSummary extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'loot_Uuid'                     => ['type' => 'VARCHAR', 'constraint' => 100,  'null' => true],
            'user_id'                       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'loot_Created'                  => ['type' => 'DATETIME', 'null' => true],
            'info_Get_from_MPESA'           => ['type' => 'INT', 'default' => 0],
            'info_Get_Received'             => ['type' => 'INT', 'default' => 0],
            'info_Get_from_Mshwari'         => ['type' => 'INT', 'default' => 0],
            'info_Get_from_NCBA'            => ['type' => 'INT', 'default' => 0],
            'info_Get_from_KCB'             => ['type' => 'INT', 'default' => 0],
            'info_Get_from_IM'              => ['type' => 'INT', 'default' => 0],
            'info_Get_from_Reversal'        => ['type' => 'INT', 'default' => 0],
            'info_Get_Bal_MPESA'            => ['type' => 'INT', 'default' => 0],
            'info_Get_Bal_KCB'              => ['type' => 'INT', 'default' => 0],
            'info_Get_Bal_Mshwari'          => ['type' => 'INT', 'default' => 0],
            'info_Loan_Limit'               => ['type' => 'INT', 'default' => 0],
            'info_Sent_to_MPESA'            => ['type' => 'INT', 'default' => 0],
            'info_Sent_to_LNM'              => ['type' => 'INT', 'default' => 0],
            'info_Sent_Mini'                => ['type' => 'INT', 'default' => 0],
            'info_Sent_to_Mshwari'          => ['type' => 'INT', 'default' => 0],
            'info_Sent_Cancel'              => ['type' => 'INT', 'default' => 0],
            'info_Error_Failed'             => ['type' => 'INT', 'default' => 0],
            'info_Error_Pin'                => ['type' => 'INT', 'default' => 0],
            'info_Error_Less'               => ['type' => 'INT', 'default' => 0],
            'info_Error_Receiver'           => ['type' => 'INT', 'default' => 0],
            'info_Error_Receiver_Org'       => ['type' => 'INT', 'default' => 0],
            'info_Withdraw'                 => ['type' => 'INT', 'default' => 0],
            'info_Fuliza_Opt_Out'           => ['type' => 'INT', 'default' => 0],
            'info_Fuliza_Opt_In'            => ['type' => 'INT', 'default' => 0],
            'info_Fuliza_Limit'             => ['type' => 'INT', 'default' => 0],
            'info_Fuliza_Loan_Paid'         => ['type' => 'INT', 'default' => 0],
            'info_Fuliza_Mini_Statement'    => ['type' => 'INT', 'default' => 0],
            'info_Fuliza_Loan_Taken'        => ['type' => 'INT', 'default' => 0],
            'info_Similar_Transaction'      => ['type' => 'INT', 'default' => 0],
            'info_Unknown'                  => ['type' => 'INT', 'default' => 0],
            'info_All'                      => ['type' => 'INT', 'default' => 0],
            'direction_breakdown'           => ['type' => 'TEXT', 'null' => true],
            'transaction_type_breakdown'    => ['type' => 'TEXT', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('loot_Uuid');
        $this->forge->addKey('user_id');
        $this->forge->createTable('tbl_Loot_Summary', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_Loot_Summary', true);
    }
}
