@extends('layouts.master')

@section('title', 'Department Details')

@section('content')

<div class="container py-4">

    <div class="card shadow-sm border-0">

        <div class="card-body">

            <h3>{{ $department->name }}</h3>

            <p class="text-muted">{{ $department->description }}</p>

            <hr>

            <p><strong>Code:</strong> {{ $department->code ?? 'N/A' }}</p>

            <p><strong>Head:</strong> {{ $department->hod->name ?? 'Not Assigned' }}</p>

            <p>
                <strong>Status:</strong>
                @if($department->status == 'active')
                    <span class="badge bg-success">Active</span>
                @else
                    <span class="badge bg-danger">Inactive</span>
                @endif
            </p>
        </div>

    </div>

</div>

@endsection