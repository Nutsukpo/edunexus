@extends('layouts.master')

@section('title', 'Grievance Management')

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
                                <i class="fas fa-exclamation-circle text-warning"></i> 
                                Grievance Management
                            </h3>
                            <small class="text-muted d-block mt-1">
                                @if(auth()->user()->role === 'staff')
                                    Submit and track your grievances
                                @elseif(auth()->user()->role === 'admin' || auth()->user()->role === 'hr')
                                    Manage and resolve staff grievances
                                @else
                                    View grievance information
                                @endif
                            </small>
                        </div>
                        <div class="mt-2 mt-md-0">
                            <a href="{{ route('grievance.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add New Grievance
                            </a>
                        </div>
                    </div>
                </div>
                <!-- /.card-header -->
                
                <div class="card-body">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $grievances->total() }}</h3>
                                    <p>Total Grievances</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <a href="{{ route('grievance.index') }}" class="small-box-footer">
                                    View All <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $grievances->whereIn('status', ['draft', 'submitted', 'under_review', 'investigation', 'resolution_proposed'])->count() }}</h3>
                                    <p>Pending Grievances</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <a href="{{ route('grievance.index', ['status' => 'submitted']) }}" class="small-box-footer">
                                    View Pending <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $grievances->whereIn('status', ['resolved', 'closed'])->count() }}</h3>
                                    <p>Resolved Grievances</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <a href="{{ route('grievance.index', ['status' => 'resolved']) }}" class="small-box-footer">
                                    View Resolved <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ $grievances->where('status', 'rejected')->count() }}</h3>
                                    <p>Rejected Grievances</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <a href="{{ route('grievance.index', ['status' => 'rejected']) }}" class="small-box-footer">
                                    View Rejected <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card card-outline card-light">
                               
                                <div class="card-body">
                                    <form action="{{ route('grievance.index') }}" method="GET" id="filterForm">
                                        <div class="row">
                                            <div class="col-md-2 mb-2">
                                                <label class="small text-muted">Status</label>
                                                <select name="status" class="form-control form-control-sm">
                                                    <option value="">All Status</option>
                                                    @foreach($statuses as $status)
                                                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                                            {{ ucwords(str_replace('_', ' ', $status)) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label class="small text-muted">Priority</label>
                                                <select name="priority" class="form-control form-control-sm">
                                                    <option value="">All Priorities</option>
                                                    @foreach($priorities as $priority)
                                                        <option value="{{ $priority }}" {{ request('priority') == $priority ? 'selected' : '' }}>
                                                            {{ ucfirst($priority) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label class="small text-muted">Category</label>
                                                <select name="category_id" class="form-control form-control-sm">
                                                    <option value="">All Categories</option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                                            {{ $category->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="small text-muted">Search</label>
                                                <div class="input-group input-group-sm">
                                                    <input type="text" name="search" class="form-control" 
                                                           placeholder="Search by code, title, or staff..." 
                                                           value="{{ request('search') }}">
                                                    <div class="input-group-append">
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fas fa-search"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2 mb-2 d-flex align-items-end">
                                                <div class="btn-group w-100">
                                                    <button type="button" class="btn btn-sm btn-secondary" id="clearFilters">
                                                        <i class="fas fa-undo"></i> Clear
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-info" id="exportData" title="Export Data">
                                                        <i class="fas fa-file-export"></i>
                                                    </button>
                                                </div>
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
                            <thead class="bg-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Code</th>
                                    <th>Title</th>
                                    <th>Staff</th>
                                    <th>Category</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($grievances as $key => $grievance)
                                <tr>
                                    <td>{{ $grievances->firstItem() + $key }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <i class="fas fa-hashtag"></i> {{ $grievance->grievance_code }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ Str::limit($grievance->title, 50) }}</strong>
                                        <div class="mt-1">
                                            @if($grievance->is_anonymous)
                                                <span class="text-dark badge-pill" title="Anonymous">
                                                    <i class="fas fa-user-secret"></i> Anonymous
                                                </span>
                                            @endif
                                            @if($grievance->is_confidential)
                                                <span class="text-dark badge-pill" title="Confidential">
                                                    <i class="fas fa-lock"></i> Confidential
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($grievance->is_anonymous)
                                            <span class="text-muted">
                                                <i class="fas fa-user-secret"></i> Anonymous
                                            </span>
                                        @else
                                            <div>
                                                <strong>{{ $grievance->staff->full_name ?? 'N/A' }}</strong>
                                                @if($grievance->staff)
                                                    <br>
                                                    <small class="text-muted">{{ $grievance->staff->position ?? '' }}</small>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-dark badge-pill">
                                            {{ $grievance->category->name ?? 'Uncategorized' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-dark badge-pill">
                                            @if($grievance->priority == 'urgent')
                                                <i class="fas fa-exclamation-circle"></i>
                                            @elseif($grievance->priority == 'high')
                                                <i class="fas fa-arrow-up"></i>
                                            @elseif($grievance->priority == 'medium')
                                                <i class="fas fa-minus"></i>
                                            @else
                                                <i class="fas fa-arrow-down"></i>
                                            @endif
                                            {{ ucfirst($grievance->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-dark badge-pill">
                                            @if($grievance->status == 'draft')
                                                <i class="fas fa-pencil-alt"></i>
                                            @elseif($grievance->status == 'submitted')
                                                <i class="fas fa-paper-plane"></i>
                                            @elseif($grievance->status == 'under_review')
                                                <i class="fas fa-search"></i>
                                            @elseif($grievance->status == 'investigation')
                                                <i class="fas fa-search-plus"></i>
                                            @elseif($grievance->status == 'resolution_proposed')
                                                <i class="fas fa-handshake"></i>
                                            @elseif($grievance->status == 'resolved')
                                                <i class="fas fa-check-circle"></i>
                                            @elseif($grievance->status == 'closed')
                                                <i class="fas fa-times-circle"></i>
                                            @elseif($grievance->status == 'rejected')
                                                <i class="fas fa-ban"></i>
                                            @elseif($grievance->status == 'appealed')
                                                <i class="fas fa-gavel"></i>
                                            @endif
                                            {{ $grievance->status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <div title="{{ $grievance->created_at->format('l, d F Y h:i A') }}">
                                            <i class="fas fa-calendar-alt"></i> 
                                            {{ $grievance->created_at->format('d/m/Y') }}
                                            <br>
                                            <small class="text-muted">                                           
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('grievance.show', $grievance->id) }}" 
                                               class="btn btn-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($grievance->canEdit())
                                                <a href="{{ route('grievance.edit', $grievance->id) }}" 
                                                   class="btn btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            @if($grievance->canDelete())
                                                <button type="button" class="btn btn-danger" 
                                                        onclick="confirmDelete({{ $grievance->id }})" 
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                            @if($grievance->canAppeal())
                                                <button type="button" class="btn btn-info" 
                                                        onclick="appealGrievance({{ $grievance->id }})" 
                                                        title="Appeal">
                                                    <i class="fas fa-gavel"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="empty-state">
                                            <div class="empty-state-icon">
                                                <i class="fas fa-inbox fa-4x text-muted"></i>
                                            </div>
                                            <h5 class="mt-3">No Grievances Found</h5>
                                            <p class="text-muted">There are no grievances matching your criteria.</p>
                                            @if(auth()->user()->role === 'staff')
                                                <a href="{{ route('grievance.create') }}" class="btn btn-primary">
                                                    <i class="fas fa-plus"></i> Submit Your First Grievance
                                                </a>
                                            @endif
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
                                    Showing {{ $grievances->firstItem() ?? 0 }} to {{ $grievances->lastItem() ?? 0 }} 
                                    of {{ $grievances->total() }} entries
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if($grievances->hasPages())
                                <div class="float-right">
                                    {{ $grievances->appends(request()->query())->links() }}
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

<!-- Delete Form -->
<form id="delete-form" action="" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Appeal Form -->
<form id="appeal-form" action="" method="POST" style="display: none;">
    @csrf
</form>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Confirm Delete
        window.confirmDelete = function(id) {
            if (confirm('Are you sure you want to delete this grievance? This action cannot be undone.')) {
                document.getElementById('delete-form').action = '{{ url("grievance") }}/' + id;
                document.getElementById('delete-form').submit();
            }
        };

        // Appeal Grievance
        window.appealGrievance = function(id) {
            if (confirm('Are you sure you want to appeal this grievance? This will reopen it for review.')) {
                document.getElementById('appeal-form').action = '{{ url("grievance") }}/' + id + '/appeal';
                document.getElementById('appeal-form').submit();
            }
        };

        // Clear Filters
        $('#clearFilters').click(function() {
            window.location.href = '{{ route("grievance.index") }}';
        });

        // Export Data
        $('#exportData').click(function() {
            if (typeof toastr !== 'undefined') {
                toastr.info('Export functionality coming soon!');
            } else {
                alert('Export functionality coming soon!');
            }
        });

        // Auto-submit filter on change
        $('select[name="status"], select[name="priority"], select[name="category_id"]').on('change', function() {
            $('#filterForm').submit();
        });

        // Auto-hide success messages after 5 seconds
        setTimeout(function() {
            $('.alert-success').fadeOut('slow');
        }, 5000);

        // Add tooltips
        $('[title]').tooltip();
    });
</script>
@endpush

@push('styles')
<style>
    /* Small Box Styles */
    .small-box {
        border-radius: 5px;
        position: relative;
        display: block;
        margin-bottom: 20px;
        box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    
    .small-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
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
        color: rgba(255,255,255,0.8);
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
        background-color: rgba(0,0,0,0.1);
        color: rgba(255,255,255,0.8);
        text-align: center;
        text-decoration: none;
        font-size: 12px;
        position: relative;
        z-index: 10;
        transition: all 0.3s ease;
    }
    
    .small-box .small-box-footer:hover {
        background-color: rgba(0,0,0,0.15);
        color: #fff;
    }
    
    /* Card Styles */
    .card-outline {
        border: 1px solid #e9ecef;
    }
    
    .card-outline .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }
    
    /* Empty State */
    .empty-state {
        padding: 40px 20px;
    }
    
    .empty-state-icon {
        opacity: 0.5;
    }
    
    /* Table Styles */
    .table th {
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background-color: #f8f9fa;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
        cursor: pointer;
    }
    
    /* Badge Styles */
    .badge-pill {
        padding: 5px 12px;
        font-size: 0.75rem;
    }
    
    .badge-pill i {
        margin-right: 3px;
    }
    
    /* Responsive Design */
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
    
    /* Custom Scrollbar */
    .table-responsive::-webkit-scrollbar {
        height: 6px;
    }
    
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>
@endpush