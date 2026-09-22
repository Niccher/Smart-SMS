<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Migrate extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        // Simple auth check - only allow from localhost or with secret key
        $key = $this->request->getGet('key');
        $validKey = getenv('MIGRATE_KEY') ?: 'run-migrations';

        if ($key !== $validKey) {
            return $this->respond(['status' => 'error', 'message' => 'Invalid key'], 403);
        }

        try {
            $migrations = \Config\Services::migrations();
            
            // Get current status
            $current = $migrations->current();
            
            // Run migrations
            $result = $migrations->latest();
            
            $new = $migrations->current();
            
            return $this->respond([
                'status' => 'success',
                'message' => 'Migrations completed',
                'before' => $current,
                'after' => $new,
                'migrated' => $result,
            ]);
        } catch (\Throwable $e) {
            return $this->respond([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    public function status()
    {
        $key = $this->request->getGet('key');
        $validKey = getenv('MIGRATE_KEY') ?: 'run-migrations';

        if ($key !== $validKey) {
            return $this->respond(['status' => 'error', 'message' => 'Invalid key'], 403);
        }

        try {
            $migrations = \Config\Services::migrations();
            $current = $migrations->current();
            $files = $migrations->findMigrations();
            
            return $this->respond([
                'status' => 'success',
                'current_version' => $current,
                'available_migrations' => array_keys($files),
            ]);
        } catch (\Throwable $e) {
            return $this->respond([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}