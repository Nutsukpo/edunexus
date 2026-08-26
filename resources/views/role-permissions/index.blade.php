@extends('layouts.master')

@section('title', 'Roles & Permissions')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Roles & Permissions</h4>
            <p class="text-muted mb-0">Manage what each EDUNEXUS role is allowed to do.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="px-4">Role</th>
                            <th>Assigned Permissions</th>
                            <th class="text-end px-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td class="px-4">
                                <strong>{{ $role->name }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-primary">
                                    {{ $role->permissions_count }}
                                </span>
                            </td>
                            <td class="text-end px-4">
                                <a href="{{ route('roles.permissions.edit', $role) }}"
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Manage Permissions
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">
                                No roles have been created yet.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
