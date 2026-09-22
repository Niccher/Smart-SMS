<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\Audit;
use CodeIgniter\API\ResponseTrait;

class Maintenance extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $cacheInfo = $this->getCacheInfo();
        $sessionInfo = $this->getSessionInfo();

        return view('Admin/Maintenance/index', [
            'bg_color' => '#B1B8ED',
            'cache_info' => $cacheInfo,
            'session_info' => $sessionInfo,
            'retention_days' => $this->getRetentionDays(),
        ]);
    }

    public function saveRetention()
    {
        $days = (int) $this->request->getPost('retention_days');

        if ($days < 0) {
            return $this->respond(['status' => 'error', 'message' => 'Retention days cannot be negative.'], 400);
        }

        $db = \Config\Database::connect();
        $db->table('tbl_Settings')->upsert([
            'key' => 'data_retention_days',
            'value' => (string) $days,
            'type' => 'int',
            'description' => 'Number of days to keep uploaded data before it is purged (0 disables auto-purge).',
        ]);

        Audit::log('retention_update', 'system', 'Updated data retention window', [
            'retention_days' => $days,
        ]);

        return $this->respond([
            'status' => 'success',
            'message' => $days > 0
                ? "Data retention set to {$days} days. The data:retention cron job will purge older uploads."
                : 'Data retention disabled. Uploaded data will be kept indefinitely unless purged manually.',
        ]);
    }

    private function getRetentionDays(): int
    {
        $db = \Config\Database::connect();
        $row = $db->table('tbl_Settings')
            ->where('`key`', 'data_retention_days')
            ->get()
            ->getRow();

        return $row ? (int) $row->value : 365;
    }

    public function clearCache()
    {
        try {
            $cache = \Config\Services::cache();
            $cleared = $cache->clean();

            // Also clear view cache files
            $viewCachePath = WRITEPATH . 'cache';
            $viewCleared = 0;
            if (is_dir($viewCachePath)) {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($viewCachePath, \FilesystemIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach ($files as $file) {
                    if ($file->isFile()) {
                        @unlink($file->getPathname());
                        $viewCleared++;
                    }
                }
                // Remove empty directories
                foreach ($files as $file) {
                    if ($file->isDir()) {
                        @rmdir($file->getPathname());
                    }
                }
            }

            Audit::log('cache_clear', 'system', 'Cleared application cache', [
                'cache_driver' => get_class($cache),
                'view_files_cleared' => $viewCleared,
            ]);

            return $this->respond([
                'status' => 'success',
                'message' => 'Cache cleared successfully. Driver: ' . $cleared . ' entries, View files: ' . $viewCleared,
            ]);
        } catch (\Throwable $e) {
            return $this->respond(['status' => 'error', 'message' => 'Failed to clear cache: ' . $e->getMessage()], 500);
        }
    }

    public function clearSessions()
    {
        try {
            $session = \Config\Services::session();
            $session->destroy();

            // Clear session files
            $sessionPath = WRITEPATH . 'session';
            $filesCleared = 0;
            if (is_dir($sessionPath)) {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($sessionPath, \FilesystemIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach ($files as $file) {
                    if ($file->isFile()) {
                        @unlink($file->getPathname());
                        $filesCleared++;
                    }
                }
            }

            Audit::log('session_clear', 'system', 'Cleared all sessions', [
                'files_cleared' => $filesCleared,
            ]);

            return $this->respond([
                'status' => 'success',
                'message' => 'All sessions cleared. Files removed: ' . $filesCleared,
            ]);
        } catch (\Throwable $e) {
            return $this->respond(['status' => 'error', 'message' => 'Failed to clear sessions: ' . $e->getMessage()], 500);
        }
    }

    public function cleanExpiredSessions()
    {
        try {
            $db = \Config\Database::connect();
            $table = $db->prefixTable('ci_sessions');

            if (!$db->tableExists('ci_sessions')) {
                return $this->respond(['status' => 'success', 'message' => 'Session table does not exist.']);
            }

            // Delete sessions older than 2 hours (7200 seconds)
            $cutoff = time() - 7200;
            $result = $db->table('ci_sessions')
                ->where('timestamp <', $cutoff)
                ->delete();

            $deleted = $db->affectedRows();

            Audit::log('session_cleanup', 'system', 'Cleaned expired sessions', [
                'deleted_count' => $deleted,
                'cutoff_timestamp' => $cutoff,
            ]);

            return $this->respond([
                'status' => 'success',
                'message' => "Cleaned {$deleted} expired session(s) (older than 2 hours).",
            ]);
        } catch (\Throwable $e) {
            return $this->respond(['status' => 'error', 'message' => 'Failed to clean sessions: ' . $e->getMessage()], 500);
        }
    }

    private function getCacheInfo(): array
    {
        $cache = \Config\Services::cache();
        $driver = get_class($cache);

        $info = [
            'driver' => $driver,
            'path' => WRITEPATH . 'cache',
            'exists' => is_dir(WRITEPATH . 'cache'),
            'file_count' => 0,
            'size_bytes' => 0,
        ];

        if ($info['exists']) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($info['path'], \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $info['file_count']++;
                    $info['size_bytes'] += $file->getSize();
                }
            }
        }

        $info['size_human'] = $this->humanSize($info['size_bytes']);
        return $info;
    }

    private function getSessionInfo(): array
    {
        $db = \Config\Database::connect();
        $info = [
            'handler' => ini_get('session.save_handler'),
            'path' => WRITEPATH . 'session',
            'file_count' => 0,
            'size_bytes' => 0,
            'db_sessions' => 0,
            'expired_sessions' => 0,
        ];

        // File sessions
        if (is_dir($info['path'])) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($info['path'], \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $info['file_count']++;
                    $info['size_bytes'] += $file->getSize();
                }
            }
        }

        // Database sessions
        if ($db->tableExists('ci_sessions')) {
            $info['db_sessions'] = (int) $db->table('ci_sessions')->countAllResults();
            $cutoff = time() - 7200;
            $info['expired_sessions'] = (int) $db->table('ci_sessions')
                ->where('timestamp <', $cutoff)
                ->countAllResults();
        }

        $info['size_human'] = $this->humanSize($info['size_bytes']);
        return $info;
    }

    private function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}