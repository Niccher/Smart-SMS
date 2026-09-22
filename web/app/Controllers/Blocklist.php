<?php

namespace App\Controllers;

use App\Models\UploadModel;

class Blocklist extends BaseController
{
    private UploadModel $uploadsModel;

    public function __construct()
    {
        $this->uploadsModel = new UploadModel();
    }

    /**
     * Default landing — the Blocked senders page.
     */
    public function index()
    {
        return redirect()->to('/dashboard/blocklist/status');
    }

    public function status()
    {
        $userId = auth()->user()->id;
        $tokens = $this->uploadsModel->getOwnedRawTokens($userId);

        $db = \Config\Database::connect();
        
        $allSms = 0;
        $goodSms = 0;
        $badSms = 0;
        
        if (!empty($tokens)) {
            $allSms = $db->table('tbl_Sms')->whereIn('sms_owner', $tokens)->countAllResults();
            $goodSms = $db->table('tbl_Sms')->whereIn('sms_owner', $tokens)->where('sms_is_finance', 1)->countAllResults();
            $badSms = $db->table('tbl_Sms')->whereIn('sms_owner', $tokens)->where('sms_is_finance', 0)->countAllResults();
        }

        $senders = $this->getCategorizedSenders($userId);
        
        $counts = ['blocked' => 0, 'allowed' => 0, 'unknown' => 0];
        foreach ($senders as $s) {
            if ($s['blocked']) {
                $counts['blocked']++;
            } elseif ($s['allowed']) {
                $counts['allowed']++;
            } else {
                $counts['unknown']++;
            }
        }
        
        $blockedSmsCount = 0;
        if (!empty($tokens)) {
            $blockedRows = $db->table('tbl_Blocked_Senders')->where('user_id', $userId)->get()->getResultArray();
            $blockedSendersList = array_map(static fn($r) => strtoupper(trim($r['sender'])), $blockedRows);
            if (!empty($blockedSendersList)) {
                $blockedSmsCount = $db->table('tbl_Sms')
                    ->whereIn('sms_owner', $tokens)
                    ->whereIn('sms_number', $blockedSendersList)
                    ->countAllResults();
            }
        }
        
        $data = [
            'bg_color' => '#B1B8ED',
            'active_tab' => 'status',
            'counts' => $counts,
            'stats' => [
                'all_sms' => $allSms,
                'good_sms' => $goodSms,
                'bad_sms' => $badSms,
                'all_senders' => count($senders),
                'finance_senders' => $counts['allowed'],
                'unverified_senders' => $counts['unknown'],
                'blocked_senders' => $counts['blocked'],
                'blocked_sms' => $blockedSmsCount,
            ]
        ];

        return view('Blocklist/status', $data);
    }

    public function blocked()
    {
        return $this->renderList('blocked');
    }

    public function allowed()
    {
        return $this->renderList('allowed');
    }

    public function unknown()
    {
        return $this->renderList('unknown');
    }

    public function deleteUnwantedSms()
    {
        $userId = auth()->user()->id;
        $tokens = $this->uploadsModel->getOwnedRawTokens($userId);
        if (empty($tokens)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No tokens found.']);
        }
        
        $db = \Config\Database::connect();
        $blockedRows = $db->table('tbl_Blocked_Senders')->where('user_id', $userId)->get()->getResultArray();
        $blockedSenders = array_map(static fn($r) => strtoupper(trim($r['sender'])), $blockedRows);
        
        if (empty($blockedSenders)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No blocked senders to delete messages from.']);
        }
        
        $db->transStart();
        
        $smsRows = $db->table('tbl_Sms')
            ->select('id')
            ->whereIn('sms_owner', $tokens)
            ->whereIn('sms_number', $blockedSenders)
            ->get()
            ->getResultArray();
            
        $smsIds = array_map(static fn($r) => $r['id'], $smsRows);
        
        if (!empty($smsIds)) {
            if ($db->tableExists('tbl_Sms_Processing')) {
                $db->table('tbl_Sms_Processing')->whereIn('sms_id', $smsIds)->delete();
            }
            if ($db->tableExists('tbl_Sms_Classification')) {
                $db->table('tbl_Sms_Classification')->whereIn('sms_id', $smsIds)->delete();
            }
            $db->table('tbl_Sms')->whereIn('id', $smsIds)->delete();
        }
        
        $db->transComplete();
        
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Successfully deleted ' . count($smsIds) . ' SMS messages from unwanted (blocked) senders.'
        ]);
    }

    /**
     * Render a single Blocklist sub-page (tab). Each tab is its own URL but
     * shares the same top navigation, like /admin/ml/models.
     */
    private function renderList(string $tab)
    {
        $senders = $this->getCategorizedSenders(auth()->user()->id);

        $list = [];
        $listMeta = [
            'blocked' => ['title' => 'Blocked Senders', 'icon' => 'fa-ban', 'desc' => 'Senders whose SMS is excluded from your finance intelligence.'],
            'allowed' => ['title' => 'Allowed Senders', 'icon' => 'fa-star', 'desc' => 'Known finance senders allowed by default. Block any you want excluded.'],
            'unknown' => ['title' => 'Unknown Senders', 'icon' => 'fa-question-circle', 'desc' => 'Senders not yet determined — these are subject to classification as finance or non-finance.'],
        ];

        foreach ($senders as $s) {
            if ($tab === 'blocked' && $s['blocked']) {
                $list[] = $s;
            } elseif ($tab === 'allowed' && !$s['blocked'] && $s['allowed']) {
                $list[] = $s;
            } elseif ($tab === 'unknown' && !$s['blocked'] && !$s['allowed']) {
                $list[] = $s;
            }
        }

        // Counts for the tab badges.
        $counts = ['blocked' => 0, 'allowed' => 0, 'unknown' => 0];
        foreach ($senders as $s) {
            if ($s['blocked']) {
                $counts['blocked']++;
            } elseif ($s['allowed']) {
                $counts['allowed']++;
            } else {
                $counts['unknown']++;
            }
        }

        $data = [
            'bg_color' => '#B1B8ED',
            'active_tab' => $tab,
            'meta' => $listMeta[$tab],
            'senders' => $list,
            'counts' => $counts,
            'total' => count($senders),
        ];

        return view('Blocklist/list', $data);
    }

    /**
     * Block a single sender (persist + re-categorise as non-finance).
     */
    public function block()
    {
        $senders = $this->request->getPost('senders')
            ? (array) $this->request->getPost('senders')
            : [$this->request->getPost('sender')];

        $senders = array_values(array_filter(array_map(
            static fn ($s) => strtoupper(trim((string) $s)),
            $senders
        )));

        if (empty($senders)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No sender provided.']);
        }

        return $this->response->setJSON($this->applyBlock(auth()->user()->id, $senders));
    }

    /**
     * Unblock a single sender.
     */
    public function unblock()
    {
        $senders = $this->request->getPost('senders')
            ? (array) $this->request->getPost('senders')
            : [$this->request->getPost('sender')];

        $senders = array_values(array_filter(array_map(
            static fn ($s) => strtoupper(trim((string) $s)),
            $senders
        )));

        if (empty($senders)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No sender provided.']);
        }

        return $this->response->setJSON($this->applyUnblock(auth()->user()->id, $senders));
    }

    /**
     * Bulk block multiple senders.
     */
    public function bulkBlock()
    {
        $senders = $this->request->getPost('senders');
        if (empty($senders) || !is_array($senders)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No senders selected.']);
        }

        $senders = array_values(array_filter(array_map(
            static fn ($s) => strtoupper(trim((string) $s)),
            $senders
        )));

        if (empty($senders)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No senders selected.']);
        }

        return $this->response->setJSON($this->applyBlock(auth()->user()->id, $senders));
    }

    /**
     * Bulk unblock multiple senders.
     */
    public function bulkUnblock()
    {
        $senders = $this->request->getPost('senders');
        if (empty($senders) || !is_array($senders)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No senders selected.']);
        }

        $senders = array_values(array_filter(array_map(
            static fn ($s) => strtoupper(trim((string) $s)),
            $senders
        )));

        if (empty($senders)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No senders selected.']);
        }

        return $this->response->setJSON($this->applyUnblock(auth()->user()->id, $senders));
    }

    /**
     * Allow a single sender (adds to allowed list and resets user flags).
     */
    public function allow()
    {
        $senders = $this->request->getPost('senders')
            ? (array) $this->request->getPost('senders')
            : [$this->request->getPost('sender')];

        $senders = array_values(array_filter(array_map(
            static fn ($s) => strtoupper(trim((string) $s)),
            $senders
        )));

        if (empty($senders)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No sender provided.']);
        }

        return $this->response->setJSON($this->applyAllow(auth()->user()->id, $senders));
    }

    /**
     * Bulk allow multiple senders.
     */
    public function bulkAllow()
    {
        $senders = $this->request->getPost('senders');
        if (empty($senders) || !is_array($senders)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No senders selected.']);
        }

        $senders = array_values(array_filter(array_map(
            static fn ($s) => strtoupper(trim((string) $s)),
            $senders
        )));

        if (empty($senders)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No senders selected.']);
        }

        return $this->response->setJSON($this->applyAllow(auth()->user()->id, $senders));
    }

    private function applyAllow(int $userId, array $senders): array
    {
        $db = \Config\Database::connect();
        $tokens = $this->uploadsModel->getOwnedRawTokens($userId);

        foreach ($senders as $sender) {
            $exists = $db->table('tbl_Allowed_Senders')
                ->where('sender', $sender)
                ->get()
                ->getRow();

            if (!$exists) {
                $db->table('tbl_Allowed_Senders')->insert([
                    'sender' => $sender,
                    'category' => 'Finance',
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $db->table('tbl_Blocked_Senders')
                ->where('user_id', $userId)
                ->where('sender', $sender)
                ->delete();
        }

        $smsUpdated = 0;
        $profilesUpdated = 0;

        if (!empty($tokens)) {
            if ($db->tableExists('tbl_Sms')) {
                $smsUpdated = $db->table('tbl_Sms')
                    ->whereIn('sms_number', $senders)
                    ->whereIn('sms_owner', $tokens)
                    ->set('sms_is_finance', 1)
                    ->update();
            }

            if ($db->tableExists('tbl_Sender_Profiles')) {
                $profilesUpdated = $db->table('tbl_Sender_Profiles')
                    ->whereIn('sp_number', $senders)
                    ->whereIn('sp_owner', $tokens)
                    ->set('sp_is_finance', 1)
                    ->set('sp_updated', date('Y-m-d H:i:s'))
                    ->update();
            }
        }

        return [
            'status' => 'success',
            'message' => count($senders) . ' sender(s) added to allowed list.'
                . " ({$smsUpdated} SMS classifications, {$profilesUpdated} sender profiles updated).",
        ];
    }

    private function applyBlock(int $userId, array $senders): array
    {
        $db = \Config\Database::connect();
        $tokens = $this->uploadsModel->getOwnedRawTokens($userId);

        foreach ($senders as $sender) {
            $db->table('tbl_Blocked_Senders')->upsert([
                'user_id' => $userId,
                'sender' => $sender,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $smsUpdated = 0;
        $profilesUpdated = 0;

        if (!empty($tokens)) {
            if ($db->tableExists('tbl_Sms')) {
                $smsUpdated = $db->table('tbl_Sms')
                    ->whereIn('sms_number', $senders)
                    ->whereIn('sms_owner', $tokens)
                    ->set('sms_is_finance', 0)
                    ->update();
            }

            if ($db->tableExists('tbl_Sender_Profiles')) {
                $profilesUpdated = $db->table('tbl_Sender_Profiles')
                    ->whereIn('sp_number', $senders)
                    ->whereIn('sp_owner', $tokens)
                    ->set('sp_is_finance', 0)
                    ->set('sp_updated', date('Y-m-d H:i:s'))
                    ->update();
            }
        }

        return [
            'status' => 'success',
            'message' => count($senders) . ' sender(s) blocked. Their SMS is now excluded from finance intelligence'
                . " ({$smsUpdated} SMS classifications, {$profilesUpdated} sender profiles updated).",
        ];
    }

    private function applyUnblock(int $userId, array $senders): array
    {
        $db = \Config\Database::connect();

        $deleted = $db->table('tbl_Blocked_Senders')
            ->where('user_id', $userId)
            ->whereIn('sender', $senders)
            ->delete();

        return [
            'status' => 'success',
            'message' => count($senders) . ' sender(s) unblocked. Run a rescan to reclassify their SMS.',
        ];
    }

    /**
     * Senders currently on this user's blocklist, keyed by sender.
     */
    private function getBlockedMap(int $userId): array
    {
        $db = \Config\Database::connect();
        $rows = $db->table('tbl_Blocked_Senders')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[strtoupper(trim($row['sender']))] = $row['created_at'];
        }
        return $map;
    }

    /**
     * Global allowed-sender set (the ML allowlist), keyed by sender.
     *
     * Combines:
     *  1. The DB allowlist (tbl_Allowed_Senders) — takes precedence.
     *  2. The ML backend's hardcoded default finance senders (fallback list).
     * This way "preselected good senders" from the ML code show up as allowed
     * even if the user has never uploaded SMS from them.
     */
    private function getGlobalAllowedMap(): array
    {
        $db = \Config\Database::connect();
        $rows = $db->table('tbl_Allowed_Senders')
            ->select('sender, category')
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[strtoupper(trim($row['sender']))] = $row['category'] ?? '';
        }

        // Merge the hardcoded defaults (they never override an explicit DB entry).
        foreach ($this->getHardcodedDefaults() as $sender => $category) {
            $senderUpper = strtoupper(trim($sender));
            if (!isset($map[$senderUpper])) {
                $map[$senderUpper] = $category;
            }
        }

        return $map;
    }

    /**
     * The ML backend's hardcoded default finance senders, keyed by sender.
     * Returns [] if the backend is unreachable.
     */
    private function getHardcodedDefaults(): array
    {
        try {
            $base = rtrim((string) config('MlBackend')->baseUrl, '/');
            $resp = \Config\Services::curlrequest()->get($base . '/admin/allowed/defaults', ['timeout' => 5]);
            $body = json_decode($resp->getBody(), true);
            $defaults = $body['defaults'] ?? [];
            $map = [];
            foreach ($defaults as $d) {
                $map[$d['sender']] = $d['category'] ?? '';
            }
            return $map;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Every unique sender belonging to this user, categorised with the flags
     * needed to split them into blocked / allowed / unknown lists.
     */
    private function getCategorizedSenders(int $userId): array
    {
        $tokens = $this->uploadsModel->getOwnedRawTokens($userId);
        if (empty($tokens)) {
            return [];
        }

        $db = \Config\Database::connect();
        $blockedMap = $this->getBlockedMap($userId);
        $globalAllowed = $this->getGlobalAllowedMap();

        $bySender = [];
        if ($db->tableExists('tbl_Sender_Profiles')) {
            $profiles = $db->table('tbl_Sender_Profiles')
                ->select('sp_number, sp_name, sp_category, sp_is_finance')
                ->whereIn('sp_owner', $tokens)
                ->get()
                ->getResultArray();

            foreach ($profiles as $p) {
                $sender = $p['sp_number'];
                if (!isset($bySender[$sender])) {
                    $bySender[$sender] = [
                        'sender' => $sender,
                        'name' => $p['sp_name'] ?? '',
                        'category' => $p['sp_category'] ?? '',
                        'is_finance' => (int) $p['sp_is_finance'],
                    ];
                }
            }
        }

        if ($db->tableExists('tbl_Sms_Classification') && $db->tableExists('tbl_Sms')) {
            $classification = $db->table('tbl_Sms_Classification c')
                ->select('c.sender, MAX(c.is_finance) AS is_finance')
                ->join('tbl_Sms s', 's.id = c.sms_id')
                ->whereIn('s.sms_owner', $tokens)
                ->groupBy('c.sender')
                ->get()
                ->getResultArray();

            foreach ($classification as $c) {
                $sender = $c['sender'];
                if (!isset($bySender[$sender])) {
                    $bySender[$sender] = [
                        'sender' => $sender,
                        'name' => '',
                        'category' => '',
                        'is_finance' => (int) $c['is_finance'],
                    ];
                }
            }
        }

        $senders = [];
        foreach ($bySender as $s) {
            $senderUpper = strtoupper(trim($s['sender']));
            $allowed = (bool) $s['is_finance'] || isset($globalAllowed[$senderUpper]);
            $senders[] = [
                'sender' => $s['sender'],
                'name' => $s['name'],
                'category' => $s['category'] ?: ($globalAllowed[$senderUpper] ?? ''),
                'is_finance' => (bool) $s['is_finance'],
                'allowed' => $allowed,
                'blocked' => isset($blockedMap[$senderUpper]),
                'blocked_at' => $blockedMap[$senderUpper] ?? null,
            ];
        }

        // Add hardcoded default finance senders that the user has no data for,
        // so the Allowed tab lists every "preselected good sender".
        foreach ($globalAllowed as $senderUpper => $category) {
            $found = false;
            foreach ($bySender as $origSender => $info) {
                if (strcasecmp($origSender, $senderUpper) === 0) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $senders[] = [
                    'sender' => $senderUpper,
                    'name' => '',
                    'category' => $category ?? '',
                    'is_finance' => true,
                    'allowed' => true,
                    'blocked' => isset($blockedMap[$senderUpper]),
                    'blocked_at' => $blockedMap[$senderUpper] ?? null,
                ];
            }
        }

        usort($senders, static fn ($a, $b) => strcasecmp($a['sender'], $b['sender']));

        return $senders;
    }
}
