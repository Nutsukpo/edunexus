<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Display the currently authenticated user's profile.
     */
    public function show()
    {
        $user = Auth::guard('web')->user();

        abort_unless($user, 403);

        return view('profile.show', compact('user'));
    }
}
