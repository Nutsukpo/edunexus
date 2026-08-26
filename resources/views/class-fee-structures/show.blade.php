@extends('layouts.master')

@section('title', 'Fee Structure Details')

@section('content')
<div class="container-fluid">
    <div class="mb-4 mt-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1">
                    <i class="fas fa-coins me-2 text-primary"></i>
                    Fee Structure Details
                </h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('class-fee-structures.index') }}">Fee Structures</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Details</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('class-fee-structures.edit', $feeStructure->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-2"></i>
                    Edit
                </a>
                <a href="{{ route('class-fee-structures.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Back
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Fee Structure Details --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2 text-primary"></i>
                        Fee Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted small">Fee Name</label>
                            <p class="h5">{{ $feeStructure->fee_name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted small">Fee Type</label>
                            <p><span class="badge bg-info text-dark">{{ $feeStructure->fee_type_label }}</span></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted small">Amount</label>
                            <p class="h4 text-primary">{{ $feeStructure->formatted_amount }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted small">Due Date</label>
                            <p>{{ $feeStructure->due_date ? $feeStructure->due_date->format('M d, Y') : 'Not Set' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted small">Required</label>
                            <p>
                                @if($feeStructure->is_required)
                                    <span class="badge bg-danger">Required</span>
                                @else
                                    <span class="badge bg-secondary">Optional</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted small">Status</label>
                            <p>
                                @if($feeStructure->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted small">Class</label>
                            <p class="fw-semibold">{{ $feeStructure->studentClass->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted small">Academic Year</label>
                            <p>{{ $feeStructure->academicYear->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="fw-semibold text-muted small">Description</label>
                            <p>{{ $feeStructure->description ?: 'No description provided.' }}</p>
                        </div>
                        @if($feeStructure->metadata)
                            <div class="col-md-12 mb-3">
                                <label class="fw-semibold text-muted small">Metadata</label>
                                <pre class="bg-light p-3 rounded">{{ json_encode($feeStructure->metadata, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Statistics --}}
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2 text-primary"></i>
                        Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="bg-light p-3 rounded text-center">
                                <small class="text-muted">Total Students</small>
                                <h3 class="mb-0">{{ $totalStudents ?? 0 }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light p-3 rounded text-center">
                                <small class="text-muted">Total Amount</small>
                                <h3 class="mb-0 text-primary">{{ $feeStructure->formatted_amount }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light p-3 rounded text-center">
                                <small class="text-muted">Total Paid</small>
                                <h3 class="mb-0 text-success">GHS {{ number_format($totalPaid ?? 0, 2) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Quick Actions --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <a href="{{ route('class-fee-structures.edit', $feeStructure->id) }}" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-edit me-2"></i>
                        Edit Fee Structure
                    </a>
                    <button type="button" 
                            class="btn btn-outline-danger w-100"
                            onclick="confirmDelete('{{ $feeStructure->id }}', '{{ $feeStructure->fee_name }}')">
                        <i class="fas fa-trash me-2"></i>
                        Delete Fee Structure
                    </button>
                </div>
            </div>

            {{-- Created Info --}}
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-clock me-2 text-primary"></i>
                        Record Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Created:</span>
                        <span>{{ $feeStructure->created_at ? $feeStructure->created_at->format('M d, Y H:i') : 'N/A' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Last Updated:</span>
                        <span>{{ $feeStructure->updated_at ? $feeStructure->updated_at->format('M d, Y H:i') : 'N/A' }}</span>
                    </div>
                    @if($feeStructure->creator)
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Created By:</span>
                            <span>{{ $feeStructure->creator->name }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Related Actions --}}
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="mb-2">
                        <i class="fas fa-link me-2 text-primary"></i>
                        Related Actions
                    </h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2">
                            <a href="{{ route('fee-payments.index') }}?fee_structure_id={{ $feeStructure->id }}" class="text-decoration-none">
                                <i class="fas fa-credit-card me-1"></i> View Payments
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="{{ route('fee-payments.create') }}?fee_structure_id={{ $feeStructure->id }}" class="text-decoration-none">
                                <i class="fas fa-plus-circle me-1"></i> Record Payment
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('student-fee-accounts.index') }}?fee_structure_id={{ $feeStructure->id }}" class="text-decoration-none">
                                <i class="fas fa-users me-1"></i> View Student Accounts
                            </a>
                        </li>
                    </ul>
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
                <p class="text-danger"><small>This action cannot be undone. All associated student fee items will also be deleted.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" action="{{ route('class-fee-structures.destroy', $feeStructure->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border-radius: 10px;
        overflow: hidden;
    }
    
    .card-header {
        background-color: #f8f9fa;
    }
    
    .bg-light {
        background-color: #f8f9fa !important;
    }
    
    .rounded {
        border-radius: 8px !important;
    }
</style>

@push('scripts')
<script>
function confirmDelete(id, name) {
    $('#deleteFeeName').text(name);
    $('#deleteModal').modal('show');
}
</script>
@endpush

@endsection