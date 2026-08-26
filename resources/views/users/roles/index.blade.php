@extends('layouts.master')

@section('title', 'Roles & Permissions')

@section('content')

<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h4 class="mb-1">
                <i class="fas fa-user-shield me-2"></i>
                Roles & Permissions
            </h4>

            <p class="text-muted mb-0">
                Manage the permissions assigned to EDUNEXUS roles.
            </p>
        </div>

    </div>


    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>
        </div>
    @endif


    {{-- Error Message --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>
        </div>
    @endif


    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white py-3">

            <div class="d-flex justify-content-between align-items-center">

                <div>
                    <h5 class="mb-0">
                        System Roles
                    </h5>

                    <small class="text-muted">
                        Select a role to manage its permissions.
                    </small>
                </div>

            </div>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>
                            <th class="ps-4">#</th>
                            <th>Role</th>
                            <th>Permissions</th>
                            <th>Guard</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>

                    </thead>

                    <tbody>

                        @forelse($roles as $index => $role)

                            <tr>

                                <td class="ps-4">
                                    {{ $index + 1 }}
                                </td>

                                <td>

                                    <div class="d-flex align-items-center">

                                        <div class="rounded-circle
                                                    bg-primary
                                                    text-white
                                                    d-flex
                                                    align-items-center
                                                    justify-content-center
                                                    me-2"
                                             style="width:40px;height:40px;">

                                            <i class="fas fa-user-shield"></i>

                                        </div>

                                        <div>

                                            <strong>
                                                {{ $role->name }}
                                            </strong>

                                            @if($role->name === 'Super Admin')

                                                <span class="badge bg-danger ms-2">
                                                    Full Access
                                                </span>

                                            @endif

                                        </div>

                                    </div>

                                </td>


                                <td>

                                    <span class="badge bg-info text-dark">

                                        {{ $role->permissions_count }}

                                        {{ Str::plural('permission', $role->permissions_count) }}

                                    </span>

                                </td>


                                <td>

                                    <span class="badge bg-secondary">
                                        {{ $role->guard_name }}
                                    </span>

                                </td>


                                <td class="text-end pe-4">

                                    @can('roles.manage-permissions')

                                        <a href="{{ route('roles.permissions.edit', $role) }}"
                                           class="btn btn-sm btn-primary">

                                            <i class="fas fa-sliders-h me-1"></i>
                                            Manage Permissions

                                        </a>

                                    @else

                                        <span class="text-muted">
                                            No access
                                        </span>

                                    @endcan

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5"
                                    class="text-center py-5">

                                    <i class="fas fa-user-shield
                                              fa-3x
                                              text-muted
                                              mb-3">
                                    </i>

                                    <h6>
                                        No roles found
                                    </h6>

                                    <p class="text-muted mb-0">
                                        Run the RolesAndPermissionsSeeder
                                        to create the system roles.
                                    </p>

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