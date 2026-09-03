<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * URIs that should be excluded from CSRF verification.
     *
     * Paystack cannot send a Laravel CSRF token, so the webhook
     * is protected using Paystack's x-paystack-signature instead.
     */
    protected $except = [
        'paystack/webhook',
    ];
}