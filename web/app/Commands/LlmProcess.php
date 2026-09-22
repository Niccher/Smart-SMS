<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class LlmProcess extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'llm:process';
    protected $description = 'Process queued LLM analysis jobs by triggering the ML backend for each user.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        if (!$db->tableExists('tbl_Processing_Jobs')) {
            CLI::write('No processing jobs table found. Nothing to do.', 'yellow');

            return;
        }

        // ── 1. Heartbeat check: Auto-fail stuck jobs (no updates for 10 minutes) ──
        $tokenType = \CodeIgniter\Shield\Authentication\Authenticators\AccessTokens::ID_TYPE_ACCESS_TOKEN;
        $processingJobs = $db->table('tbl_Processing_Jobs')
            ->where('status', 'processing')
            ->get()
            ->getResultArray();

        foreach ($processingJobs as $pJob) {
            $pJobId = $pJob['id'];
            $pUserId = $pJob['user_id'];
            $startedAt = strtotime(($pJob['started_at'] ?? $pJob['created_at']) . ' UTC');
            $now = time();

            // Resolve user tokens to query latest processed_at timestamp
            $rawTokens = [];
            $tokenRows = $db->query("
                SELECT DISTINCT s.sms_owner AS tk FROM tbl_Sms s
                INNER JOIN auth_identities i ON i.secret = SHA2(s.sms_owner, 256)
                WHERE i.user_id = ? AND i.type = ?
                UNION
                SELECT DISTINCT l.loot_Owner AS tk FROM tbl_Loot l
                INNER JOIN auth_identities i ON i.secret = SHA2(l.loot_Owner, 256)
                WHERE i.user_id = ? AND i.type = ?
            ", [$pUserId, $tokenType, $pUserId, $tokenType])->getResult();

            foreach ($tokenRows as $r) {
                if (!empty($r->tk)) $rawTokens[] = $r->tk;
            }

            $latestActivity = $startedAt;
            if (!empty($rawTokens) && $db->tableExists('tbl_Sms_Processing')) {
                $latestProcessed = $db->table('tbl_Sms_Processing sp')
                    ->selectMax('processed_at')
                    ->join('tbl_Sms s', 's.id = sp.sms_id')
                    ->whereIn('s.sms_owner', $rawTokens)
                    ->get()
                    ->getRow();

                if ($latestProcessed && !empty($latestProcessed->processed_at)) {
                    $latestActivity = max($latestActivity, strtotime($latestProcessed->processed_at . ' UTC'));
                }
            }

            // 10 minutes timeout (600 seconds)
            if ($now - $latestActivity > 600) {
                $meta = json_decode($pJob['metadata'] ?? '', true) ?: [];
                $meta['error'] = 'Job timed out due to inactivity (no progress updates for 10 minutes).';

                $db->table('tbl_Processing_Jobs')
                    ->where('id', $pJobId)
                    ->update([
                        'status' => 'failed',
                        'errors' => 1,
                        'completed_at' => gmdate('Y-m-d H:i:s'),
                        'metadata' => json_encode($meta),
                    ]);

                CLI::write("Job #{$pJobId} (user {$pUserId}) marked failed due to inactivity timeout.", 'red');
            }
        }

        // ── 2. Run queued jobs ──
        $queued = $db->table('tbl_Processing_Jobs')
            ->where('status', 'queued')
            ->get()
            ->getResultArray();

        if (empty($queued)) {
            CLI::write('No queued processing jobs.', 'green');

            return;
        }

        $client = \Config\Services::curlrequest();
        $triggered = 0;
        $failed = 0;

        foreach ($queued as $job) {
            $userId = $job['user_id'];
            $jobId = $job['id'];
            $startTime = microtime(true);

            $db->table('tbl_Processing_Jobs')
                ->where('id', $jobId)
                ->update(['status' => 'processing', 'started_at' => gmdate('Y-m-d H:i:s')]);

            try {
                $mlBase = (string) config('MlBackend')->baseUrl;
                $resp = $client->post(
                    $mlBase . '/process/for-user/' . $userId . '?job_id=' . $jobId,
                    ['timeout' => 10]
                );
                $body = json_decode($resp->getBody(), true);

                $status = $body['status'] ?? 'starting';

                if ($status === 'starting' || $status === 'already_running') {
                    CLI::write("Job #{$jobId} (user {$userId}) successfully triggered in background.", 'green');
                    $triggered++;
                    continue;
                }

                $messagesProcessed = (int)($body['messages_processed'] ?? 0);
                $duration = (int)($body['duration_seconds'] ?? round(microtime(true) - $startTime));
                $errors = (int)($body['errors'] ?? 0);
                $status = $body['status'] === 'skipped' ? 'queued' : 'completed';

                // Merge metadata from backend if present
                $meta = json_decode($job['metadata'] ?? '', true) ?: [];
                if (!empty($body['metadata'])) {
                    $meta = array_merge($meta, (array)$body['metadata']);
                }

                $db->table('tbl_Processing_Jobs')
                    ->where('id', $jobId)
                    ->update([
                        'status' => $status,
                        'completed_at' => gmdate('Y-m-d H:i:s'),
                        'duration_seconds' => $duration,
                        'messages_processed' => $messagesProcessed,
                        'errors' => $errors,
                        'metadata' => json_encode($meta),
                    ]);

                if ($body['status'] === 'skipped') {
                    CLI::write('Job #' . $jobId . ' (user ' . $userId . '): scan already in progress, re-queued.', 'yellow');
                } else {
                    CLI::write('Job #' . $jobId . ' (user ' . $userId . '): processed ' . $messagesProcessed . ' messages in ' . $duration . 's.', 'green');
                    $triggered++;

                    if (\App\Libraries\Notifier::isTriggerEnabled('ml_complete')) {
                        $user = \auth()->getProvider()->findById($userId);
                        $userEmail = $user ? $user->email : '';
                        if (!empty($userEmail)) {
                            \App\Libraries\Notifier::sendTrigger($userEmail, 'ml_complete', [
                                'messagesProcessed' => $messagesProcessed,
                                'newTransactions'   => (int)($meta['sms_finance'] ?? 0),
                                'categoriesFound'   => count((array)($meta['category_counts'] ?? [])),
                                'duration'          => $duration . ' seconds',
                            ]);
                        }
                    }
                }
            } catch (\Throwable $e) {
                $duration = round(microtime(true) - $startTime);

                $meta = json_decode($job['metadata'] ?? '', true) ?: [];
                $meta['error'] = $e->getMessage();
                $meta['exception_class'] = get_class($e);
                $meta['stack_trace'] = substr($e->getTraceAsString(), 0, 2000);

                $db->table('tbl_Processing_Jobs')
                    ->where('id', $jobId)
                    ->update([
                        'status' => 'failed',
                        'errors' => 1,
                        'duration_seconds' => $duration,
                        'completed_at' => gmdate('Y-m-d H:i:s'),
                        'metadata' => json_encode($meta),
                    ]);
                CLI::write('Job #' . $jobId . ' (user ' . $userId . '): ' . $e->getMessage(), 'red');
                $failed++;
            }
        }

        CLI::write('Done. Triggered: ' . $triggered . ', failed: ' . $failed . '.', 'green');
    }
}
