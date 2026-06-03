@extends('layouts.master')

@section('title', 'Departments')

@section('content')

<div class="container-fluid py-4">

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h5 class="fw-bold mb-1">
                <i class="fas fa-building me-2 text-danger"></i>
                Departments
            </h5>
        </div>
        <a href="{{ route('departments.create') }}" class="btn btn-white text-dark">
            <i class="fas fa-plus-circle me-1"></i> Add Department
        </a>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- DEPARTMENTS TABLE --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="departmentsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="50">#</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Head of Department</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departments as $index => $department)
                            <tr>
                                <td class="text-muted">{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $department->name }}</td>
                                <td>
                                    @if($department->code)
                                        <span class="badge bg-white text-dark">{{ $department->code }}</span>
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </td>
                                <td>
                                    @if($department->hod)
                                        <div>
                                            <div class="fw-semibold small">
                                                {{ $department->hod->first_name }} {{ $department->hod->last_name }}
                                            </div>
                                            @if($department->hod->staff_id)
                                                <small class="text-muted">{{ $department->hod->staff_id }}</small>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">Not Assigned</span>
                                    @endif
                                </td>
                                <td>
                                    @if($department->status == 'active')
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $department->created_at->format('d M, Y') }}
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('departments.show', $department->id) }}" 
                                           class="btn btn-outline-white" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('departments.edit', $department->id) }}" 
                                           class="btn btn-outline-white" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-building fa-3x mb-3 d-block"></i>
                                    No departments found. Click "Add Department" to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modals --}}
@foreach($departments as $department)

<div class="modal fade"
     id="deleteModal{{ $department->id }}"
     tabindex="-1"
     aria-labelledby="deleteModalLabel{{ $department->id }}"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header bg-danger text-white">

                <h5 class="modal-title"
                    id="deleteModalLabel{{ $department->id }}">

                    <i class="fas fa-trash me-2"></i>
                    Confirm Delete

                </h5>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>

            </div>

            <div class="modal-body">

                <p>
                    Are you sure you want to delete
                    <strong>{{ $department->name }}</strong>?
                </p>

                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    This action cannot be undone.
                </div>

            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                    Cancel

                </button>

                <form action="{{ route('departments.destroy', $department->id) }}"
                      method="POST">

                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="btn btn-danger">

                        Delete Department

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>

@endforeach

<style>
    .table th {
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
    }
    
    .table td {
        font-size: 13px;
        vertical-align: middle;
    }
    
    .btn-group-sm .btn {
        padding: 4px 8px;
    }
    
    .card {
        border-radius: 12px;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.03);
    }
    
    .alert {
        border-radius: 10px;
    }
</style>

@endsection