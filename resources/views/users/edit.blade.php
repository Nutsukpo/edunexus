@extends('layouts.master')

@section('title', 'Edit User')

@section('content')

<div class="container py-4">

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white">
            <h4 class="mb-0">Edit User</h4>
        </div>

        <div class="card-body">

            <form action="{{ route('users.update', $user->id) }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf
                @method('PUT')

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Full Name</label>

                        <input type="text"
                               name="name"
                               class="form-control"
                               value="{{ old('name', $user->name) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>

                        <input type="email"
                               name="email"
                               class="form-control"
                               value="{{ old('email', $user->email) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone</label>

                        <input type="text"
                               name="phone"
                               class="form-control"
                               value="{{ old('phone', $user->phone) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Role</label>

                        <select name="role" class="form-select">

                            @foreach($roles as $role)

                                <option value="{{ $role->name }}"
                                    {{ $user->role == $role->name ? 'selected' : '' }}>

                                    {{ ucfirst($role->name) }}

                                </option>

                            @endforeach

                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>

                        <select name="status" class="form-select">

                            <option value="active"
                                {{ $user->status == 'active' ? 'selected' : '' }}>
                                Active
                            </option>

                            <option value="inactive"
                                {{ $user->status == 'inactive' ? 'selected' : '' }}>
                                Inactive
                            </option>

                        </select>
                    </div>

                </div>

                <button class="btn btn-light">
                    <i class="fas fa-save me-1"></i>
                    Update User
                </button>

                <a href="{{ route('users.index') }}"
                   class="btn btn-secondary">
                    Cancel
                </a>

            </form>

        </div>

    </div>

</div>

@endsection