<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\API\ResponseTrait;

class ChatController extends BaseApiController
{
    use ResponseTrait;

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
            $user = $this->getUserFromToken();
            $userId = $user ? (string)$user->id : 'mobile_user';
        }

        $message = trim((string)($json['message'] ?? ''));
        $history = $json['history'] ?? [];

        if (empty($message)) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'message is required and cannot be empty.',
            ]);
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
            log_message('error', 'Api/V1/ChatController error: ' . $curlErr);
            return $this->response->setStatusCode(502)->setJSON([
                'error' => 'Could not connect to AI microservice: ' . $curlErr,
            ]);
        }

        if ($httpCode >= 400) {
            log_message('error', 'Api/V1/ChatController HTTP ' . $httpCode . ': ' . $resp);
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

        return $this->response->setJSON($decoded);
    }

    /**
     * Alias for apiChat
     */
    public function chat()
    {
        return $this->apiChat();
    }

    /**
     * Mobile API endpoint: GET /api/v1/chat/info
     */
    public function apiInfo()
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
     * Alias for apiInfo
     */
    public function info()
    {
        return $this->apiInfo();
    }
}
