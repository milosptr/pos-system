<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyExternalApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key');
        $configuredKey = config('services.external_invoice.api_key');

        if (empty($configuredKey) || empty($apiKey) || $apiKey !== $configuredKey) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing API key',
            ], 401);
        }

        return $next($request);
    }
}
