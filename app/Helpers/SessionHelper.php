<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;

class SessionHelper
{
    /**
     * Set session configuration based on the current guard
     */
    public static function setGuardSessionConfig($guard = null)
    {
        // Determine the guard if not provided
        if (!$guard) {
            $guard = Request::route() ? Request::route()->getAction('guard') : 'web';
        }
        
        // Check if it's a student route
        $isStudent = ($guard === 'student' || Request::is('student/*'));
        
        if ($isStudent) {
            // Student session configuration
            Config::set('session.cookie', env('STUDENT_SESSION_COOKIE', 'student_session'));
            Config::set('session.lifetime', (int) env('STUDENT_SESSION_LIFETIME', 120));
        } else {
            // Admin/Web session configuration
            Config::set('session.cookie', env('SESSION_COOKIE', 'admin_session'));
            Config::set('session.lifetime', (int) env('SESSION_LIFETIME', 120));
        }
    }
}