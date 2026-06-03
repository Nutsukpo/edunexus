@extends('layouts.master')

@section('title', 'Users Management')

@section('content')

<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0 font-light">Users list</h3>
            <!-- <small class="text-muted">Manage system users and permissions</small> -->
        </div>

        <a href="{{ route('users.create') }}" class="btn btn-dark bg-white text-dark">
            <i class="fas fa-plus-circle me-1"></i> Add User
        </a>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Users Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <!-- <th>#</th> -->
                            <th>User Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th width="220">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($users as $user)

                            <tr>

                                <!-- <td>{{ $loop->iteration }}</td> -->

                                <td>
                                    <div class="d-flex align-items-center">

                                        <!-- <img
                                            src="{{ $user->profile_photo ? asset('storage/'.$user->profile_photo) : asset('images/default-user.png') }}"
                                            class="rounded-circle me-2"
                                            width="45"
                                            height="45"
                                            style="object-fit:cover;"
                                        > -->

                                        <div>
                                            <div class="fw-semibold">
                                                {{ $user->name }}
                                            </div>

                                            <small class="text-muted">
                                                Joined {{ $user->created_at->diffForHumans() }}
                                            </small>
                                        </div>

                                    </div>
                                </td>

                                <td>{{ $user->email }}</td>

                                <td>{{ $user->phone ?? '-' }}</td>

                                <td>
                                    <span class="badge bg-info text-dark bg-light">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>

                                <td>
                                    @if($user->status == 'active')
                                        <span class="badge bg-success">
                                            Active
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <td>

                                    <div class="btn-group">

                                        <a href="{{ route('users.show', $user->id) }}"
                                           class="btn btn-sm btn-light">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <a href="{{ route('users.edit', $user->id) }}"
                                           class="btn btn-sm btn-light">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="{{ route('users.toggle-status', $user->id) }}"
                                              method="POST">
                                            @csrf
                                            @method('PATCH')

                                            <button class="btn btn-sm btn-light">
                                                <i class="fas fa-sync"></i>
                                            </button>
                                        </form>

                                        <form action="{{ route('users.destroy', $user->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Delete this user?')">

                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-sm btn-light">
                                                <i class="fas fa-trash"></i>
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    No users found
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