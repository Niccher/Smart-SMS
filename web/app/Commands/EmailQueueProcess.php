<?php

namespace App\Commands;

use App\Libraries\Notifier;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class EmailQueueProcess extends BaseCommand
{
    protected $group       = 'Notifications';
    protected $name        = 'email:process';
    protected $description = 'Process pending email notifications in the queue and retry failed sends.';

    public function run(array $params)
    {
        $limit = isset($params[0]) ? (int)$params[0] : 25;
        if ($limit < 1) {
            $limit = 25;
        }

        CLI::write("Processing email queue (limit: {$limit})...", 'yellow');

        $result = Notifier::processQueue($limit);

        if ($result['processed'] === 0) {
            CLI::write($result['message'], 'green');
            return;
        }

        CLI::write("Processed: {$result['processed']}, Sent: {$result['sent']}, Failed: {$result['failed']}", $result['failed'] > 0 ? 'yellow' : 'green');
        CLI::write($result['message'], 'green');
    }
}
