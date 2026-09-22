<?php

namespace App\Commands;

use App\Libraries\Audit;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class LogsPrune extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'logs:prune';
    protected $description = 'Prune old entries from tbl_Audit_Log, tbl_Cron_Log, and tbl_Email_Log older than specified retention days (default 90 days).';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $days = (int)($params[0] ?? 90);
        if ($days < 7) {
            $days = 90;
        }

        $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $pruned = [
            'audit' => 0,
            'cron'  => 0,
            'email' => 0,
        ];

        if ($db->tableExists('tbl_Audit_Log')) {
            $builder = $db->table('tbl_Audit_Log');
            $builder->where('created_at <', $cutoff)->delete();
            $pruned['audit'] = $db->affectedRows();
        }

        if ($db->tableExists('tbl_Cron_Log')) {
            $builder = $db->table('tbl_Cron_Log');
            $builder->where('ran_at <', $cutoff)->delete();
            $pruned['cron'] = $db->affectedRows();
        }

        if ($db->tableExists('tbl_Email_Log')) {
            $builder = $db->table('tbl_Email_Log');
            $builder->where('sent_at <', $cutoff)->delete();
            $pruned['email'] = $db->affectedRows();
        }

        Audit::log('logs_prune', 'system', 'Pruned log tables older than ' . $days . ' days', $pruned);

        CLI::write("Logs prune complete (cutoff: {$cutoff}). Pruned - Audit: {$pruned['audit']}, Cron: {$pruned['cron']}, Email: {$pruned['email']}.", 'green');
    }
}
