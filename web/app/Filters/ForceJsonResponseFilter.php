<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ForceJsonResponseFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Force Accept header to application/json so CodeIgniter exception handler outputs JSON errors
        $request->setHeader('Accept', 'application/json');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Ensure the response Content-Type is always application/json
        $response->setHeader('Content-Type', 'application/json');
    }
}
