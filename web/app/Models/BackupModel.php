<?php

namespace App\Models;

use CodeIgniter\Model;

class BackupModel extends Model
{
    protected $table      = 'tbl_Backups';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'filename', 'filepath', 'file_size', 'structure_only',
        'compressed', 'type', 'created_by', 'created_at'
    ];
    protected $useTimestamps = false;

    public function logBackup(array $data): int
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('tbl_Backups')) {
            $db->query("CREATE TABLE IF NOT EXISTS tbl_Backups (
                id INT AUTO_INCREMENT PRIMARY KEY,
                filename VARCHAR(255) NOT NULL,
                filepath VARCHAR(255),
                file_size BIGINT DEFAULT 0,
                structure_only TINYINT(1) DEFAULT 0,
                compressed TINYINT(1) DEFAULT 0,
                type ENUM('manual', 'scheduled') DEFAULT 'manual',
                created_by VARCHAR(100),
                created_at DATETIME
            )");
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    public function getHistory(): array
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('tbl_Backups')) {
            return [];
        }
        return $this->orderBy('created_at', 'DESC')->findAll();
    }
}
