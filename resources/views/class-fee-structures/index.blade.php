@extends('layouts.master')

@section('title', 'Class Fee Structures')

@section('content')
<div class="container-fluid">
    <div class="mb-4 mt-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1">
                    <i class="fas fa-coins me-2 text-primary"></i>
                    Class Fee Structures
                </h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Fee Structures</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('class-fee-structures.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Add Fee Structure
                </a>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('class-fee-structures.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Academic Year</label>
                    <select name="academic_year_id" class="form-select">
                        <option value="">All Years</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Class</label>
                    <select name="class_id" class="form-select">
                        <option value="">All Classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Required</label>
                    <select name="is_required" class="form-select">
                        <option value="">All</option>
                        <option value="1" {{ request('is_required') == '1' ? 'selected' : '' }}>Required</option>
                        <option value="0" {{ request('is_required') == '0' ? 'selected' : '' }}>Optional</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Fee Structures Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Class</th>
                            <th>Academic Year</th>
                            <th>Fee Name</th>
                            <th>Fee Type</th>
                            <th class="text-end">Amount</th>
                            <th>Required</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($feeStructures as $fee)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <span class="fw-semibold">{{ $fee->studentClass->name ?? 'N/A' }}</span>
                                </td>
                                <td>{{ $fee->academicYear->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="fw-semibold">{{ $fee->fee_name }}</span>
                                    @if($fee->description)
                                        <br>
                                        <small class="text-muted">{{ Str::limit($fee->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark">
                                        {{ $fee->fee_type_label }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold">{{ $fee->formatted_amount }}</span>
                                </td>
                                <td>
                                    @if($fee->is_required)
                                        <span class="badge bg-danger">Required</span>
                                    @else
                                        <span class="badge bg-secondary">Optional</span>
                                    @endif
                                </td>
                                <td>
                                    @if($fee->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    @if($fee->due_date)
                                        {{ $fee->due_date->format('M d, Y') }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('class-fee-structures.show', $fee->id) }}" 
                                           class="btn btn-sm btn-outline-info" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('class-fee-structures.edit', $fee->id) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger" 
                                                title="Delete"
                                                onclick="confirmDelete('{{ $fee->id }}', '{{ $fee->fee_name }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="fas fa-coins fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-0">No fee structures found.</p>
                                    <a href="{{ route('class-fee-structures.create') }}" class="btn btn-primary mt-3">
                                        <i class="fas fa-plus me-2"></i>
                                        Create First Fee Structure
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $feeStructures->firstItem() ?? 0 }} to {{ $feeStructures->lastItem() ?? 0 }} 
                    of {{ $feeStructures->total() }} entries
                </div>
                <div>
                    {{ $feeStructures->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the fee structure: <strong id="deleteFeeName"></strong>?</p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .table th {
        font-weight: 600;
        color: #495057;
        border-bottom-width: 2px;
    }
    .table td {
        vertical-align: middle;
    }
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }
    .badge {
        font-weight: 500;
        padding: 0.35rem 0.65rem;
    }
</style>

@push('scripts')
<script>
function confirmDelete(id, name) {
    $('#deleteFeeName').text(name);
    $('#deleteForm').attr('action', '/class-fee-structures/' + id);
    $('#deleteModal').modal('show');
}

$(document).ready(function() {
    // Auto-submit filter on change
    $('select[name="academic_year_id"], select[name="class_id"], select[name="status"], select[name="is_required"]').on('change', function() {
        $(this).closest('form').submit();
    });
});
</script>
@endpush

@endsection