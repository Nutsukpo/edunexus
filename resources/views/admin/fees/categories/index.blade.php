@extends('layouts.master')

@section('title', 'Fee Categories')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Main Card -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h3 class="card-title mb-0">
                                <i class="fas fa-tags text-primary"></i> Fee Categories
                            </h3>
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-info-circle"></i> 
                                Manage fee categories for your institution
                            </small>
                        </div>
                        <div class="mt-2 mt-md-0">
                            <a href="{{ route('fee-categories.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Category
                            </a>
                        </div>
                    </div>
                </div>
                <!-- /.card-header -->
                     <!-- Statistics Cards -->
            <div class="row mb-0">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $categories->total() }}</h3>
                            <p>Total Categories</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <a href="{{ route('fee-categories.index') }}" class="small-box-footer">
                            View All <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $categories->where('is_active', true)->count() }}</h3>
                            <p>Active Categories</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <a href="{{ route('fee-categories.index', ['status' => 'active']) }}" class="small-box-footer">
                            View Active <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3>{{ $categories->where('is_active', false)->count() }}</h3>
                            <p>Inactive Categories</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <a href="{{ route('fee-categories.index', ['status' => 'inactive']) }}" class="small-box-footer">
                            View Inactive <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $categories->sum(function($c) { return $c->feeItems()->count(); }) }}</h3>
                            <p>Total Fee Items</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <a href="#" class="small-box-footer">
                            View Items <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>
                
                <div class="card-body">
                    <!-- Alerts -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <!-- Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="card card-outline card-light">
                                <div class="card-body">
                                    <form action="{{ route('fee-categories.index') }}" method="GET" id="filterForm">
                                        <div class="row">
                                            <div class="col-md-3 mb-2">
                                                <label class="small text-muted">Status</label>
                                                <select name="status" class="form-control form-control-sm">
                                                    <option value="">All Status</option>
                                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="small text-muted">Search</label>
                                                <div class="input-group input-group-sm">
                                                    <input type="text" name="search" class="form-control" 
                                                           placeholder="Search by name, code, or description..." 
                                                           value="{{ request('search') }}">
                                                    <div class="input-group-append">
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fas fa-search"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-sm btn-secondary w-100" id="clearFilters">
                                                    <i class="fas fa-undo"></i> Clear
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Description</th>
                                    <th>Sort Order</th>
                                    <th>Status</th>
                                    <th>Used In</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                <tr>
                                    <td>{{ $categories->firstItem() + $loop->index }}</td>
                                    <td>
                                        <strong>{{ $category->name }}</strong>
                                        @if($category->is_default)
                                            <span class="badge badge-primary ml-1" title="Default Category">
                                                <i class="fas fa-star"></i> Default
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($category->code)
                                            <span class="badge badge-info">{{ $category->code }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($category->description)
                                            {{ Str::limit($category->description, 50) }}
                                        @else
                                            <span class="text-muted">No description</span>
                                        @endif
                                    </td>
                                    <td>{{ $category->sort_order ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-pill {{ $category->is_active ? 'badge-success' : 'badge-danger' }}">
                                            <i class="fas {{ $category->is_active ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $itemCount = $category->feeItems()->count();
                                        @endphp
                                        @if($itemCount > 0)
                                            <span class="badge badge-primary">
                                                <i class="fas fa-file-invoice"></i> {{ $itemCount }} items
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">0 items</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('fee-categories.show', $category->id) }}" 
                                               class="btn btn-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('fee-categories.edit', $category->id) }}" 
                                               class="btn btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn {{ $category->is_active ? 'btn-secondary' : 'btn-success' }}"
                                                    onclick="toggleStatus({{ $category->id }})"
                                                    title="{{ $category->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="fas {{ $category->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger" 
                                                    onclick="deleteCategory({{ $category->id }}, '{{ $category->name }}')"
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="empty-state">
                                            <div class="empty-state-icon">
                                                <i class="fas fa-tags fa-4x text-muted"></i>
                                            </div>
                                            <h5 class="mt-3">No Fee Categories Found</h5>
                                            <p class="text-muted">Get started by creating your first fee category.</p>
                                            <a href="{{ route('fee-categories.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Add Category
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination and Summary -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="text-muted">
                                <small>
                                    Showing {{ $categories->firstItem() ?? 0 }} to {{ $categories->lastItem() ?? 0 }} 
                                    of {{ $categories->total() }} entries
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if($categories->hasPages())
                                <div class="float-right">
                                    {{ $categories->appends(request()->query())->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>

<!-- Delete Form (Hidden) -->
<form id="deleteForm" action="" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Toggle Status Form (Hidden) -->
<form id="toggleForm" action="" method="POST" style="display: none;">
    @csrf
    @method('PATCH')
</form>

@push('scripts')
<script>
$(document).ready(function() {
    // Clear Filters
    $('#clearFilters').click(function() {
        window.location.href = '{{ route("fee-categories.index") }}';
    });

    // Auto-submit filter on change
    $('select[name="status"]').on('change', function() {
        $('#filterForm').submit();
    });

    // Auto-hide success messages after 5 seconds
    setTimeout(function() {
        $('.alert-success').fadeOut('slow');
    }, 5000);

    // Add enter key support for search
    $('input[name="search"]').on('keypress', function(e) {
        if (e.which === 13) {
            $('#filterForm').submit();
        }
    });
});

function toggleStatus(id) {
    if (confirm('Are you sure you want to toggle the status of this category?')) {
        const form = $('#toggleForm');
        form.attr('action', `/fee-categories/${id}/toggle-status`);
        form.submit();
    }
}

function deleteCategory(id, name) {
    if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
        const form = $('#deleteForm');
        form.attr('action', `/fee-categories/${id}`);
        form.submit();
    }
}
</script>
@endpush

@push('styles')
<style>
    /* ================= SMALL BOX STYLES ================= */
    .small-box {
        border-radius: 8px;
        position: relative;
        display: block;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }
    
    .small-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.2);
    }
    
    .small-box .inner {
        padding: 10px 20px;
    }
    
    .small-box .inner h3 {
        font-size: 38px;
        font-weight: bold;
        margin: 0 0 10px 0;
        white-space: nowrap;
        padding: 0;
        color: #fff;
    }
    
    .small-box .inner p {
        font-size: 14px;
        margin: 0;
        color: rgba(255,255,255,0.9);
    }
    
    .small-box .icon {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 70px;
        color: rgba(255,255,255,0.15);
        transition: all 0.3s ease;
    }
    
    .small-box:hover .icon {
        font-size: 75px;
        color: rgba(255,255,255,0.25);
    }
    
    .small-box .small-box-footer {
        display: block;
        padding: 10px 20px;
        background-color: rgba(0,0,0,0.08);
        color: rgba(255,255,255,0.9);
        text-align: center;
        text-decoration: none;
        font-size: 12px;
        position: relative;
        z-index: 10;
        transition: all 0.3s ease;
        border-radius: 0 0 8px 8px;
    }
    
    .small-box .small-box-footer:hover {
        background-color: rgba(0,0,0,0.12);
        color: #fff;
        text-decoration: none;
    }
    
    /* ================= BLUE LIGHT THEME COLORS ================= */
    .bg-info {
        background: linear-gradient(135deg, #3498db, #2980b9) !important;
    }
    
    .bg-primary {
        background: linear-gradient(135deg, #5b9bd5, #2b6da1) !important;
    }
    
    .bg-secondary {
        background: linear-gradient(135deg, #95a5a6, #7f8c8d) !important;
    }
    
    .bg-success {
        background: linear-gradient(135deg, #2ecc71, #27ae60) !important;
    }
    
    .bg-warning {
        background: linear-gradient(135deg, #f39c12, #e67e22) !important;
    }
    
    .bg-danger {
        background: linear-gradient(135deg, #e74c3c, #c0392b) !important;
    }
    
    /* ================= CARD STYLES ================= */
    .card {
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e9ecef;
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        border-radius: 8px 8px 0 0;
    }
    
    .card-outline {
        border: 1px solid #e9ecef;
    }
    
    .card-outline .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }
    
    /* ================= TABLE STYLES ================= */
    .table th {
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: #34495e;
        background-color: #f8f9fa;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(52, 152, 219, 0.05);
        cursor: pointer;
    }
    
    .table-bordered {
        border: 1px solid #dee2e6;
    }
    
    /* ================= BADGE STYLES ================= */
    .badge-pill {
        padding: 5px 12px;
        font-size: 0.75rem;
        border-radius: 20px;
    }
    
    .badge-pill i {
        margin-right: 3px;
    }
    
    .badge-success {
        background: linear-gradient(135deg, #2ecc71, #27ae60);
        color: white;
    }
    
    .badge-primary {
        background: linear-gradient(135deg, #3498db, #2980b9);
        color: white;
    }
    
    .badge-secondary {
        background: linear-gradient(135deg, #95a5a6, #7f8c8d);
        color: white;
    }
    
    .badge-danger {
        background: linear-gradient(135deg, #e74c3c, #c0392b);
        color: white;
    }
    
    .badge-info {
        background: linear-gradient(135deg, #3498db, #2980b9);
        color: white;
    }
    
    /* ================= BUTTON STYLES ================= */
    .btn-primary {
        background: linear-gradient(135deg, #3498db, #2980b9);
        border: none;
        color: white;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #2980b9, #1a6e9e);
        color: white;
    }
    
    .btn-info {
        background: #3498db;
        border: none;
        color: white;
    }
    
    .btn-info:hover {
        background: #2980b9;
        color: white;
    }
    
    .btn-warning {
        background: #f39c12;
        border: none;
        color: white;
    }
    
    .btn-warning:hover {
        background: #e67e22;
        color: white;
    }
    
    .btn-success {
        background: #2ecc71;
        border: none;
        color: white;
    }
    
    .btn-success:hover {
        background: #27ae60;
        color: white;
    }
    
    .btn-danger {
        background: #e74c3c;
        border: none;
        color: white;
    }
    
    .btn-danger:hover {
        background: #c0392b;
        color: white;
    }
    
    .btn-secondary {
        background: #95a5a6;
        border: none;
        color: white;
    }
    
    .btn-secondary:hover {
        background: #7f8c8d;
        color: white;
    }
    
    /* ================= EMPTY STATE ================= */
    .empty-state {
        padding: 40px 20px;
    }
    
    .empty-state-icon {
        opacity: 0.5;
    }
    
    /* ================= CUSTOM CHECKBOX ================= */
    .custom-control-label {
        cursor: pointer;
    }
    
    /* ================= PAGINATION ================= */
    .pagination .page-item.active .page-link {
        background-color: #3498db;
        border-color: #3498db;
        color: white;
    }
    
    .pagination .page-link {
        color: #3498db;
    }
    
    .pagination .page-link:hover {
        color: #2980b9;
    }
    
    /* ================= RESPONSIVE DESIGN ================= */
    @media (max-width: 768px) {
        .small-box .inner h3 {
            font-size: 28px;
        }
        
        .small-box .icon {
            font-size: 50px;
        }
        
        .table-responsive {
            font-size: 0.85rem;
        }
        
        .btn-group-sm .btn {
            padding: 0.15rem 0.4rem;
        }
    }
</style>
@endpush
@endsection