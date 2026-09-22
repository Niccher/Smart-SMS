<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class UserAreaFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login')->with('error', 'Please log in to access this page.');
        }

        $user = auth()->user();
        if ($user && ($user->inGroup('superadmin') || $user->can('admin.access'))) {
            if ($request->isAJAX() || str_contains((string)$request->getHeaderLine('Accept'), 'application/json')) {
                return service('response')->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Admin accounts cannot access user console endpoints. Please log in with a regular user account.',
                ]);
            }

            $view = view('Errors/forbidden_admin', [
                'message' => 'Admin accounts cannot access the user console. Please log in with a regular user account to view this content.',
            ]);

            return service('response')->setStatusCode(403)->setBody($view);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
