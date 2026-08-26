<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->latest()->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::where('guard_name', 'web')
            ->whereIn('name', [
                'Super Admin',
                'Administrator',
                'Teaching Staff',
                'Accountant',
                'MIS',
                'Power User',
                'Non-Teaching Staff',
            ])
            ->orderBy('name')
            ->get();

        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', 'exists:roles,name'],
            'status' => ['required', 'in:active,inactive'],
            'password' => ['required', 'string', 'min:8'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $imagePath = $request->hasFile('profile_photo')
            ? $request->file('profile_photo')->store('profiles', 'public')
            : null;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role, // compatibility only
            'status' => $request->status,
            'profile_photo' => $imagePath,
            'password' => Hash::make($request->password),
        ]);

        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        

        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::where('guard_name', 'web')
            ->whereIn('name', [
                'Super Admin',
                'Administrator',
                'Teaching Staff',
                'Accountant',
                'MIS',
                'Power User',
                'Non-Teaching Staff',
            ])
            ->orderBy('name')
            ->get();

        $user->load('roles');

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', 'exists:roles,name'],
            'status' => ['required', 'in:active,inactive'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        if ($request->role === 'Super Admin' && !$user->exists) {
            abort(403);
        }

        $imagePath = $user->profile_photo;

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $imagePath = $request->file('profile_photo')->store('profiles', 'public');
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role, // compatibility only
            'status' => $request->status,
            'profile_photo' => $imagePath,
        ]);

        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->hasRole('Super Admin') && User::role('Super Admin')->count() <= 1) {
            return back()->with('error', 'The last Super Admin cannot be deleted.');
        }

        if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(User $user)
    {
        if ($user->hasRole('Super Admin') && $user->status === 'active'
            && User::role('Super Admin')->where('status', 'active')->count() <= 1) {
            return back()->with('error', 'The last active Super Admin cannot be deactivated.');
        }

        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        return back()->with('success', 'Status updated successfully.');
    }
}
