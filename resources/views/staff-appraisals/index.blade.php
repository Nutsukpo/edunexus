@extends('layouts.master')

@section('title', 'Staff Appraisals')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-file-alt text-primary mr-2"></i>Staff Appraisals
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('staff-appraisals.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-upload mr-1"></i> Upload Appraisal
                        </a>
                    </div>
                </div>
                <div class="card-body">
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

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-2 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $stats['total'] }}</h3>
                                    <p>Total</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $stats['submitted'] }}</h3>
                                    <p>Submitted</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-paper-plane"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-6">
                            <div class="small-box bg-secondary">
                                <div class="inner">
                                    <h3>{{ $stats['reviewed'] }}</h3>
                                    <p>Reviewed</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $stats['approved'] }}</h3>
                                    <p>Approved</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ $stats['rejected'] }}</h3>
                                    <p>Rejected</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $stats['submitted'] }}</h3>
                                    <p>History</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-paper-plane"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="card card-outline card-primary">
                                <div class="card-body p-3">
                                    <form method="GET" action="{{ route('staff-appraisals.index') }}" class="row g-3">
                                        <div class="col-md-3">
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                </div>
                                                <input type="text" name="search" class="form-control" 
                                                       placeholder="Search by title, file..." value="{{ request('search') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <select name="staff_id" class="form-control form-control-sm">
                                                <option value="">All Staff</option>
                                                @foreach($staffMembers as $member)
                                                    <option value="{{ $member->id }}" {{ request('staff_id') == $member->id ? 'selected' : '' }}>
                                                        {{ $member->first_name ?? '' }} {{ $member->last_name ?? '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <select name="academic_year_id" class="form-control form-control-sm">
                                                <option value="">All Years</option>
                                                @foreach($academicYears as $year)
                                                    <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                                        {{ $year->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <select name="term_id" class="form-control form-control-sm">
                                                <option value="">All Terms</option>
                                                @foreach($terms as $term)
                                                    <option value="{{ $term->id }}" {{ request('term_id') == $term->id ? 'selected' : '' }}>
                                                        {{ $term->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <select name="status" class="form-control form-control-sm">
                                                <option value="">All Status</option>
                                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                                <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                                <option value="reviewed" {{ request('status') == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                            </select>
                                        </div>
                                        <div class="col-md-1">
                                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                                <i class="fas fa-filter"></i> Filter
                                            </button>
                                        </div>
                                        <!-- <div class="col-md-1">
                                            <a href="{{ route('staff-appraisals.index') }}" class="btn btn-secondary btn-sm w-100">
                                                <i class="fas fa-undo"></i> Reset
                                            </a>
                                        </div> -->
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th width="15%">Title</th>
                                    <th width="15%">Staff</th>
                                    <th width="12%">Academic Year</th>
                                    <th width="10%">Term</th>
                                    <th width="20%">File Name</th>
                                    <th width="10%">Status</th>
                                    <th width="10%">Submitted</th>
                                    <th width="18%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($appraisals as $appraisal)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="text-dark">{{ $appraisal->title ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                
                                                <div>
                                                    <div>{{ $appraisal->staff->first_name ?? '' }} {{ $appraisal->staff->last_name ?? '' }}</div>
                                                    
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-dark">{{ $appraisal->academicYear->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-dark">{{ $appraisal->term->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <div class="file-info">
                                                <i class="fas {{ $appraisal->file_icon }} mr-2"></i>
                                                <a href="{{ route('staff-appraisals.download', $appraisal->id) }}" 
                                                   target="_blank" class="text-primary">
                                                    {{ Str::limit($appraisal->file_name, 25) }}
                                                </a>
                                                <!-- @if($appraisal->title)
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-tag"></i> {{ Str::limit($appraisal->title, 20) }}
                                                    </small>
                                                @endif -->
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-dark px-3 py-2">
                                                {{ ucfirst($appraisal->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($appraisal->submission_date)
                                                <i class="fas fa-calendar-alt text-muted mr-1"></i>
                                                {{ $appraisal->submission_date->format('d/m/Y') }}
                                                <br>
                                                <!-- <small class="text-muted">{{ $appraisal->submission_date->format('h:i A') }}</small> -->
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('staff-appraisals.show', $appraisal->id) }}" 
                                                   class="btn btn-sm btn-info" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                
                                                    @if($appraisal->status == 'draft')
                                                        <a href="{{ route('staff-appraisals.edit', $appraisal->id) }}" 
                                                           class="btn btn-sm btn-warning" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                    
                                                    <a href="{{ route('staff-appraisals.download', $appraisal->id) }}" 
                                                       class="btn btn-sm btn-success" title="Download File" target="_blank">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    
                                                    @if($appraisal->status == 'draft')
                                                        <form action="{{ route('staff-appraisals.toggle-status', $appraisal->id) }}" 
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-primary" 
                                                                    title="Submit for Review" 
                                                                    onclick="return confirm('Submit this appraisal for review?')">
                                                                <i class="fas fa-paper-plane"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    
                                                    @if(auth()->user()->hasRole('admin') && $appraisal->status == 'submitted')
                                                        <button type="button" class="btn btn-sm btn-secondary" 
                                                                data-toggle="modal" data-target="#reviewModal{{ $appraisal->id }}"
                                                                title="Review Appraisal">
                                                            <i class="fas fa-clipboard-check"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    @if($appraisal->status == 'draft' || $appraisal->status == 'submitted')
                                                        <form action="{{ route('staff-appraisals.destroy', $appraisal->id) }}" 
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                                    title="Delete" 
                                                                    onclick="return confirm('Are you sure you want to delete this appraisal?')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                
                                            </div>

                                            <!-- Review Modal -->
                                            @if(auth()->user()->hasRole('admin'))
                                            <div class="modal fade" id="reviewModal{{ $appraisal->id }}" tabindex="-1" role="dialog">
                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-secondary text-white">
                                                            <h5 class="modal-title">
                                                                <i class="fas fa-clipboard-check"></i> Review Appraisal
                                                            </h5>
                                                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <form action="{{ route('staff-appraisals.review', $appraisal->id) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-body">
                                                                <div class="form-group">
                                                                    <label>Review Status <span class="text-danger">*</span></label>
                                                                    <select name="status" class="form-control" required>
                                                                        <option value="">Select Status</option>
                                                                        <option value="reviewed">Reviewed</option>
                                                                        <option value="approved">Approved</option>
                                                                        <option value="rejected">Rejected</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Comments</label>
                                                                    <textarea name="reviewer_comments" class="form-control" rows="3" 
                                                                              placeholder="Enter your review comments..."></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-primary">
                                                                    <i class="fas fa-save"></i> Submit Review
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="empty-state">
                                                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">No Appraisals Found</h5>
                                                <p class="text-muted">Start by uploading your first appraisal document.</p>
                                                <a href="{{ route('staff-appraisals.create') }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-upload"></i> Upload Appraisal
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="text-muted">
                                Showing {{ $appraisals->firstItem() ?? 0 }} to {{ $appraisals->lastItem() ?? 0 }} of {{ $appraisals->total() }} entries
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="float-right">
                                {{ $appraisals->appends(request()->all())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .small-box {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .small-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }
    
    .small-box .icon {
        font-size: 3rem;
        opacity: 0.3;
        position: absolute;
        right: 15px;
        top: 15px;
        transition: all 0.3s ease;
    }
    
    .small-box:hover .icon {
        opacity: 0.5;
        transform: scale(1.1);
    }
    
    .card-outline {
        border: 1px solid #e9ecef;
    }
    
    .card-outline.card-primary {
        border-color: #007bff;
    }
    
    .avatar-sm .badge {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 600;
    }
    
    .table th {
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #dee2e6;
    }
    
    .table tbody tr {
        transition: background-color 0.2s ease;
    }
    
    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    .badge {
        font-weight: 500;
    }
    
    .badge i {
        font-size: 8px;
        vertical-align: middle;
    }
    
    .empty-state {
        padding: 2rem;
    }
    
    .btn-group .btn {
        border-radius: 4px;
        margin: 0 1px;
        padding: 0.2rem 0.5rem;
    }
    
    .file-info a {
        font-weight: 500;
    }
    
    .file-info a:hover {
        text-decoration: underline;
    }
    
    /* Card header styling */
    .card-header {
        background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
        border-bottom: 1px solid #e9ecef;
        padding: 1rem 1.25rem;
    }
    
    .card-header .card-title {
        font-weight: 600;
        color: #2c3e50;
    }
    
    .card-header .card-title i {
        color: #007bff;
    }
    
    /* Modal styling */
    .modal-header.bg-secondary {
        background: linear-gradient(135deg, #6c757d, #495057) !important;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .btn-group .btn {
            padding: 0.15rem 0.4rem;
            font-size: 0.7rem;
        }
        
        .small-box h3 {
            font-size: 1.5rem;
        }
        
        .small-box p {
            font-size: 0.8rem;
        }
        
        .card-header {
            flex-direction: column;
            align-items: flex-start !important;
        }
        
        .card-header .card-tools {
            margin-top: 0.5rem;
            width: 100%;
        }
        
        .card-header .card-tools .btn {
            width: 100%;
        }
    }
    
    @media (max-width: 576px) {
        .table-responsive {
            font-size: 0.8rem;
        }
        
        .badge {
            font-size: 0.65rem;
            padding: 0.2rem 0.5rem;
        }
        
        .btn-group .btn {
            padding: 0.1rem 0.3rem;
            font-size: 0.65rem;
        }
    }
</style>
@endpush