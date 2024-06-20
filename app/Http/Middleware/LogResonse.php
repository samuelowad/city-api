<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Process the request and get the response
        $response = $next($request);

        // Log the response status code and content
        Log::info('Response Status: ' . $response->status());
        Log::info('Response Content: ' . $response->getContent());

        return $response;
    }
}
