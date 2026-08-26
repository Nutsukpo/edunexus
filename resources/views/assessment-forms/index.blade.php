@extends('layouts.master')

@section('title', 'Assessment Forms')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
                <div>
                    <h4 class="mb-0 text-dark font-weight-bold">
                        <i class="fas fa-file-alt text-primary mr-2"></i>Assessment Forms
                    </h4>
                    <small class="text-muted">Manage and track all assessment forms</small>
                </div>
                <div>
                    <a href="{{ route('assessment-forms.create') }}" class="btn btn-primary">
                        <i class="fas fa-upload mr-2"></i> New Form
                    </a>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">


                    <!-- Filters -->
                    <div class="card bg-light p-3 mb-4">
                        <form method="GET" action="{{ route('assessment-forms.index') }}" id="filterForm">
                            <div class="row align-items-end">
                                <div class="col-md-2 col-sm-6 mb-2">
                                    <label class="form-label small text-muted">Search</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                        <input type="text" name="search" class="form-control" 
                                               placeholder="Search forms..." value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-md-2 col-sm-6 mb-2">
                                    <label class="form-label small text-muted">Teacher</label>
                                    <select name="staff_id" class="form-control form-control-sm select2">
                                        <option value="">All Teachers</option>
                                        @foreach($staff as $s)
                                            <option value="{{ $s->id }}" {{ request('staff_id') == $s->id ? 'selected' : '' }}>
                                                {{ $s->full_name ?? $s->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 col-sm-6 mb-2">
                                    <label class="form-label small text-muted">Class</label>
                                    <select name="student_class_id" class="form-control form-control-sm select2">
                                        <option value="">All Classes</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ request('student_class_id') == $class->id ? 'selected' : '' }}>
                                                {{ $class->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 col-sm-6 mb-2">
                                    <label class="form-label small text-muted">Type</label>
                                    <select name="assessment_type" class="form-control form-control-sm">
                                        <option value="">All Types</option>
                                        <option value="quiz" {{ request('assessment_type') == 'quiz' ? 'selected' : '' }}>📝 Quiz</option>
                                        <option value="test" {{ request('assessment_type') == 'test' ? 'selected' : '' }}>📋 Test</option>
                                        <option value="exam" {{ request('assessment_type') == 'exam' ? 'selected' : '' }}>📊 Exam</option>
                                        <option value="assignment" {{ request('assessment_type') == 'assignment' ? 'selected' : '' }}>📄 Assignment</option>
                                        <option value="project" {{ request('assessment_type') == 'project' ? 'selected' : '' }}>📁 Project</option>
                                    </select>
                                </div>
                                <div class="col-md-2 col-sm-6 mb-2">
                                    <label class="form-label small text-muted">Status</label>
                                    <select name="status" class="form-control form-control-sm">
                                        <option value="">All Status</option>
                                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>📄 Draft</option>
                                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>✅ Published</option>
                                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>📦 Archived</option>
                                    </select>
                                </div>
                                <div class="col-md-2 col-sm-6 mb-2">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-filter mr-2"></i>Apply Filters
                                    </button>
                                    <a href="{{ route('assessment-forms.index') }}" class="btn btn-link btn-sm btn-block text-muted">
                                        <i class="fas fa-undo mr-1"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="thead-light">
                                <tr>
                                    <th width="5%">#</th>                                                                  
                                    <th width="12%">Class</th>
                                    <th width="15%">Teacher</th>
                                    <th width="20%">File Details</th>
                                    <th width="15%">Title</th> 
                                    <th width="12%">Subject</th>
                                    <th width="10%">Type</th>
                                    <th width="10%">Date</th>
                                    <th width="8%">Downloads</th>
                                    <!-- <th width="12%">Actions</th> -->
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assessmentForms as $form)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>                           
                                        <td>
                                            <span class="text-dark border">
                                                
                                                {{ $form->studentClass->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                
                                                {{ $form->staff->full_name ?? $form->staff->name ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="file-icon mr-2">
                                                    <i class="{{ $form->file_icon }} fa-2x text-primary"></i>
                                                </div>
                                                <div>
                                                    <a href="{{ route('assessment-forms.show', $form->id) }}" 
                                                       class="font-weight-bold text-dark">
                                                        {{ Str::limit($form->file_name, 25) }}
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-dark px-3 py-2">
                                                {{ ucfirst($form->title) }}
                                            </span>
                                        </td>
                                        
                                        <td>
                                            <span class="text-dark px-3 py-2">
                                                {{ ucfirst($form->subject->name) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-dark px-3 py-2">
                                                {{ ucfirst($form->assessment_type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>
                                                
                                                {{ $form->assessment_date->format('d/m/Y') }}
                                            </small>
                                        </td>
                                        <td>
                                            <span class="text-danger border px-3 py-2">
                                                <i class="fas fa-download text-primary mr-1"></i>
                                                {{ $form->downloads_count }}
                                            </span>
                                        </td>
                                        <!-- <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('assessment-forms.show', $form->id) }}" 
                                                   class="btn btn-outline-dark" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @can('update', $form)
                                                    <a href="{{ route('assessment-forms.edit', $form->id) }}" 
                                                       class="btn btn-outline-warning" title="Edit Form">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan
                                                <a href="{{ route('assessment-forms.download', $form->id) }}" 
                                                   class="btn btn-outline-dark" title="Download Form">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                @can('delete', $form)
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            title="Delete Form"
                                                            onclick="confirmDelete('{{ $form->id }}', '{{ $form->file_name }}')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                    <form id="delete-form-{{ $form->id }}" 
                                                          action="{{ route('assessment-forms.destroy', $form->id) }}" 
                                                          method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                @endcan
                                            </div>
                                        </td> -->
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9">
                                            <div class="text-center py-5">
                                                <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                                                <h5 class="text-muted">No Assessment Forms Found</h5>
                                                <p class="text-muted">Try adjusting your filters or upload a new form.</p>
                                                <a href="{{ route('assessment-forms.create') }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-upload mr-2"></i>Upload New Form
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <small class="text-muted">
                                Showing {{ $assessmentForms->firstItem() ?? 0 }} to {{ $assessmentForms->lastItem() ?? 0 }} 
                                of {{ $assessmentForms->total() }} results
                            </small>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end">
                                {{ $assessmentForms->appends(request()->all())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .stats-card {
        transition: transform 0.2s;
        cursor: default;
    }
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    .stats-icon {
        opacity: 0.7;
    }
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .bg-gradient-success {
        background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    }
    .bg-gradient-warning {
        background: linear-gradient(135deg, #fccb90 0%, #d57eeb 100%);
    }
    .bg-gradient-secondary {
        background: linear-gradient(135deg, #a8a8a8 0%, #6c757d 100%);
    }
    .bg-gradient-info {
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    }
    .file-icon {
        width: 40px;
        text-align: center;
    }
    .table td {
        vertical-align: middle;
    }
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
    }
    .select2-container .select2-selection--single {
        height: 31px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 29px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 29px !important;
    }
</style>
@endpush

@push('scripts')
<script>
    // Initialize Select2
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%',
            allowClear: true,
            placeholder: 'Select option'
        });
    });

    // Delete confirmation with SweetAlert
    function confirmDelete(id, fileName) {
        Swal.fire({
            title: 'Are you sure?',
            html: `You are about to delete <strong>"${fileName}"</strong><br>This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }

    // Auto-submit filter on select change
    $(document).ready(function() {
        $('.filter-select').on('change', function() {
            $('#filterForm').submit();
        });
    });
</script>
@endpush

@push('styles')
<!-- Include Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
@endsection