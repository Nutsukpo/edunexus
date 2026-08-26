@extends('layouts.master')

@section('title', 'Salary Structures')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                {{-- Card Header --}}
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-money-check-alt text-primary me-1"></i> Salary Structures
                        <span class="badge bg-secondary text-white ms-1">{{ $salaryStructures->total() }}</span>
                    </h5>
                    <a href="{{ route('salary-structures.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus-circle me-1"></i> Add Salary Structure
                    </a>
                </div>

                {{-- Card Body --}}
                <div class="card-body">
                    {{-- Alert Messages --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show border-start border-success border-4 rounded-0" role="alert">
                            <i class="fas fa-check-circle text-success me-1"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show border-start border-danger border-4 rounded-0" role="alert">
                            <i class="fas fa-exclamation-circle text-danger me-1"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- Summary Cards --}}
                    <div class="row g-3 mb-4">
                        <div class="col-lg-3 col-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted text-uppercase small fw-semibold mb-1">
                                                <i class="fas fa-users me-1"></i> Total Structures
                                            </h6>
                                            <h3 class="fw-bold mb-0 text-dark">{{ $salaryStructures->total() }}</h3>
                                        </div>
                                        <div class="bg-light rounded-circle p-3 d-flex align-items-center justify-content-center">
                                            <i class="fas fa-money-check-alt text-secondary fs-4"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted text-uppercase small fw-semibold mb-1">
                                                <i class="fas fa-check-circle me-1"></i> Active
                                            </h6>
                                            <h3 class="fw-bold mb-0 text-success">{{ $salaryStructures->where('is_active', true)->count() }}</h3>
                                        </div>
                                        <div class="bg-light rounded-circle p-3 d-flex align-items-center justify-content-center">
                                            <i class="fas fa-check-circle text-success fs-4"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted text-uppercase small fw-semibold mb-1">
                                                <i class="fas fa-times-circle me-1"></i> Inactive
                                            </h6>
                                            <h3 class="fw-bold mb-0 text-danger">{{ $salaryStructures->where('is_active', false)->count() }}</h3>
                                        </div>
                                        <div class="bg-light rounded-circle p-3 d-flex align-items-center justify-content-center">
                                            <i class="fas fa-times-circle text-danger fs-4"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted text-uppercase small fw-semibold mb-1">
                                                <i class="fas fa-money-bill-wave me-1"></i> Total Basic Salary
                                            </h6>
                                            <h3 class="fw-bold mb-0 text-dark">GHC {{ number_format($salaryStructures->sum('basic_salary'), 0) }}</h3>
                                        </div>
                                        <div class="bg-light rounded-circle p-3 d-flex align-items-center justify-content-center">
                                            <i class="fas fa-money-bill-wave text-secondary fs-4"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-uppercase small fw-semibold text-dark" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                        <i class="fas fa-user me-1 text-secondary"></i> Staff
                                    </th>
                                    <th class="text-uppercase small fw-semibold text-dark" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                        <i class="fas fa-money-bill-wave me-1 text-secondary"></i> Basic Salary
                                    </th>
                                    <th class="text-uppercase small fw-semibold text-dark" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                        <i class="fas fa-plus-circle me-1 text-secondary"></i> Total Allowance
                                    </th>
                                    <th class="text-uppercase small fw-semibold text-dark" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                        <i class="fas fa-minus-circle me-1 text-secondary"></i> Total Deduction
                                    </th>
                                    <th class="text-uppercase small fw-semibold text-dark" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                        <i class="fas fa-calculator me-1 text-secondary"></i> Net Salary
                                    </th>
                                    <th class="text-uppercase small fw-semibold text-dark" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                        <i class="fas fa-toggle-on me-1 text-secondary"></i> Status
                                    </th>
                                    <th class="text-uppercase small fw-semibold text-dark text-center" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                        <i class="fas fa-cog me-1 text-secondary"></i> Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($salaryStructures as $salary)
                                    <tr>
                                        {{-- Staff Info --}}
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; flex-shrink: 0;">
                                                    <i class="fas fa-user text-secondary" style="font-size: 14px;"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold text-dark" style="font-size: 0.875rem;">
                                                        {{ $salary->staff->full_name ?? $salary->staff->name ?? 'N/A' }}
                                                    </div>
                                                    @if(isset($salary->staff->staff_code))
                                                        <small class="text-muted" style="font-size: 0.7rem;">
                                                            <i class="fas fa-id-card me-1"></i> {{ $salary->staff->staff_code }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Monetary Values --}}
                                        <td class="fw-semibold text-dark" style="font-size: 0.85rem;">
                                            GHC {{ number_format($salary->basic_salary, 2) }}
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="fw-semibold text-success" style="font-size: 0.85rem;">
                                                    GHC {{ number_format($salary->total_allowance, 2) }}
                                                </span>
                                                {{-- Show allowance breakdown on hover --}}
                                                <button type="button" class="btn btn-sm btn-outline-secondary ms-1" 
                                                        style="padding: 0.15rem 0.4rem; font-size: 0.65rem; border-color: #dee2e6;"
                                                        data-bs-toggle="popover" 
                                                        data-bs-html="true"
                                                        data-bs-trigger="hover focus"
                                                        data-bs-content="
                                                            <div class='fw-bold text-dark text-start' style='font-size: 0.8rem;'>Allowances Breakdown:</div>
                                                            <div class='text-start' style='font-size: 0.75rem;'>
                                                                <span class='text-muted'>🏠 Housing:</span> <span class='text-dark'>GHC {{ number_format($salary->housing_allowance ?? 0, 2) }}</span><br>
                                                                <span class='text-muted'>🚗 Transport:</span> <span class='text-dark'>GHC {{ number_format($salary->transport_allowance ?? 0, 2) }}</span><br>
                                                                <span class='text-muted'>🏥 Medical:</span> <span class='text-dark'>GHC {{ number_format($salary->medical_allowance ?? 0, 2) }}</span><br>
                                                                <span class='text-muted'>💼 Responsibility:</span> <span class='text-dark'>GHC {{ number_format($salary->responsibility_allowance ?? 0, 2) }}</span><br>
                                                                <span class='text-muted'>📦 Other:</span> <span class='text-dark'>GHC {{ number_format($salary->other_allowance ?? 0, 2) }}</span>
                                                            </div>
                                                            <div class='mt-1 pt-1 border-top text-start' style='font-size: 0.75rem;'>
                                                                <span class='fw-bold text-dark'>Total:</span> <span class='fw-bold text-success'>GHC {{ number_format($salary->total_allowance, 2) }}</span>
                                                            </div>
                                                        "
                                                        title="Allowances Breakdown">
                                                    <i class="fas fa-info-circle" style="font-size: 10px;"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="fw-semibold text-danger" style="font-size: 0.85rem;">
                                                    GHC {{ number_format($salary->total_deduction, 2) }}
                                                </span>
                                                {{-- Show deduction breakdown on hover --}}
                                                <button type="button" class="btn btn-sm btn-outline-secondary ms-1" 
                                                        style="padding: 0.15rem 0.4rem; font-size: 0.65rem; border-color: #dee2e6;"
                                                        data-bs-toggle="popover" 
                                                        data-bs-html="true"
                                                        data-bs-trigger="hover focus"
                                                        data-bs-content="
                                                            <div class='fw-bold text-dark text-start' style='font-size: 0.8rem;'>Deductions Breakdown:</div>
                                                            <div class='text-start' style='font-size: 0.75rem;'>
                                                                <span class='text-muted'>💰 Tax:</span> <span class='text-dark'>GHC {{ number_format($salary->tax ?? 0, 2) }}</span><br>
                                                                <span class='text-muted'>🏦 SSNIT:</span> <span class='text-dark'>GHC {{ number_format($salary->ssnit ?? 0, 2) }}</span><br>
                                                                <span class='text-muted'>📊 Tier 2:</span> <span class='text-dark'>GHC {{ number_format($salary->tier2 ?? 0, 2) }}</span><br>
                                                                <span class='text-muted'>📊 Tier 3:</span> <span class='text-dark'>GHC {{ number_format($salary->tier3 ?? 0, 2) }}</span><br>
                                                                <span class='text-muted'>💰 Loans:</span> <span class='text-dark'>GHC {{ number_format($salary->loan_deduction ?? 0, 2) }}</span><br>
                                                                <span class='text-muted'>📦 Other:</span> <span class='text-dark'>GHC {{ number_format($salary->other_deduction ?? 0, 2) }}</span>
                                                            </div>
                                                            <div class='mt-1 pt-1 border-top text-start' style='font-size: 0.75rem;'>
                                                                <span class='fw-bold text-dark'>Total:</span> <span class='fw-bold text-danger'>GHC {{ number_format($salary->total_deduction, 2) }}</span>
                                                            </div>
                                                        "
                                                        title="Deductions Breakdown">
                                                    <i class="fas fa-info-circle" style="font-size: 10px;"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="fw-bold text-dark" style="font-size: 0.9rem;">
                                            GHC {{ number_format($salary->net_salary, 2) }}
                                        </td>

                                        {{-- Status Badge --}}
                                        <td>
                                            @if($salary->is_active)
                                                <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill" style="font-size: 0.7rem; font-weight: 500;">
                                                    <i class="fas fa-check-circle me-1" style="font-size: 10px;"></i> Active
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill" style="font-size: 0.7rem; font-weight: 500;">
                                                    <i class="fas fa-times-circle me-1" style="font-size: 10px;"></i> Inactive
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Actions --}}
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('salary-structures.edit', $salary->id) }}"
                                                class="btn btn-outline-secondary btn-sm"
                                                style="padding: 0.2rem 0.5rem; font-size: 0.75rem; border-color: #dee2e6;"
                                                title="Edit Salary Structure">
                                                    <i class="fas fa-edit" style="font-size: 12px;"></i>
                                                </a>
                                                <form action="{{ route('salary-structures.destroy', $salary->id) }}"
                                                    method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-outline-secondary btn-sm"
                                                            style="padding: 0.2rem 0.5rem; font-size: 0.75rem; border-color: #dee2e6;"
                                                            title="Delete Salary Structure"
                                                            onclick="return confirm('Are you sure you want to delete the salary structure for {{ $salary->staff->full_name ?? $salary->staff->name ?? 'this staff' }}? This action cannot be undone.');">
                                                        <i class="fas fa-trash-alt" style="font-size: 12px;"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    {{-- Empty State --}}
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fas fa-money-check-alt text-muted" style="font-size: 48px;"></i>
                                            <h5 class="fw-semibold text-dark mt-3">No Salary Structures Found</h5>
                                            <p class="text-muted mb-3">Click "Add Salary Structure" to create your first salary structure.</p>
                                            <a href="{{ route('salary-structures.create') }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-plus-circle me-1"></i> Add Salary Structure
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($salaryStructures->hasPages())
                        <div class="d-flex flex-wrap justify-content-between align-items-center mt-3">
                            <div class="text-muted small">
                                Showing {{ $salaryStructures->firstItem() ?? 0 }} to {{ $salaryStructures->lastItem() ?? 0 }}
                                of {{ $salaryStructures->total() }} entries
                            </div>
                            <div>
                                {{ $salaryStructures->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize popovers
    $('[data-bs-toggle="popover"]').popover({
        container: 'body',
        placement: 'bottom',
        sanitize: false
    });
});
</script>
@endpush

@push('styles')
<style>
    /* Utility overrides for consistent spacing */
    .gap-1 { gap: 0.25rem; }
    .gap-2 { gap: 0.5rem; }
    .gap-3 { gap: 1rem; }

    /* Card & table refinements */
    .card {
        border: none;
        border-radius: 0.75rem;
    }
    .card-header {
        background: #ffffff;
        border-bottom: 1px solid #e9ecef;
        border-radius: 0.75rem 0.75rem 0 0 !important;
        padding: 1rem 1.25rem;
    }
    .table th {
        border-top: none;
        border-bottom: 2px solid #dee2e6;
    }
    .table td {
        vertical-align: middle;
        border-bottom: 1px solid #f1f3f5;
    }

    /* Hover effects */
    .table-hover tbody tr:hover {
        background-color: #f8f9fa !important;
        transition: background-color 0.15s ease-in-out;
    }

    /* Button refinements */
    .btn-outline-secondary {
        border-color: #dee2e6;
        color: #6c757d;
    }
    .btn-outline-secondary:hover {
        background-color: #f8f9fa;
        border-color: #c4c9d0;
        color: #212529;
    }

    /* Pagination refinements */
    .pagination .page-item.active .page-link {
        background: #212529;
        border-color: #212529;
        color: #ffffff;
    }
    .pagination .page-link {
        color: #212529;
        border-color: #dee2e6;
    }
    .pagination .page-link:hover {
        background: #f8f9fa;
        color: #212529;
    }

    /* Badge styles using Bootstrap utility classes */
    .bg-success-subtle {
        background-color: #d1e7dd !important;
    }
    .bg-danger-subtle {
        background-color: #f8d7da !important;
    }

    /* Ensure all text remains dark */
    .text-dark {
        color: #212529 !important;
    }
    .text-secondary {
        color: #6c757d !important;
    }

    /* Alert refinements */
    .alert {
        border-radius: 0.5rem;
    }
    .border-start {
        border-left-width: 4px !important;
    }
    .rounded-0 {
        border-radius: 0 !important;
    }

    /* Responsive improvements */
    @media (max-width: 576px) {
        .card-header {
            flex-direction: column;
            align-items: stretch !important;
        }
        .card-header .btn {
            width: 100%;
        }
    }

    /* Popover customization */
    .popover {
        max-width: 350px !important;
    }
    .popover-body {
        padding: 0.75rem 1rem;
    }
    .popover-body .text-start {
        font-size: 0.9rem;
    }
</style>
@endpush