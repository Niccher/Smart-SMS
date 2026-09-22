<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\UserSettingModel;
use App\Models\NoteModel;
use App\Models\UploadModel;

class Api extends BaseController
{
    use ResponseTrait;

    private function getUserFromToken(): ?\CodeIgniter\Shield\Entities\User
    {
        $tokenStr = $this->request->getPost('varToken') ?? $this->request->getHeaderLine('X-API-Token');
        if (empty($tokenStr)) return null;

        $hashed = hash('sha256', $tokenStr);
        $db = \Config\Database::connect();
        $identity = $db->table('auth_identities')
            ->where('type', \CodeIgniter\Shield\Authentication\Authenticators\AccessTokens::ID_TYPE_ACCESS_TOKEN)
            ->where('secret', $hashed)
            ->get()
            ->getRow();

        if (!$identity) return null;

        \App\Libraries\Audit::log(
            'api_call',
            'api',
            'API call: ' . $this->request->getUri()->getPath(),
            [
                'token_hash'  => $hashed,
                'endpoint'    => $this->request->getUri()->getPath(),
                'method'      => $this->request->getMethod(),
                'app_version' => $this->request->getHeaderLine('X-App-Version') ?: null,
                'device_time' => $this->request->getHeaderLine('X-Device-Time') ?: null,
            ],
            (int) $identity->user_id
        );

        return model(\CodeIgniter\Shield\Models\UserModel::class)->findById($identity->user_id);
    }

    public function profile()
    {
        $user = $this->getUserFromToken();
        if (!$user) return $this->failUnauthorized('Invalid token');

        return $this->respond([
            'status' => '1',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'created_at' => $user->created_at,
            ]
        ]);
    }

    public function updateProfile()
    {
        $user = $this->getUserFromToken();
        if (!$user) return $this->failUnauthorized('Invalid token');

        $username = $this->request->getPost('username');
        $email = $this->request->getPost('email');

        $data = [];
        if (!empty($username) && $username !== $user->username) $data['username'] = $username;
        if (!empty($email) && $email !== $user->email) $data['email'] = $email;

        if (!empty($data)) {
            $user->fill($data);
            $user->save();
        }

        return $this->respond(['status' => '1', 'message' => 'Profile updated']);
    }

    public function preferences()
    {
        $user = $this->getUserFromToken();
        if (!$user) return $this->failUnauthorized('Invalid token');

        $settings = (new UserSettingModel())->getSettings($user->id);

        return $this->respond([
            'status' => '1',
            'settings' => $settings,
        ]);
    }

    public function savePreferences()
    {
        $user = $this->getUserFromToken();
        if (!$user) return $this->failUnauthorized('Invalid token');

        $settingsModel = new UserSettingModel();
        $existing = $settingsModel->getSettings($user->id);

        $fields = ['currency', 'date_format', 'time_format', 'default_budget_period',
                    'notify_email_alerts', 'notify_budget_alerts', 'notify_low_balance',
                    'notify_unusual_activity', 'export_default_format'];

        $data = [];
        foreach ($fields as $f) {
            $val = $this->request->getPost($f);
            if ($val !== null) $data[$f] = $val;
        }

        if (!empty($data)) {
            $data['dashboard_widgets'] = $existing['dashboard_widgets'];
            $settingsModel->saveSettings($user->id, $data);
        }

        return $this->respond(['status' => '1', 'message' => 'Preferences saved']);
    }

    public function noteGet(int $smsId)
    {
        $user = $this->getUserFromToken();
        if (!$user) return $this->failUnauthorized('Invalid token');

        $note = (new NoteModel())->getForSms($smsId);
        return $this->respond([
            'status' => '1',
            'note' => $note['note'] ?? '',
        ]);
    }

    public function noteSave()
    {
        $user = $this->getUserFromToken();
        if (!$user) return $this->failUnauthorized('Invalid token');

        $smsId = (int)$this->request->getPost('sms_id');
        $note = $this->request->getPost('note');

        if ($smsId > 0) {
            (new NoteModel())->saveNote($user->id, $smsId, $note ?? '');
        }

        return $this->respond(['status' => '1', 'message' => 'Note saved']);
    }

    public function scanTrigger()
    {
        $user = $this->getUserFromToken();
        if (!$user) return $this->failUnauthorized('Invalid token');

        $mlBase = (string) config('MlBackend')->baseUrl;
        $fastApiUrl = $mlBase . '/process/for-user/' . $user->id;
        $client = \Config\Services::curlrequest();

        try {
            $resp = $client->post($fastApiUrl, ['timeout' => 5]);
            $body = json_decode($resp->getBody(), true);
            return $this->respond([
                'status' => 'started',
                'message' => 'Scan triggered',
                'processed' => $body['messages_processed'] ?? 0,
            ]);
        } catch (\Throwable $e) {
            return $this->respond(['status' => 'error', 'message' => 'Service unavailable'], 503);
        }
    }

    public function scanProgress()
    {
        $user = $this->getUserFromToken();
        if (!$user) return $this->failUnauthorized('Invalid token');

        $controller = new \App\Controllers\Home();
        $progress = $controller->progress();
        return $progress;
    }

    public function export(string $format = 'csv')
    {
        $user = $this->getUserFromToken();
        if (!$user) return $this->failUnauthorized('Invalid token');

        $modUploads = new UploadModel();
        $transactions = $modUploads->getAllTransactionsForUser($user->id);

        if ($format === 'json') {
            return $this->response
                ->setHeader('Content-Type', 'application/json')
                ->setHeader('Content-Disposition', 'attachment; filename="mpesa_export.json"')
                ->setBody(json_encode($transactions, JSON_PRETTY_PRINT));
        }

        $filename = 'mpesa_export.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Date', 'Type', 'Amount', 'Counterparty', 'Description', 'Category', 'Direction', 'Balance']);
        foreach ($transactions as $row) {
            fputcsv($output, [
                $row['trans_date'] ?? $row['sms_time'] ?? '',
                $row['sms_transaction_type'] ?? '',
                $row['amount'] ?? $row['sms_amount'] ?? '',
                $row['counterparty'] ?? $row['sms_counterparty'] ?? '',
                $row['description'] ?? '',
                $row['category'] ?? '',
                $row['direction'] ?? $row['sms_direction'] ?? '',
                $row['sms_balance'] ?? '',
            ]);
        }
        fclose($output);
        exit;
    }
}
