<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePasswordController extends Controller
{
    public function edit()
    {
        return view('auth.change-password');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'current_password' => [
                'required',
                'current_password:web',
            ],
            'password' => [
                'required',
                'confirmed',
                'different:current_password',
                Password::min(8),
            ],
        ], [
            'current_password.current_password' =>
                'Your current password is incorrect.',
            'password.confirmed' =>
                'The new password confirmation does not match.',
            'password.different' =>
                'Your new password must be different from your current password.',
        ]);

        $user = Auth::guard('web')->user();

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()
            ->route('password.change.form')
            ->with('success', 'Your password has been changed successfully.');
    }
}
