<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Squashed baseline migration for tbl_User_Settings.
 *
 * Final schema derived from:
 *   2026-07-25-000000_CreateUserSettingsTable
 *   2026-07-25-160000_AddReportScheduleColumns   (report schedule cols)
 *   2026-08-08-010000_AddReportLastSentColumn     (report_schedule_last_sent)
 */
class CreateUserSettingsTable extends Migration
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
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'currency' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'default'    => 'KES',
            ],
            'date_format' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'Y-m-d',
            ],
            'time_format' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'default'    => 'H:i',
            ],
            'default_budget_period' => [
                'type'       => 'ENUM',
                'constraint' => ['monthly', 'weekly'],
                'default'    => 'monthly',
            ],
            'budget_alert_threshold' => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 80,
            ],
            'dashboard_widgets' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'notify_email_alerts' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'notify_budget_alerts' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'notify_low_balance' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'notify_unusual_activity' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'export_default_format' => [
                'type'       => 'ENUM',
                'constraint' => ['csv', 'json'],
                'default'    => 'csv',
            ],
            // Report schedule columns
            'report_schedule_enabled' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'report_schedule_frequency' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'monthly',
            ],
            'report_schedule_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'report_schedule_day' => [
                'type'       => 'INT',
                'constraint' => 2,
                'default'    => 1,
            ],
            'report_schedule_format' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'default'    => 'pdf',
            ],
            'report_schedule_last_sent' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tbl_User_Settings', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_User_Settings', true);
    }
}
