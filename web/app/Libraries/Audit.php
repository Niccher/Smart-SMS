<?php

namespace App\Libraries;

/**
 * Audit logging helper for admin actions.
 */
class Audit
{
    private const TABLE = 'tbl_Audit_Log';

    /**
     * Record an admin action.
     *
     * @param string $action        Action identifier (e.g. 'user_delete', 'maintenance_toggle')
     * @param string $category      Category: auth, user, config, data, system
     * @param string $description   Human-readable description
     * @param array  $metadata      Additional context data
     * @param int    $userId        User ID (null = current auth user)
     */
    public static function log(
        string $action,
        string $category,
        string $description = '',
        array $metadata = [],
        ?int $userId = null
    ): void {
        try {
            $db = \Config\Database::connect();

            if (!$db->tableExists(self::TABLE)) {
                return;
            }

            if ($userId === null) {
                $userId = \auth()->id();
            }

            $db->table(self::TABLE)->insert([
                'user_id'         => $userId,
                'ip'              => \Config\Services::request()->getIPAddress() ?? '',
                'user_agent'      => \Config\Services::request()->getUserAgent() ?? '',
                'action'          => $action,
                'action_category' => $category,
                'description'     => $description,
                'metadata'        => $metadata ? json_encode($metadata) : null,
                'created_at'      => gmdate('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // Auditing must never break the main flow
            log_message('error', 'Audit log failed: ' . $e->getMessage());
        }
    }

    /**
     * Get audit log entries with filters.
     *
     * @return array<int,array>
     */
    public static function getEntries(
        int $limit = 50,
        int $offset = 0,
        ?string $category = null,
        ?int $userId = null,
        ?string $action = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {
        $db = \Config\Database::connect();

        if (!$db->tableExists(self::TABLE)) {
            return [];
        }

        $builder = $db->table(self::TABLE)
            ->select('tbl_Audit_Log.*, users.username')
            ->join('users', 'users.id = tbl_Audit_Log.user_id', 'left')
            ->orderBy('tbl_Audit_Log.id', 'DESC')
            ->limit($limit, $offset);

        if ($category !== null) {
            $builder->where('action_category', $category);
        }
        if ($userId !== null) {
            $builder->where('user_id', $userId);
        }
        if ($action !== null) {
            $builder->where('action', $action);
        }
        if ($dateFrom !== null) {
            $builder->where('created_at >=', $dateFrom);
        }
        if ($dateTo !== null) {
            $builder->where('created_at <=', $dateTo . ' 23:59:59');
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Count audit log entries with same filters.
     */
    public static function countEntries(
        ?string $category = null,
        ?int $userId = null,
        ?string $action = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): int {
        $db = \Config\Database::connect();

        if (!$db->tableExists(self::TABLE)) {
            return 0;
        }

        $builder = $db->table(self::TABLE)->selectCount('id');

        if ($category !== null) $builder->where('action_category', $category);
        if ($userId !== null) $builder->where('user_id', $userId);
        if ($action !== null) $builder->where('action', $action);
        if ($dateFrom !== null) $builder->where('created_at >=', $dateFrom);
        if ($dateTo !== null) $builder->where('created_at <=', $dateTo . ' 23:59:59');

        return (int) $builder->get()->getRow()->id ?? 0;
    }

    /**
     * Get distinct action categories.
     */
    public static function getCategories(): array
    {
        $db = \Config\Database::connect();

        if (!$db->tableExists(self::TABLE)) {
            return ['auth', 'user', 'config', 'data', 'system'];
        }

        $rows = $db->table(self::TABLE)
            ->select('action_category, COUNT(*) as cnt')
            ->groupBy('action_category')
            ->get()
            ->getResultArray();

        $cats = [];
        foreach ($rows as $r) {
            $cats[$r['action_category']] = (int)$r['cnt'];
        }
        return $cats;
    }

    /**
     * Get distinct actions for a category.
     */
    public static function getActions(?string $category = null): array
    {
        $db = \Config\Database::connect();

        if (!$db->tableExists(self::TABLE)) {
            return [];
        }

        $builder = $db->table(self::TABLE)
            ->select('action, COUNT(*) as cnt')
            ->groupBy('action')
            ->orderBy('cnt', 'DESC');

        if ($category !== null) {
            $builder->where('action_category', $category);
        }

        $rows = $builder->get()->getResultArray();

        $actions = [];
        foreach ($rows as $r) {
            $actions[$r['action']] = (int)$r['cnt'];
        }
        return $actions;
    }
}

// Helper function for convenience
if (!function_exists('audit')) {
    /**
     * @see Audit::log()
     */
    function audit(
        string $action,
        string $category,
        string $description = '',
        array $metadata = [],
        ?int $userId = null
    ): void {
        \App\Libraries\Audit::log($action, $category, $description, $metadata, $userId);
    }
}