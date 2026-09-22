<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login')->with('error', 'Please log in to access the admin area.');
        }

        $user = auth()->user();
        if (!$user || (!$user->inGroup('superadmin') && !$user->can('admin.access'))) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to access the admin area.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}