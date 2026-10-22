<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogApiRequests
{
    public function handle(Request $request, Closure $next)
    {
        if (str_contains($request->path(), 'daily-register/data')) {
            Log::info('Daily Register Data Request:', [
                'user' => $request->user()?->email,
                'role' => $request->user()?->role,
                'source_sheet' => $request->source_sheet,
                'cabang_id' => $request->cabang_id,
                'kode_unit' => $request->kode_unit,
                'full_url' => $request->fullUrl()
            ]);
        }
        return $next($request);
    }
}
