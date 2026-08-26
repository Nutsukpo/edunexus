<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    /**
     * Show Login Form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showAdminLoginForm()
    {
        return view('auth.adminLogin');
    }

    /**
     * Handle Login
     */
    public function login(Request $request)
    {   
        $request->validate([
            'role'        => 'required|in:admin,student',
            'email' => 'email',
            'login_field'  => 'string',
            'password'     => 'required|string',
        ]);

        $role = $request->role;

        /*
        |--------------------------------------------------------------------------
        | ADMIN LOGIN
        |--------------------------------------------------------------------------
        */
        if ($role == 'admin') {
            $credentials = $request->only('email', 'password');

            if (Auth::attempt($credentials)) {

                $request->session()->regenerate();

                return redirect()->route('dashboard');
            }

            return back()->with('error', 'Invalid credentials');
        }

        /*
        |--------------------------------------------------------------------------
        | STUDENT LOGIN
        |--------------------------------------------------------------------------
        */
        $student = Student::where('student_id', $request->login_field)->first();

        if (!$student) {
            return back()
                ->withInput($request->except('password'))
                ->withErrors([
                    'errors' => 'Student ID not found. Please check your Student ID.'
                ]);
        }

        if (!$student->is_active) {
            return back()
                ->withInput($request->except('password'))
                ->withErrors([
                    'errors' => 'Your account is inactive. Please contact the school administrator.'
                ]);
        }

        if (isset($student->portal_access) && !$student->portal_access) {
            return back()
                ->withInput($request->except('password'))
                ->withErrors([
                    'errors' => 'Portal access is not enabled for this student. Please contact the school.'
                ]);
        }

        if (empty($student->password)) {
            return back()
                ->withInput($request->except('password'))
                ->withErrors([
                    'errors' => 'No password set. Please contact the school to set your password.'
                ]);
        }

        if (!Hash::check($request->password, $student->password)) {
            return back()
                ->withInput($request->except('password'))
                ->withErrors([
                    'errors' => 'Incorrect password. Please try again.'
                ]);
        }

        try {
            Auth::guard('student')->login($student, $request->has('remember'));
            Log::info('Student logging in');
            $request->session()->regenerate();
        } catch (\Exception $e) {
            Log::error('Student login error: ' . $e->getMessage());
            $request->session()->put('student_id', $student->id);
            $request->session()->put('student_login', true);
        }

        $student->update([
            'last_login_at' => now()
        ]);

        $hasPasswordChangedColumn = Schema::hasColumn('students', 'password_changed');

        if ($hasPasswordChangedColumn && !$student->password_changed) {
            return redirect()->route('student.password.change.form');
        }

        // FIXED: Redirect to student dashboard
        return redirect()->route('students.dashboard');
    }

    /**
     * Handle Logout
     */
    public function logout(Request $request)
    {
        // Check which guard is currently logged in
        $isAdmin = Auth::guard('web')->check();
        $isStudent = Auth::guard('student')->check();
        
        // Logout the appropriate guard
        if ($isAdmin) {
            Auth::guard('web')->logout();
            $request->session()->forget('admin_logged_in');
        }
        
        if ($isStudent) {
            Auth::guard('student')->logout();
            $request->session()->forget('student_logged_in');
            $request->session()->forget('student_id');
            $request->session()->forget('student_login');
        }

        // Only invalidate session if both guards are logged out
        if (!$isAdmin && !$isStudent) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        } else {
            $request->session()->regenerate();
        }

        return redirect('/login')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Show Force Password Change Form
     */
    public function showPasswordChangeForm()
    {
        if (!Auth::guard('student')->check() && !session('student_id')) {
            return redirect()->route('login');
        }

        $student = Auth::guard('student')->user();

        if (!$student) {
            $student = Student::find(session('student_id'));
        }

        return view('students.change-password', compact('student'));
    }

    /**
     * Handle Password Change
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|confirmed',
        ]);

        $student = Auth::guard('student')->user();

        if (!$student) {
            $studentId = session('student_id');
            $student = Student::find($studentId);
        }

        if (!$student) {
            return redirect()->route('login')->withErrors([
                'errors' => 'Session expired. Please login again.'
            ]);
        }

        if (!Hash::check($request->current_password, $student->password)) {
            return back()->withErrors([
                'current_password' => 'Current password is incorrect.'
            ]);
        }

        $student->update([
            'password' => Hash::make($request->new_password),
            'password_changed' => true,
        ]);

        $request->session()->regenerate();

        return redirect()->route('students.dashboard')->with('success', 'Password changed successfully!');
    }
}