<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\SessionHelper;

class SetGuardSession
{
    public function handle(Request $request, Closure $next, $guard = null)
    {
        // Set session configuration based on guard
        SessionHelper::setGuardSessionConfig($guard);
        
        return $next($request);
    }
}