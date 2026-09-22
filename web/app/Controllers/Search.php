<?php

namespace App\Controllers;

use App\Models\UploadModel;

class Search extends BaseController
{
    protected int $perPage = 25;

    public function index()
    {
        helper(['mpesa_date', 'form']);
        $db = \Config\Database::connect();

        $page    = (int)($this->request->getGet('page') ?? 1);
        $page    = max(1, $page);
        $offset  = ($page - 1) * $this->perPage;

        $filters = [
            'date_from'  => $this->request->getGet('date_from'),
            'date_to'    => $this->request->getGet('date_to'),
            'category'   => $this->request->getGet('category'),
            'search'     => $this->request->getGet('search'),
            'sender'     => $this->request->getGet('sender'),
            'min_amount' => $this->request->getGet('min_amount'),
            'max_amount' => $this->request->getGet('max_amount'),
        ];

        // Fetch unique finance senders for the dropdown
        $senders = $db->table('tbl_Sms_Classification')
            ->select('sender')
            ->distinct()
            ->where('is_finance', 1)
            ->where('sender IS NOT NULL')
            ->where("sender != ''")
            ->orderBy('sender', 'ASC')
            ->get()
            ->getResultArray();
        $senders = array_unique(array_column($senders, 'sender'));

        // Count total
        $mod_uploads = new UploadModel();
        $total = $mod_uploads->countFilteredTransactions($filters);

        // Fetch just this page
        $transactions = $mod_uploads->getFilteredTransactions($filters, $this->perPage, $offset);

        $totalPages = (int)ceil($total / $this->perPage);

        $data = [
            'transactions' => $transactions,
            'filters'      => $filters,
            'senders'      => $senders,
            'total'        => $total,
            'page'         => $page,
            'perPage'      => $this->perPage,
            'totalPages'   => $totalPages,
            'bg_color'     => '#B1B8ED'
        ];

        return view('Search/index', $data);
    }
}
