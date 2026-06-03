@extends('layouts.master')

@section('title', 'Create User')

@section('content')

<div class="container py-4">

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white">
            <h4 class="mb-0">Create User</h4>
        </div>

        <div class="card-body">

            <form action="{{ route('users.store') }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Full Name</label>

                        <input type="text"
                               name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}">

                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>

                        <input type="email"
                               name="email"
                               class="form-control"
                               value="{{ old('email') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone</label>

                        <input type="text"
                               name="phone"
                               class="form-control"
                               value="{{ old('phone') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Role</label>

                        <select name="role" class="form-select">

                            <option value="">Select Role</option>

                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>

                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password</label>

                        <input type="password"
                               name="password"
                               class="form-control">
                    </div>

                </div>

                <div class="mt-4">

                    <button class="btn btn-light">
                        <i class="fas fa-save me-1"></i>
                        Save User
                    </button>

                    <a href="{{ route('users.index') }}"
                       class="btn btn-secondary">
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection