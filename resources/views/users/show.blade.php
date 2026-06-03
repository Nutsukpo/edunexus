@extends('layouts.master')

@section('title', 'User Details')

@section('content')

<div class="container py-4">

    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <div class="row">

                <div class="col-md-4 text-center">

                    <img src="{{ $user->profile_photo ? asset('storage/'.$user->profile_photo) : asset('images/default-user.png') }}"
                         class="rounded-circle shadow"
                         width="180"
                         height="180"
                         style="object-fit:cover;">

                    <h4 class="mt-3">
                        {{ $user->name }}
                    </h4>

                    <span class="badge bg-primary">
                        {{ ucfirst($user->role) }}
                    </span>

                </div>

                <div class="col-md-8">

                    <table class="table">

                        <tr>
                            <th width="200">Full Name</th>
                            <td>{{ $user->name }}</td>
                        </tr>

                        <tr>
                            <th>Email</th>
                            <td>{{ $user->email }}</td>
                        </tr>

                        <tr>
                            <th>Phone</th>
                            <td>{{ $user->phone ?? '-' }}</td>
                        </tr>

                        <tr>
                            <th>Status</th>
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
                        </tr>

                        <tr>
                            <th>Created At</th>
                            <td>{{ $user->created_at->format('d M Y h:i A') }}</td>
                        </tr>

                    </table>

                    <div class="mt-4">

                        <a href="{{ route('users.edit', $user->id) }}"
                           class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>
                            Edit
                        </a>

                        <a href="{{ route('users.index') }}"
                           class="btn btn-secondary">
                            Back
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection