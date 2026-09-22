<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SessionGc extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'session:gc';
    protected $description = 'Clean up expired sessions, remember tokens, and stale token logins.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $deleted = 0;

        $tables = [
            'auth_remember_tokens' => 'expires',
            'auth_token_logins' => 'date',
        ];

        foreach ($tables as $table => $col) {
            if ($db->tableExists($table)) {
                $fields = $db->getFieldNames($table);
                if (in_array($col, $fields, true)) {
                    $count = $db->table($table)->where($col . ' <', date('Y-m-d H:i:s'))->delete();
                    $deleted += $count;
                }
            }
        }

        // Clean expired app auth tokens
        if ($db->tableExists('app_auth_tokens')) {
            $fields = $db->getFieldNames('app_auth_tokens');
            if (in_array('expires_at', $fields, true)) {
                $count = $db->table('app_auth_tokens')->where('expires_at <', date('Y-m-d H:i:s'))->delete();
                $deleted += $count;
            }
        }

        CLI::write('Session cleanup complete. ' . $deleted . ' expired records removed.', 'green');
    }
}
