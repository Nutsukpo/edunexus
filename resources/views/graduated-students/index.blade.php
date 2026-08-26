@extends('layouts.master')

@section('title', 'Graduated Students')

@section('content')

<div class="container-fluid px-4">

    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-primary mt-3">
                <i class="fas fa-graduation-cap me-2"></i>
                Graduated Students
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Graduated Students</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <a href="{{ route('graduates.export', request()->query()) }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> Export
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted text-uppercase small fw-bold">Total Graduates</span>
                            <h3 class="mb-0 fw-bold">{{ $graduates->total() }}</h3>
                        </div>
                        <div class="bg-white p-3 rounded-circle">
                            <i class="fas fa-users text-dark fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted text-uppercase small fw-bold">This Year</span>
                            <h3 class="mb-0 fw-bold">{{ $graduates->where('academicYear.name', date('Y').'/'.(date('Y')+1))->count() }}</h3>
                        </div>
                        <div class="bg-white  p-3 rounded-circle">
                            <i class="fas fa-calendar-check text-success fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted text-uppercase small fw-bold">Classes</span>
                            <h3 class="mb-0 fw-bold">{{ $classes->count() }}</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-school text-info fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted text-uppercase small fw-bold">Academic Years</span>
                            <h3 class="mb-0 fw-bold">{{ $academicYears->count() }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-clock text-warning fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card shadow-sm border-0">

        <div class="card-header bg-white border-0 py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-list me-2 text-primary"></i>
                        Graduate Records
                        <span class="badge bg-dark ms-2">{{ $graduates->total() }}</span>
                    </h5>
                </div>
                <div class="col-md-6 text-md-end mt-2 mt-md-0">
                    <span class="badge bg-success">
                        <i class="fas fa-check-circle me-1"></i>
                        {{ $graduates->total() }} Graduated
                    </span>
                </div>
            </div>
        </div>

        <div class="card-body">

            <!-- Search and Filter Section -->
            <div class="row g-3 mb-4">
                <div class="col-md-8">
                    <form method="GET" action="{{ route('graduates.index') }}" class="row g-2">
                        <!-- Search -->
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" 
                                       name="search" 
                                       class="form-control border-start-0 ps-0" 
                                       placeholder="Search by name or ID..." 
                                       value="{{ request('search') }}">
                            </div>
                        </div>

                        <!-- Filter by Class -->
                        <div class="col-md-3">
                            <select name="class_id" class="form-select">
                                <option value="">All Classes</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" 
                                        {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter by Academic Year -->
                        <div class="col-md-3">
                            <select name="academic_year_id" class="form-select">
                                <option value="">All Years</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" 
                                        {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                        {{ $year->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="col-md-4 text-md-end">
                    <a href="{{ route('graduates.index') }}" class="btn btn-outline-secondary btn-sm me-2">
                        <i class="fas fa-undo me-1"></i> Reset
                    </a>
                    <span class="text-muted small">
                        <i class="fas fa-eye me-1"></i>
                        {{ $graduates->firstItem() ?? 0 }} - {{ $graduates->lastItem() ?? 0 }} 
                        of {{ $graduates->total() }}
                    </span>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">

                    <thead class="bg-light">
                        <tr>
                            <th style="width: 50px;" class="text-center">#</th>
                            <th>
                                <a href="{{ route('graduates.index', array_merge(request()->query(), ['sort' => 'student_id', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                                   class="text-decoration-none text-dark d-flex align-items-center gap-1">
                                    Student ID
                                    @if(request('sort') == 'student_id')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-primary"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('graduates.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                                   class="text-decoration-none text-dark d-flex align-items-center gap-1">
                                    Name
                                    @if(request('sort') == 'name')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-primary"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('graduates.index', array_merge(request()->query(), ['sort' => 'class', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                                   class="text-decoration-none text-dark d-flex align-items-center gap-1">
                                    Class
                                    @if(request('sort') == 'class')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-primary"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('graduates.index', array_merge(request()->query(), ['sort' => 'academic_year', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                                   class="text-decoration-none text-dark d-flex align-items-center gap-1">
                                    Academic Year
                                    @if(request('sort') == 'academic_year')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-primary"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 140px;">
                                <a href="{{ route('graduates.index', array_merge(request()->query(), ['sort' => 'graduation_date', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                                   class="text-decoration-none text-dark d-flex align-items-center gap-1">
                                    Grad. Date
                                    @if(request('sort') == 'graduation_date')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-primary"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th style="width: 120px;" class="text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($graduates as $graduate)
                            <tr class="border-bottom">
                                <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="fw-bold text-dark">{{ $graduate->student->student_id ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <div class="fw-semibold">{{ $graduate->student->full_name ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ $graduate->student->email ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-white  text-dark px-3 py-2">
                                        {{ $graduate->studentClass->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-white  text-dark ">
                                        {{ $graduate->academicYear->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-success text-white">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Graduated
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        
                                        <span>{{ $graduate->updated_at ? $graduate->updated_at->format('d M Y') : 'N/A' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center">
                                        <a href="{{ route('graduates.show', $graduate->id) }}" 
                                           class="btn btn-sm btn-white text-dark" 
                                           style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;"
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('graduates.certificate', $graduate->id) }}" 
                                           class="btn btn-sm btn-outline-success rounded-circle" 
                                           style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;"
                                           title="Download Certificate"
                                           target="_blank">
                                            <i class="fas fa-certificate"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="fas fa-inbox fa-4x text-muted mb-3 d-block"></i>
                                        <h5 class="text-muted">No Graduated Students Found</h5>
                                        <p class="text-muted mb-0">Try adjusting your search or filter criteria</p>
                                        <a href="{{ route('graduates.index') }}" class="btn btn-primary btn-sm mt-3">
                                            <i class="fas fa-undo me-1"></i> Reset Filters
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mt-4 pt-3 border-top">
                <div class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    Showing {{ $graduates->firstItem() ?? 0 }} to {{ $graduates->lastItem() ?? 0 }} 
                    of {{ $graduates->total() }} graduates
                </div>
                <div>
                    {{ $graduates->appends(request()->query())->links() }}
                </div>
            </div>

        </div>

    </div>

</div>

@endsection

@push('styles')
<style>
    /* Custom Gradient Backgrounds */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    /* Card Hover Effects */
    .card.shadow-sm {
        transition: all 0.3s ease;
    }
    .card.shadow-sm:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    
    /* Stats Cards */
    .card .bg-opacity-10 {
        opacity: 0.1;
    }
    .card .rounded-circle {
        transition: all 0.3s ease;
    }
    .card:hover .rounded-circle {
        transform: scale(1.1);
    }
    
    /* Table Styles */
    .table-hover tbody tr {
        transition: all 0.2s ease;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.03);
        transform: scale(1.001);
    }
    
    /* Badge Styles */
    .badge.bg-opacity-10 {
        backdrop-filter: blur(4px);
        font-weight: 500;
    }
    .badge.bg-info.bg-opacity-10 {
        background-color: rgba(13, 202, 240, 0.1) !important;
    }
    .badge.bg-success.bg-opacity-10 {
        background-color: rgba(25, 135, 84, 0.1) !important;
    }
    .badge.bg-secondary.bg-opacity-10 {
        background-color: rgba(108, 117, 125, 0.1) !important;
    }
    
    /* Action Buttons */
    .btn-outline-primary.rounded-circle,
    .btn-outline-success.rounded-circle {
        transition: all 0.3s ease;
    }
    .btn-outline-primary.rounded-circle:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: transparent;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
    }
    .btn-outline-success.rounded-circle:hover {
        background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
        border-color: transparent;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(86, 171, 47, 0.3);
    }
    
    /* Sort Links */
    .table th a {
        transition: color 0.2s ease;
        font-weight: 600;
    }
    .table th a:hover {
        color: #0d6efd !important;
    }
    
    /* Empty State */
    .fa-inbox {
        opacity: 0.3;
    }
    
    /* Pagination */
    .pagination {
        margin-bottom: 0;
    }
    .page-link {
        border: none;
        color: #6c757d;
        padding: 0.5rem 0.75rem;
        border-radius: 6px !important;
        margin: 0 2px;
    }
    .page-link:hover {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }
    .page-item.active .page-link {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: transparent;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .container-fluid {
            padding: 0 10px;
        }
        .table th a {
            font-size: 12px;
        }
        .table td {
            font-size: 13px;
        }
        .stats-cards .col-6 {
            margin-bottom: 10px;
        }
    }
    
    /* Custom Scrollbar */
    .table-responsive::-webkit-scrollbar {
        height: 6px;
        width: 6px;
    }
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    .table-responsive::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
    }
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>
@endpush