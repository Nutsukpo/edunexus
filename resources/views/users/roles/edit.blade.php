@extends('layouts.master')

@section('title', 'Manage Role Permissions')

@section('content')

<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h4 class="mb-1">

                <i class="fas fa-user-shield me-2"></i>

                {{ $role->name }}

            </h4>

            <p class="text-muted mb-0">
                Manage permissions assigned to this role.
            </p>

        </div>


        <a href="{{ route('roles.permissions.index') }}"
           class="btn btn-outline-secondary">

            <i class="fas fa-arrow-left me-1"></i>

            Back to Roles

        </a>

    </div>


    {{-- Success --}}
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


    {{-- Validation Errors --}}
    @if($errors->any())

        <div class="alert alert-danger">

            <strong>
                Please correct the following:
            </strong>

            <ul class="mb-0 mt-2">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif


    <form method="POST"
          action="{{ route('roles.permissions.update', $role) }}">

        @csrf
        @method('PUT')


        {{-- Role Information --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-body">

                <div class="row align-items-center">

                    <div class="col-md-8">

                        <h5 class="mb-1">
                            {{ $role->name }}
                        </h5>

                        <p class="text-muted mb-0">

                            Guard:
                            <strong>{{ $role->guard_name }}</strong>

                        </p>

                    </div>


                    <div class="col-md-4 text-md-end mt-3 mt-md-0">

                        <span class="badge bg-primary fs-6">

                            {{ count($assignedPermissionIds) }}

                            permissions assigned

                        </span>

                    </div>

                </div>

            </div>

        </div>


        @if($role->name === 'Super Admin')

            {{-- Super Admin --}}
            <div class="alert alert-warning">

                <div class="d-flex">

                    <i class="fas fa-shield-alt fa-2x me-3"></i>

                    <div>

                        <strong>Super Admin Protection</strong>

                        <p class="mb-0 mt-1">

                            Super Admin automatically has all EDUNEXUS
                            permissions. These permissions cannot be
                            removed from this screen.

                        </p>

                    </div>

                </div>

            </div>

        @endif


        {{-- Permission Groups --}}
        @foreach($permissions as $group => $groupPermissions)

            @php
                $groupLabel = ucwords(
                    str_replace(['-', '_'], ' ', $group)
                );
            @endphp


            <div class="card border-0 shadow-sm mb-4">

                {{-- Group Header --}}
                <div class="card-header bg-white py-3">

                    <div class="d-flex
                                justify-content-between
                                align-items-center">

                        <div>

                            <h6 class="mb-0">

                                <i class="fas fa-folder-open
                                          text-primary
                                          me-2">
                                </i>

                                {{ $groupLabel }}

                            </h6>

                            <small class="text-muted">

                                {{ $groupPermissions->count() }}
                                permissions

                            </small>

                        </div>


                        @if($role->name !== 'Super Admin')

                            <div>

                                <button type="button"
                                        class="btn btn-sm btn-outline-primary select-group">

                                    Select All

                                </button>

                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary clear-group">

                                    Clear

                                </button>

                            </div>

                        @endif

                    </div>

                </div>


                {{-- Permissions --}}
                <div class="card-body">

                    <div class="row">

                        @foreach($groupPermissions as $permission)

                            <div class="col-md-6 col-lg-4 mb-3">

                                <div class="form-check">

                                    <input
                                        class="form-check-input permission-checkbox group-{{ Str::slug($group) }}"
                                        type="checkbox"
                                        name="permissions[]"
                                        value="{{ $permission->id }}"
                                        id="permission_{{ $permission->id }}"

                                        @checked(
                                            in_array(
                                                $permission->id,
                                                $assignedPermissionIds
                                            )
                                        )

                                        @disabled(
                                            $role->name === 'Super Admin'
                                        )
                                    >

                                    <label
                                        class="form-check-label"
                                        for="permission_{{ $permission->id }}">

                                        {{ ucwords(
                                            str_replace(
                                                ['-', '_'],
                                                ' ',
                                                Str::after(
                                                    $permission->name,
                                                    '.'
                                                )
                                            )
                                        ) }}

                                    </label>

                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>

            </div>

        @endforeach


        {{-- Save --}}
        @if($role->name !== 'Super Admin')

            <div class="card border-0 shadow-sm mb-5">

                <div class="card-body">

                    <div class="d-flex
                                justify-content-between
                                align-items-center">

                        <div>

                            <h6 class="mb-1">
                                Save Role Permissions
                            </h6>

                            <small class="text-muted">

                                Changes will immediately affect users
                                assigned to this role.

                            </small>

                        </div>


                        <div>

                            <a href="{{ route('roles.permissions.index') }}"
                               class="btn btn-light me-2">

                                Cancel

                            </a>

                            <button type="submit"
                                    class="btn btn-primary">

                                <i class="fas fa-save me-1"></i>

                                Save Permissions

                            </button>

                        </div>

                    </div>

                </div>

            </div>

        @endif

    </form>

</div>

@endsection


@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Select All Within Group
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.select-group')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const card = this.closest('.card');

                card.querySelectorAll(
                    '.permission-checkbox'
                ).forEach(function (checkbox) {

                    checkbox.checked = true;

                });

            });

        });


    /*
    |--------------------------------------------------------------------------
    | Clear Group
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.clear-group')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const card = this.closest('.card');

                card.querySelectorAll(
                    '.permission-checkbox'
                ).forEach(function (checkbox) {

                    checkbox.checked = false;

                });

            });

        });

});

</script>

@endpush