@extends('layouts.master')

@section('title', 'Manage Role Permissions')

@section('content')
<div class="container-fluid">
    <form method="POST" action="{{ route('roles.permissions.update', $role) }}">
        @csrf
        @method('PUT')

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">{{ $role->name }}</h4>
                <p class="text-muted mb-0">Select the permissions this role should have.</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary" id="selectAll">
                    Select All
                </button>
                <button type="button" class="btn btn-outline-secondary" id="clearAll">
                    Clear All
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save Permissions
                </button>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @foreach($permissions as $group => $items)
            <div class="card border-0 shadow-sm mb-4 permission-group">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong>{{ $group }}</strong>
                    <label class="mb-0 small">
                        <input type="checkbox" class="module-select-all me-1">
                        Select module
                    </label>
                </div>

                <div class="card-body">
                    <div class="row">
                        @foreach($items as $permission)
                            <div class="col-md-4 col-lg-3 mb-2">
                                <label class="form-check d-flex align-items-center gap-2">
                                    <input
                                        type="checkbox"
                                        class="form-check-input permission-checkbox"
                                        name="permissions[]"
                                        value="{{ $permission->id }}"
                                        {{ in_array($permission->id, $rolePermissionIds) ? 'checked' : '' }}
                                    >
                                    <span>{{ ucwords(str_replace(['.', '-'], [' ', ' '], $permission->name)) }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const all = () => document.querySelectorAll('.permission-checkbox');

    document.getElementById('selectAll')?.addEventListener('click', () => {
        all().forEach(cb => cb.checked = true);
        document.querySelectorAll('.module-select-all').forEach(cb => cb.checked = true);
    });

    document.getElementById('clearAll')?.addEventListener('click', () => {
        all().forEach(cb => cb.checked = false);
        document.querySelectorAll('.module-select-all').forEach(cb => cb.checked = false);
    });

    document.querySelectorAll('.permission-group').forEach(group => {
        const moduleToggle = group.querySelector('.module-select-all');
        const boxes = group.querySelectorAll('.permission-checkbox');

        moduleToggle.addEventListener('change', function () {
            boxes.forEach(cb => cb.checked = this.checked);
        });

        const syncModuleToggle = () => {
            moduleToggle.checked = boxes.length > 0 &&
                Array.from(boxes).every(cb => cb.checked);
        };

        boxes.forEach(cb => cb.addEventListener('change', syncModuleToggle));
        syncModuleToggle();
    });
});
</script>
@endpush
@endsection
