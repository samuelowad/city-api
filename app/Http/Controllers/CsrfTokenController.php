<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CsrfTokenController extends Controller
{
    /**
     * Get the CSRF token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getToken()
    {
        return response()->json(['csrf_token' => csrf_token()]);
    }
}
