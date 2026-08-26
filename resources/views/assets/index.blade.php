@extends('layouts.master')

@section('title', 'Asset Register')

@section('content')
<div class="container-fluid">
    <!-- Page Header with Action Button -->
    <div class="row mb-4 mt-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0 text-dark font-weight-bold">
                        <i class="fas fa-boxes text-primary mr-2"></i>Asset Register
                    </h4>
                    <small class="text-muted">Manage and track all institutional assets</small>
                </div>
                <div>
                    <a href="{{ route('assets.create') }}" class="btn btn-primary btn-lg shadow-sm">
                        <i class="fas fa-plus-circle mr-2"></i>Add New Asset
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-info"><i class="fas fa-cubes"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Assets</span>
                    <span class="info-box-number h5 mb-0 font-weight-bold">{{ number_format($stats['total']) }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Available</span>
                    <span class="info-box-number h5 mb-0 font-weight-bold">{{ number_format($stats['available']) }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-primary"><i class="fas fa-user-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">In Use</span>
                    <span class="info-box-number h5 mb-0 font-weight-bold">{{ number_format($stats['assigned']) }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-warning"><i class="fas fa-tools"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Maintenance</span>
                    <span class="info-box-number h5 mb-0 font-weight-bold">{{ number_format($stats['maintenance']) }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Damaged</span>
                    <span class="info-box-number h5 mb-0 font-weight-bold">{{ number_format($stats['damaged']) }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-secondary"><i class="fas fa-trash-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Disposed</span>
                    <span class="info-box-number h5 mb-0 font-weight-bold">{{ number_format($stats['disposed']) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h6 class="mb-0 text-muted">
                        <i class="fas fa-list-ul mr-2"></i>Asset List
                        <span class="badge badge-secondary ml-2">{{ number_format($assets->total()) }}</span>
                    </h6>
                </div>
                <div class="col-md-8">
                    <form method="GET" action="{{ route('assets.index') }}" class="row g-2">
                        <div class="col-md-4">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search assets..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="category_id" class="form-control form-control-sm">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-control form-control-sm">
                                <option value="">All Status</option>
                                <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                                <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>In Use</option>
                                <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="damaged" {{ request('status') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                                <option value="disposed" {{ request('status') == 'disposed' ? 'selected' : '' }}>Disposed</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="condition" class="form-control form-control-sm">
                                <option value="">All Condition</option>
                                <option value="new" {{ request('condition') == 'new' ? 'selected' : '' }}>New</option>
                                <option value="good" {{ request('condition') == 'good' ? 'selected' : '' }}>Good</option>
                                <option value="fair" {{ request('condition') == 'fair' ? 'selected' : '' }}>Fair</option>
                                <option value="poor" {{ request('condition') == 'poor' ? 'selected' : '' }}>Poor</option>
                                <option value="damaged" {{ request('condition') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th width="30">#</th>
                            <th width="200">Asset Code</th>
                            <th width="150">Name</th>
                            <th width="150">Category</th>
                            <th width="140">Serial Number</th>
                            <th width="100">Status</th>
                            <th width="100">Condition</th>
                            <th width="150">Assigned To</th>
                            <th width="200" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $asset)
                            <tr>
                                <td class="align-middle text-center">{{ $loop->iteration }}</td>
                                <td class="align-middle">
                                    <span class="text-dark px-3 py-2">
                                        {{ $asset->asset_code }}
                                    </span>
                                </td>
                                <td class="align-middle">
                                    <a href="{{ route('assets.show', $asset->id) }}" class="text-dark font-weight-bold">
                                        {{ Str::limit($asset->name, 35) }}
                                    </a>
                                </td>
                                <td class="align-middle">
                                    <span class="text-dark">
                                        <!-- <i class="fas fa-tag text-muted mr-1"></i> -->
                                        {{ $asset->category->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="align-middle">
                                    <small class="text-muted">{{ $asset->serial_number ?? '-' }}</small>
                                </td>
                                <td class="align-middle">
                                    @php
                                        $statusColors = [
                                            'available' => 'success',
                                            'assigned' => 'primary',
                                            'maintenance' => 'warning',
                                            'damaged' => 'danger',
                                            'disposed' => 'secondary'
                                        ];
                                        $statusIcons = [
                                            'available' => 'fa-check-circle',
                                            'assigned' => 'fa-user-check',
                                            'maintenance' => 'fa-tools',
                                            'damaged' => 'fa-exclamation-circle',
                                            'disposed' => 'fa-trash-alt'
                                        ];
                                    @endphp
                                    <span class="text-dark badge-pill px-3 py-2">
                                        <!-- <i class="fas {{ $statusIcons[$asset->status] ?? 'fa-circle' }} mr-1"></i> -->
                                        {{ ucfirst($asset->status) }}
                                    </span>
                                </td>
                                <td class="align-middle">
                                    @php
                                        $conditionColors = [
                                            'new' => 'success',
                                            'good' => 'primary',
                                            'fair' => 'warning',
                                            'poor' => 'danger',
                                            'damaged' => 'danger'
                                        ];
                                    @endphp
                                    <span class="text-dark px-3 py-2">
                                        {{ ucfirst($asset->condition) }}
                                    </span>
                                </td>
                                <td class="align-middle">
                                    <span class="text-dark">
                                        <!-- <i class="fas fa-tag text-muted mr-1"></i> -->
                                        {{ $asset->notes ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="align-middle text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('assets.show', $asset->id) }}" 
                                           class="btn btn-outline-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('assets.edit', $asset->id) }}" 
                                           class="btn btn-outline-warning" title="Edit Asset">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        @if($asset->isAvailable())
                                            <button type="button" class="btn btn-outline-success" 
                                                    data-toggle="modal" data-target="#assignModal{{ $asset->id }}"
                                                    title="Assign to User">
                                                <i class="fas fa-user-plus"></i>
                                            </button>
                                        @endif
                
                                        
                                        <form action="{{ route('assets.destroy', $asset->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" 
                                                    title="Delete Asset" 
                                                    onclick="return confirm('Are you sure you want to delete this asset?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Assign Modal -->
                                    <div class="modal fade" id="assignModal{{ $asset->id }}" tabindex="-1" role="dialog">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content shadow-lg">
                                                <div class="modal-header bg-success text-white">
                                                    <h5 class="modal-title">
                                                        <i class="fas fa-user-plus mr-2"></i>Assign Asset
                                                    </h5>
                                                    <button type="button" class="close text-white" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <form action="{{ route('assets.assign', $asset->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="text-center mb-3">
                                                            <span class="badge badge-info badge-pill px-3 py-2">
                                                                {{ $asset->asset_code }}
                                                            </span>
                                                            <h6 class="mt-2 font-weight-bold">{{ $asset->name }}</h6>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="font-weight-bold">
                                                                Assign To <span class="text-danger">*</span>
                                                            </label>
                                                            <select name="assigned_to" class="form-control select2" required>
                                                                <option value="">Select User</option>
                                                                @foreach($users as $user)
                                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="font-weight-bold">Expected Return Date</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                                                </div>
                                                                <input type="date" name="expected_return_date" class="form-control">
                                                            </div>
                                                            <small class="form-text text-muted">Optional - when the asset is expected to be returned</small>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="font-weight-bold">Notes</label>
                                                            <textarea name="assignment_notes" class="form-control" rows="2" 
                                                                      placeholder="Add any additional information about this assignment..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                            <i class="fas fa-times mr-1"></i>Cancel
                                                        </button>
                                                        <button type="submit" class="btn btn-success">
                                                            <i class="fas fa-check mr-1"></i>Assign Asset
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="fas fa-box-open fa-3x text-muted mb-3 d-block"></i>
                                    <h6 class="text-muted">No assets found</h6>
                                    <p class="text-muted small">Try adjusting your filters or create a new asset</p>
                                    <a href="{{ route('assets.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus-circle mr-1"></i>Add Asset
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <small class="text-muted">
                        Showing {{ $assets->firstItem() ?? 0 }} to {{ $assets->lastItem() ?? 0 }} 
                        of {{ $assets->total() }} assets
                    </small>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end">
                        {{ $assets->appends(request()->all())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Info Box Styling */
    .info-box {
        background: #fff;
        border-radius: 8px;
        padding: 15px;
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
    }
    .info-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
    }
    .info-box-icon {
        border-radius: 50%;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #fff;
        float: left;
        margin-right: 15px;
    }
    .info-box-content {
        margin-left: 65px;
    }
    .info-box-text {
        font-size: 12px;
        text-transform: uppercase;
        font-weight: 600;
        color: #6c757d;
        letter-spacing: 0.5px;
    }
    .info-box-number {
        font-weight: 700 !important;
        color: #2c3e50;
    }

    /* Table Styling */
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        border-top: none;
        padding: 12px 10px;
    }
    .table td {
        padding: 10px;
        vertical-align: middle;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(52, 144, 220, 0.05);
    }

    /* Badge Enhancements */
    .badge {
        font-weight: 500;
    }
    .badge-pill {
        border-radius: 50rem;
    }

    /* Button Group */
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        border-radius: 4px;
    }
    .btn-group-sm .btn:not(:last-child) {
        margin-right: 2px;
    }

    /* Avatar */
    .avatar-sm {
        font-size: 12px;
        font-weight: 600;
    }

    /* Modal Enhancements */
    .modal-content {
        border: none;
        border-radius: 12px;
    }
    .modal-header {
        border-radius: 12px 12px 0 0;
    }

    /* Card Shadows */
    .shadow-sm {
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075) !important;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .info-box {
            margin-bottom: 10px;
        }
        .btn-lg {
            font-size: 0.9rem;
            padding: 0.4rem 0.75rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2 for better dropdowns
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Select a user',
            allowClear: true
        });

        // Auto-submit form when select changes
        $('select[name="category_id"], select[name="status"], select[name="condition"]').on('change', function() {
            $(this).closest('form').submit();
        });

        // Clear search button functionality
        $('.input-group .btn-clear').on('click', function() {
            $(this).closest('.input-group').find('input').val('');
            $(this).closest('form').submit();
        });

        // Confirm return with SweetAlert style
        $(document).on('submit', 'form[action*="assets.return"]', function(e) {
            e.preventDefault();
            if (confirm('Return this asset to inventory?')) {
                this.submit();
            }
        });

        // Confirm delete with SweetAlert style
        $(document).on('submit', 'form[action*="assets.destroy"]', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this asset? This action cannot be undone.')) {
                this.submit();
            }
        });
    });
</script>
@endpush
@endsection