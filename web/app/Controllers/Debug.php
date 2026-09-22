<?php

namespace App\Controllers;

class Debug extends BaseController
{
    public function error(int $code)
    {
        switch ($code) {
            case 400:
                return view('errors/html/error_400');
            case 401:
                return view('errors/html/error_401');
            case 403:
                return view('errors/html/error_403');
            case 404:
                return view('errors/html/error_404', ['message' => 'This is a debug preview of the 404 page.']);
            case 500:
                return view('errors/html/error_500');
            case 503:
                return view('errors/html/error_503');
            case 1: // Production generic
                return view('errors/html/production');
            default:
                return "Unknown error code: $code. Try 400, 401, 404, or 500.";
        }
    }
}
