<?php

namespace App\Models;

use CodeIgniter\Model;

class TagModel extends Model
{
    protected $table      = 'tbl_Transaction_Tags';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'name', 'color', 'created_at'];
    protected $useTimestamps = false;

    public function getForUser(int $userId): array
    {
        return $this->where('user_id', $userId)->findAll();
    }

    public function saveTag(int $userId, array $data): int
    {
        $data['user_id'] = $userId;
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    public function deleteTag(int $id, int $userId): bool
    {
        $this->where('id', $id)->where('user_id', $userId)->delete();
        \Config\Database::connect()->table('tbl_Transaction_Tag_Map')
            ->where('tag_id', $id)->delete();
        return true;
    }
}
