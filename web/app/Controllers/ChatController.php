<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class ChatController extends BaseController
{
    /**
     * Display the AI Financial Assistant chat page.
     */
    public function index()
    {
        $userId = auth()->user()->id;
        $username = auth()->user()->username ?? 'User';

        $db = \Config\Database::connect();
        $chatHistory = [];
        try {
            if ($db->tableExists('tbl_Chat_Messages')) {
                $chatHistory = $db->table('tbl_Chat_Messages')
                    ->where('user_id', (string) $userId)
                    ->orderBy('id', 'ASC')
                    ->limit(50)
                    ->get()
                    ->getResultArray();
            }
        } catch (\Throwable $e) {
            log_message('error', 'ChatController index history error: ' . $e->getMessage());
        }

        $data = [
            'title'        => 'AI Financial Assistant',
            'user_id'      => $userId,
            'username'     => $username,
            'current_url'  => 'dashboard/chat',
            'chat_history' => $chatHistory,
        ];

        return view('Chat/index', $data);
    }

    /**
     * AJAX endpoint to query active model info from ML microservice.
     */
    public function info()
    {
        $mlBase = rtrim((string) config('MlBackend')->baseUrl, '/');
        $fastApiUrl = $mlBase . '/api/v1/chat/info';

        $ch = curl_init($fastApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr || $httpCode !== 200) {
            return $this->response->setJSON([
                'status'   => 'offline',
                'model'    => 'Unknown',
                'provider' => 'Unavailable',
                'error'    => $curlErr ?: 'HTTP ' . $httpCode,
            ]);
        }

        $decoded = json_decode($resp, true);
        return $this->response->setJSON($decoded ?: [
            'status'   => 'online',
            'model'    => 'Active Model',
            'provider' => 'LLM',
        ]);
    }

    /**
     * AJAX endpoint to send a user prompt and conversation history to the ML assistant.
     */
    public function send()
    {
        if (!auth()->loggedIn() || !auth()->user()) {
            return $this->response->setStatusCode(401)->setJSON([
                'error' => 'Your session has expired. Please refresh the page and sign in again.',
            ]);
        }
        $userId = auth()->user()->id;

        $json = $this->request->getJSON(true);
        $message = trim($json['message'] ?? $this->request->getPost('message') ?? '');
        $history = $json['history'] ?? [];

        if (empty($message)) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'Message cannot be empty.',
            ]);
        }

        $userAgent = (string) $this->request->getUserAgent();
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        // Persist user prompt with 'webapp' platform
        try {
            if ($db->tableExists('tbl_Chat_Messages')) {
                $db->table('tbl_Chat_Messages')->insert([
                    'user_id'     => (string) $userId,
                    'role'        => 'user',
                    'message'     => $message,
                    'platform'    => 'webapp',
                    'device_info' => mb_substr($userAgent, 0, 255),
                    'app_version' => '3.5.0',
                    'model'       => null,
                    'provider'    => null,
                    'tokens_used' => null,
                    'latency_ms'  => null,
                    'created_at'  => $now,
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'ChatController error saving user message: ' . $e->getMessage());
        }

        $mlBase = rtrim((string) config('MlBackend')->baseUrl, '/');
        $fastApiUrl = $mlBase . '/api/v1/chat';

        $payload = json_encode([
            'user_id' => (string) $userId,
            'message' => $message,
            'history' => is_array($history) ? $history : [],
        ]);

        $ch = curl_init($fastApiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);

        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            log_message('error', 'ChatController error: ' . $curlErr);
            return $this->response->setStatusCode(502)->setJSON([
                'error' => 'Could not connect to AI microservice: ' . $curlErr,
            ]);
        }

        if ($httpCode >= 400) {
            log_message('error', 'ChatController HTTP ' . $httpCode . ': ' . $resp);
            $errData = json_decode($resp, true);
            $msg = $errData['detail'] ?? ('AI service returned HTTP ' . $httpCode);
            return $this->response->setStatusCode($httpCode)->setJSON([
                'error' => $msg,
            ]);
        }

        $decoded = json_decode($resp, true);
        if (!$decoded) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Invalid response from AI microservice.',
            ]);
        }

        // Persist assistant reply with 'webapp' platform
        try {
            if ($db->tableExists('tbl_Chat_Messages')) {
                $db->table('tbl_Chat_Messages')->insert([
                    'user_id'     => (string) $userId,
                    'role'        => 'assistant',
                    'message'     => $decoded['reply'] ?? '',
                    'platform'    => 'webapp',
                    'device_info' => mb_substr($userAgent, 0, 255),
                    'app_version' => '3.5.0',
                    'model'       => $decoded['model'] ?? null,
                    'provider'    => $decoded['provider'] ?? null,
                    'tokens_used' => $decoded['tokens_used'] ?? null,
                    'latency_ms'  => $decoded['latency_ms'] ?? null,
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'ChatController error saving assistant reply: ' . $e->getMessage());
        }

        return $this->response->setJSON($decoded);
    }

    /**
     * Clear WebApp user chat history
     */
    public function clear()
    {
        if (!auth()->loggedIn() || !auth()->user()) {
            return $this->response->setStatusCode(401)->setJSON([
                'error' => 'Your session has expired. Please refresh the page and sign in again.',
            ]);
        }
        $userId = auth()->user()->id;
        $db = \Config\Database::connect();
        try {
            if ($db->tableExists('tbl_Chat_Messages')) {
                $db->table('tbl_Chat_Messages')->where('user_id', (string) $userId)->delete();
            }
            return $this->response->setJSON(['status' => 'ok', 'message' => 'Chat history cleared.']);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    /**
     * Mobile API endpoint: POST /api/v1/chat
     * Accepts { user_id, message, history } directly from mobile client.
     */
    public function apiChat()
    {
        $json = $this->request->getJSON(true);
        if (!$json) {
            $raw = $this->request->getBody();
            $json = json_decode($raw, true) ?? [];
        }

        $userId = trim((string)($json['user_id'] ?? $this->request->getHeaderLine('X-User-Id')));
        if (empty($userId)) {
            $userId = auth()->loggedIn() ? (string)auth()->user()->id : 'mobile_user';
        }

        $message = trim((string)($json['message'] ?? ''));
        $history = $json['history'] ?? [];

        if (empty($message)) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'message is required and cannot be empty.',
            ]);
        }

        $ua = (string) $this->request->getHeaderLine('User-Agent');
        $appVersion = (string) ($this->request->getHeaderLine('X-App-Version') ?: '3.5.0');
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        // Persist mobile user message with 'mobile' platform
        try {
            if ($db->tableExists('tbl_Chat_Messages')) {
                $db->table('tbl_Chat_Messages')->insert([
                    'user_id'     => $userId,
                    'role'        => 'user',
                    'message'     => $message,
                    'platform'    => 'mobile',
                    'device_info' => mb_substr($ua, 0, 255),
                    'app_version' => $appVersion,
                    'model'       => null,
                    'provider'    => null,
                    'tokens_used' => null,
                    'latency_ms'  => null,
                    'created_at'  => $now,
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'apiChat error saving user message: ' . $e->getMessage());
        }

        $mlBase = rtrim((string) config('MlBackend')->baseUrl, '/');
        $fastApiUrl = $mlBase . '/api/v1/chat';

        $payload = json_encode([
            'user_id' => $userId,
            'message' => $message,
            'history' => is_array($history) ? $history : [],
        ]);

        $ch = curl_init($fastApiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);

        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            log_message('error', 'apiChat error: ' . $curlErr);
            return $this->response->setStatusCode(502)->setJSON([
                'error' => 'Could not connect to AI microservice: ' . $curlErr,
            ]);
        }

        if ($httpCode >= 400) {
            log_message('error', 'apiChat HTTP ' . $httpCode . ': ' . $resp);
            $errData = json_decode($resp, true);
            $msg = $errData['detail'] ?? ('AI service returned HTTP ' . $httpCode);
            return $this->response->setStatusCode($httpCode)->setJSON([
                'error' => $msg,
            ]);
        }

        $decoded = json_decode($resp, true);
        if (!$decoded) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Invalid response from AI microservice.',
            ]);
        }

        // Persist mobile assistant reply with 'mobile' platform
        try {
            if ($db->tableExists('tbl_Chat_Messages')) {
                $db->table('tbl_Chat_Messages')->insert([
                    'user_id'     => $userId,
                    'role'        => 'assistant',
                    'message'     => $decoded['reply'] ?? '',
                    'platform'    => 'mobile',
                    'device_info' => mb_substr($ua, 0, 255),
                    'app_version' => $appVersion,
                    'model'       => $decoded['model'] ?? null,
                    'provider'    => $decoded['provider'] ?? null,
                    'tokens_used' => $decoded['tokens_used'] ?? null,
                    'latency_ms'  => $decoded['latency_ms'] ?? null,
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'apiChat error saving assistant reply: ' . $e->getMessage());
        }

        return $this->response->setJSON($decoded);
    }

    /**
     * Mobile API endpoint: GET /api/v1/chat/history
     */
    public function history()
    {
        $userId = trim((string)($this->request->getGet('user_id') ?? $this->request->getHeaderLine('X-User-Id')));
        if (empty($userId)) {
            $userId = auth()->loggedIn() ? (string)auth()->user()->id : '';
        }
        if (empty($userId)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'user_id is required']);
        }

        $db = \Config\Database::connect();
        $messages = [];
        try {
            if ($db->tableExists('tbl_Chat_Messages')) {
                $messages = $db->table('tbl_Chat_Messages')
                    ->where('user_id', $userId)
                    ->orderBy('id', 'ASC')
                    ->limit(50)
                    ->get()
                    ->getResultArray();
            }
        } catch (\Throwable $e) {
            log_message('error', 'ChatController history error: ' . $e->getMessage());
        }

        return $this->response->setJSON(['status' => 'ok', 'history' => $messages]);
    }

    /**
     * Mobile API endpoint: DELETE /api/v1/chat/history
     */
    public function clearHistory()
    {
        $userId = trim((string)($this->request->getGet('user_id') ?? $this->request->getHeaderLine('X-User-Id')));
        if (empty($userId)) {
            $userId = auth()->loggedIn() ? (string)auth()->user()->id : '';
        }
        if (empty($userId)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'user_id is required']);
        }

        $db = \Config\Database::connect();
        try {
            if ($db->tableExists('tbl_Chat_Messages')) {
                $db->table('tbl_Chat_Messages')->where('user_id', $userId)->delete();
            }
            return $this->response->setJSON(['status' => 'ok', 'message' => 'Chat history cleared.']);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    /**
     * Mobile API endpoint: GET /api/v1/chat/info
     */
    public function apiInfo()
    {
        return $this->info();
    }
}

