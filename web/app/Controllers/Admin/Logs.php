<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\Audit;
use CodeIgniter\API\ResponseTrait;

class Logs extends BaseController
{
    use ResponseTrait;

    private const LOG_DIR = WRITEPATH . 'logs';
    private const LEVELS = ['DEBUG', 'INFO', 'WARNING', 'ERROR', 'CRITICAL'];

    public function index()
    {
        $files = $this->listLogFiles();

        return view('Admin/Logs/index', [
            'bg_color' => '#B1B8ED',
            'log_files' => $files,
        ]);
    }

    public function view(string $file = '')
    {
        if ($file === '') {
            return $this->respond(['status' => 'error', 'message' => 'File not specified'], 400);
        }

        // Security: only allow files in log directory
        $path = realpath(self::LOG_DIR . DIRECTORY_SEPARATOR . $file);
        $logDir = realpath(self::LOG_DIR);
        if ($path === false || strpos($path, $logDir) !== 0 || !is_file($path)) {
            return $this->respond(['status' => 'error', 'message' => 'File not found'], 404);
        }

        $lines = (int)($this->request->getGet('lines') ?? 200);
        $level = $this->request->getGet('level') ?? '';
        $search = $this->request->getGet('search') ?? '';

        $content = $this->tailFile($path, $lines, $level, $search);

        return $this->respond([
            'status' => 'success',
            'file' => $file,
            'content' => $content,
            'lines' => $lines,
            'level' => $level,
            'search' => $search,
        ]);
    }

    public function download(string $file = '')
    {
        if ($file === '') {
            return $this->respond(['status' => 'error', 'message' => 'File not specified'], 400);
        }

        $path = realpath(self::LOG_DIR . DIRECTORY_SEPARATOR . $file);
        $logDir = realpath(self::LOG_DIR);
        if ($path === false || strpos($path, $logDir) !== 0 || !is_file($path)) {
            return $this->respond(['status' => 'error', 'message' => 'File not found'], 404);
        }

        return $this->response
            ->setHeader('Content-Type', 'text/plain')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $file . '"')
            ->setBody(file_get_contents($path));
    }

    public function delete()
    {
        $file = $this->request->getPost('file');
        if ($file === '') {
            return $this->respond(['status' => 'error', 'message' => 'File not specified'], 400);
        }

        // Security: only allow files in log directory
        $logDir = realpath(self::LOG_DIR);
        if ($logDir === false) {
            return $this->respond(['status' => 'error', 'message' => 'Log directory not accessible'], 500);
        }

        $requestedPath = $logDir . DIRECTORY_SEPARATOR . $file;
        $path = realpath($requestedPath);
        
        if ($path === false || strpos($path, $logDir) !== 0 || !is_file($path)) {
            // Debug info
            log_message('error', 'Log delete failed: file=' . $file . ', logDir=' . $logDir . ', requestedPath=' . $requestedPath . ', realpath=' . ($path ?? 'false'));
            return $this->respond(['status' => 'error', 'message' => 'File not found: ' . $file], 404);
        }

        if (unlink($path)) {
            Audit::log('log_delete', 'system', 'Deleted log file', ['file' => $file]);
            return $this->respond(['status' => 'success', 'message' => 'Log file deleted']);
        }

        return $this->respond(['status' => 'error', 'message' => 'Failed to delete file'], 500);
    }

    public function deleteAll()
    {
        $dir = realpath(self::LOG_DIR);
        if ($dir === false || !is_dir($dir)) {
            return $this->respond(['status' => 'success', 'message' => 'No log directory']);
        }

        $deleted = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->isFile()) {
                @unlink($file->getPathname());
                $deleted++;
            }
        }

        Audit::log('log_delete_all', 'system', 'Deleted all log files', ['count' => $deleted]);

        return $this->respond(['status' => 'success', 'message' => "Deleted {$deleted} log file(s)"]);
    }

    /**
     * List log files in writable/logs
     *
     * @return array<int,array{name:string,size:int,modified:string}>
     */
    private function listLogFiles(): array
    {
        $dir = self::LOG_DIR;
        if (!is_dir($dir)) {
            return [];
        }

        $files = [];
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->isFile()) {
                $files[] = [
                    'name' => $file->getBasename(),
                    'size' => $file->getSize(),
                    'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                    'human_size' => $this->humanSize($file->getSize()),
                ];
            }
        }

        // Sort by modified desc
        usort($files, fn($a, $b) => strcmp($b['modified'], $a['modified']));

        return $files;
    }

    /**
     * Read last N lines from file, optionally filtered by level and search.
     */
    private function tailFile(string $path, int $lines = 200, string $level = '', string $search = ''): string
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            return 'Cannot open file';
        }

        $buffer = [];
        $lineCount = 0;
        $levels = self::LEVELS;

        // Read from end using fseek for large files
        $size = filesize($path);
        $chunkSize = 4096;
        $offset = 0;
        $data = '';

        // Simple approach for now - read all lines if file is small, otherwise seek from end
        if ($size > 1024 * 1024) { // 1MB+
            // Read last 5MB max
            $readSize = min(5 * 1024 * 1024, $size);
            fseek($handle, -$readSize, SEEK_END);
            // Skip first partial line
            fgets($handle);
        } else {
            rewind($handle);
        }

        while (($line = fgets($handle)) !== false) {
            $line = rtrim($line);
            if ($line === '') continue;

            // Filter by level
            if ($level !== '' && !preg_match('/\b' . preg_quote($level, '/') . '\b/i', $line)) {
                continue;
            }

            // Filter by search
            if ($search !== '' && stripos($line, $search) === false) {
                continue;
            }

            $buffer[] = $this->highlightLine($line);
            if (++$lineCount >= $lines) break;
        }

        fclose($handle);

        // Reverse to show oldest first
        return implode("\n", array_reverse($buffer));
    }

    /**
     * Add HTML highlighting for log levels.
     */
    private function highlightLine(string $line): string
    {
        $colors = [
            'DEBUG'     => '#94a3b8',
            'INFO'      => '#38bdf8',
            'WARNING'   => '#fbbf24',
            'ERROR'     => '#f87171',
            'CRITICAL'  => '#fda4af',
        ];

        foreach ($colors as $level => $color) {
            if (stripos($line, $level) !== false) {
                return '<span style="color:' . $color . ';font-weight:600;">' . htmlspecialchars($line) . '</span>';
            }
        }
        return htmlspecialchars($line);
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