<?php

namespace App\Controllers;

use App\Models\BudgetModel;

class Budget extends BaseController
{
    public function index()
    {
        $userId  = auth()->user()->id;
        $mod     = new BudgetModel();
        $budgets = $mod->getBudgets($userId);
        $progress = $mod->getBudgetProgress($budgets);

        $categories = [
            'Total Outflow', 'Received', 'Paybill', 'Till',
            'Sent to Mobile', 'Withdrawal', 'Fuliza'
        ];

        $data = [
            'budgets'    => $progress,
            'categories' => $categories,
            'bg_color'   => '#B1B8ED',
        ];

        return view('Budget/index', $data);
    }

    public function save()
    {
        $userId = auth()->user()->id;
        $mod    = new BudgetModel();
        $post   = $this->request->getPost();

        if (empty($post['category']) || empty($post['amount_limit'])) {
            return redirect()->to('/dashboard/budget')->with('error', 'Category and limit are required.');
        }

        $data = [
            'id'           => $post['id'] ?? null,
            'user_id'      => $userId,
            'category'     => $post['category'],
            'label'        => $post['label'] ?? $post['category'],
            'amount_limit' => (float)$post['amount_limit'],
            'period'       => $post['period'] ?? 'monthly',
            'rollover'     => !empty($post['rollover']) ? 1 : 0,
        ];

        $mod->saveBudget($data);
        return redirect()->to('/dashboard/budget')->with('success', 'Budget saved successfully.');
    }

    public function delete()
    {
        $id = (int)$this->request->getPost('id');
        if ($id > 0) {
            $mod = new BudgetModel();
            $mod->deleteBudget($id);
        }
        return redirect()->to('/dashboard/budget')->with('success', 'Budget removed.');
    }
}
