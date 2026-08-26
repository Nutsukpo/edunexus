@extends('layouts.master')

@section('title', 'Role Permissions')

@section('content')
<div class="container-fluid py-4">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">Role Permissions</h4>
                <small class="text-muted">Assign permissions to each EDUNEXUS role.</small>
            </div>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="row">
                <div class="col-lg-3 mb-4">
                    <label class="form-label fw-semibold">Select Role</label>
                    <div class="list-group">
                        @foreach($roles as $item)
                            <a href="{{ route('roles.permissions.edit', $item) }}"
                               class="list-group-item list-group-item-action {{ $item->id === $role->id ? 'active' : '' }}">
                                {{ $item->name }}
                                <span class="badge {{ $item->id === $role->id ? 'bg-light text-dark' : 'bg-secondary' }} float-end">
                                    {{ $item->permissions_count ?? $item->permissions()->count() }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="col-lg-9">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-0">{{ $role->name }}</h5>
                            @if($role->name === 'Super Admin')
                                <small class="text-danger">Super Admin always retains all permissions.</small>
                            @else
                                <small class="text-muted">Tick the permissions this role should have.</small>
                            @endif
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll">Select All</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="clearAll">Clear All</button>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('roles.permissions.update', $role) }}">
                        @csrf
                        @method('PUT')

                        @foreach($groupedPermissions as $group => $groupPermissions)
                            <div class="card border mb-3 permission-group">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <strong>{{ $group }}</strong>
                                    <button type="button" class="btn btn-sm btn-link group-toggle">Select group</button>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($groupPermissions as $permission)
                                            <div class="col-md-6 col-xl-4 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input permission-checkbox"
                                                           type="checkbox"
                                                           name="permissions[]"
                                                           value="{{ $permission->id }}"
                                                           id="permission_{{ $permission->id }}"
                                                           {{ in_array($permission->id, $assignedPermissionIds, true) ? 'checked' : '' }}
                                                           {{ $role->name === 'Super Admin' ? 'disabled checked' : '' }}>
                                                    <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                        {{ ucwords(str_replace(['.', '_', '-'], ' ', $permission->name)) }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @if($role->name !== 'Super Admin')
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Permissions
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('selectAll')?.addEventListener('click', function () {
    document.querySelectorAll('.permission-checkbox:not(:disabled)').forEach(cb => cb.checked = true);
});
document.getElementById('clearAll')?.addEventListener('click', function () {
    document.querySelectorAll('.permission-checkbox:not(:disabled)').forEach(cb => cb.checked = false);
});
document.querySelectorAll('.group-toggle').forEach(button => {
    button.addEventListener('click', function () {
        const group = this.closest('.permission-group');
        const boxes = group.querySelectorAll('.permission-checkbox:not(:disabled)');
        const shouldCheck = Array.from(boxes).some(cb => !cb.checked);
        boxes.forEach(cb => cb.checked = shouldCheck);
    });
});
</script>
@endpush
@endsection
