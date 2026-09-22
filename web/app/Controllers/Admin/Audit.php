<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\Audit as AuditLib;
use CodeIgniter\API\ResponseTrait;

class Audit extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $page = max(1, (int)$this->request->getGet('page'));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $category = $this->request->getGet('category') ?? '';
        $userId = $this->request->getGet('user_id') ? (int)$this->request->getGet('user_id') : null;
        $action = $this->request->getGet('action') ?? '';
        $dateFrom = $this->request->getGet('date_from') ?? '';
        $dateTo = $this->request->getGet('date_to') ?? '';

        $entries = AuditLib::getEntries($perPage, $offset, $category ?: null, $userId, $action ?: null, $dateFrom ?: null, $dateTo ?: null);
        $total = AuditLib::countEntries($category ?: null, $userId, $action ?: null, $dateFrom ?: null, $dateTo ?: null);
        $categories = AuditLib::getCategories();
        $actions = AuditLib::getActions($category ?: null);

        // Get users for filter dropdown
        $db = \Config\Database::connect();
        $users = $db->table('users')
            ->select('id, username')
            ->orderBy('username')
            ->get()
            ->getResultArray();

        return view('Admin/Audit/index', [
            'bg_color' => '#B1B8ED',
            'entries' => $entries,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
            'categories' => $categories,
            'actions' => $actions,
            'users' => $users,
            'filters' => [
                'category' => $category,
                'user_id' => $userId,
                'action' => $action,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    public function export()
    {
        $category = $this->request->getGet('category') ?? '';
        $userId = $this->request->getGet('user_id') ? (int)$this->request->getGet('user_id') : null;
        $action = $this->request->getGet('action') ?? '';
        $dateFrom = $this->request->getGet('date_from') ?? '';
        $dateTo = $this->request->getGet('date_to') ?? '';

        $entries = AuditLib::getEntries(10000, 0, $category ?: null, $userId, $action ?: null, $dateFrom ?: null, $dateTo ?: null);

        $csv = "ID,Date,User,IP,Category,Action,Description,Metadata\n";
        foreach ($entries as $e) {
            $csv .= implode(',', array_map(fn($v) => '"' . str_replace('"', '""', (string)$v) . '"', [
                $e['id'],
                $e['created_at'],
                $e['username'] ?? '—',
                $e['ip'] ?? '—',
                $e['action_category'],
                $e['action'],
                $e['description'] ?? '',
                $e['metadata'] ? json_encode(json_decode($e['metadata'], true)) : '',
            ])) . "\n";
        }

        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="audit_log_' . date('Y-m-d') . '.csv"')
            ->setBody($csv);
    }
}