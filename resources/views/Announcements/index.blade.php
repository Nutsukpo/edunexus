@extends('layouts.master')

@section('title', 'Manage Announcements')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">
                <i class="fas fa-bullhorn text-primary me-2"></i>
                Announcements
            </h4>
            <small class="text-muted">Manage school announcements and notices</small>
        </div>
        <a href="{{ route('announcements.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>
            New Announcement
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-bullhorn text-primary"></i>
                        </div>
                        <div>
                            <small class="text-muted">Total</small>
                            <h5 class="fw-bold mb-0">{{ $counts['total'] ?? 0 }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-check-circle text-success"></i>
                        </div>
                        <div>
                            <small class="text-muted">Published</small>
                            <h5 class="fw-bold mb-0">{{ $counts['published'] ?? 0 }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-pen text-warning"></i>
                        </div>
                        <div>
                            <small class="text-muted">Drafts</small>
                            <h5 class="fw-bold mb-0">{{ $counts['drafts'] ?? 0 }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-danger bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-clock text-danger"></i>
                        </div>
                        <div>
                            <small class="text-muted">Expired</small>
                            <h5 class="fw-bold mb-0">{{ $counts['expired'] ?? 0 }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('announcements.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search announcements..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="all">All Types</option>
                        <option value="general" {{ request('type') == 'general' ? 'selected' : '' }}>General</option>
                        <option value="academic" {{ request('type') == 'academic' ? 'selected' : '' }}>Academic</option>
                        <option value="event" {{ request('type') == 'event' ? 'selected' : '' }}>Event</option>
                        <option value="urgent" {{ request('type') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="exam" {{ request('type') == 'exam' ? 'selected' : '' }}>Exam</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="audience" class="form-select">
                        <option value="all">All Audiences</option>
                        <option value="all" {{ request('audience') == 'all' ? 'selected' : '' }}>Everyone</option>
                        <option value="students" {{ request('audience') == 'students' ? 'selected' : '' }}>Students</option>
                        <option value="staff" {{ request('audience') == 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="parents" {{ request('audience') == 'parents' ? 'selected' : '' }}>Parents</option>
                        <option value="teachers" {{ request('audience') == 'teachers' ? 'selected' : '' }}>Teachers</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="all">All Status</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>
                        Filter
                    </button>
                    <a href="{{ route('announcements.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-undo me-2"></i>
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Announcements Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Audience</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Posted</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($announcements as $announcement)
                        <tr id="announcement-row-{{ $announcement->id }}">
                            <td>
                                <input type="checkbox" class="announcement-checkbox" value="{{ $announcement->id }}">
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $announcement->title }}</div>
                                @if($announcement->is_featured)
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-star me-1"></i>
                                        Featured
                                    </span>
                                @endif
                                @if($announcement->isExpired())
                                    <span class="badge bg-danger">
                                        <i class="fas fa-clock me-1"></i>
                                        Expired
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $announcement->type_badge ?? 'secondary' }}">
                                    {{ ucfirst($announcement->type) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $announcement->audience_badge ?? 'secondary' }}">
                                    {{ ucfirst($announcement->audience) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $announcement->priority_color ?? 'secondary' }}">
                                    {{ ucfirst($announcement->priority) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $announcement->status_badge }}">
                                    {{ $announcement->status_text }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $announcement->time_ago ?? 'N/A' }}</small>
                                <br>
                                <small class="text-muted">by {{ $announcement->creator->name ?? 'Unknown' }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('announcements.show', $announcement) }}" class="btn btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('announcements.edit', $announcement) }}" class="btn btn-outline-success" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(!$announcement->isExpired())
                                    <button class="btn btn-outline-warning" onclick="expireAnnouncement({{ $announcement->id }})" title="Expire">
                                        <i class="fas fa-clock"></i>
                                    </button>
                                    @endif
                                    <button class="btn btn-outline-danger" onclick="deleteAnnouncement({{ $announcement->id }})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-bullhorn fa-3x text-muted mb-3 d-block"></i>
                                <h5 class="text-muted">No announcements found</h5>
                                <p class="text-muted">Create your first announcement to get started.</p>
                                <a href="{{ route('announcements.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>
                                    Create Announcement
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <button class="btn btn-danger btn-sm" onclick="bulkDelete()" id="bulkDeleteBtn" style="display:none;">
                        <i class="fas fa-trash me-2"></i>
                        Delete Selected
                    </button>
                    <button class="btn btn-warning btn-sm" onclick="bulkExpire()" id="bulkExpireBtn" style="display:none;">
                        <i class="fas fa-clock me-2"></i>
                        Expire Selected
                    </button>
                </div>
                {{ $announcements->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to delete this announcement? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash me-2"></i>
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Delete Confirmation Modal -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-labelledby="bulkDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="bulkDeleteModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirm Bulk Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Are you sure you want to delete the selected announcements?</p>
                <p class="text-muted small mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmBulkDeleteBtn">
                    <i class="fas fa-trash me-2"></i>
                    Delete All Selected
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Expire Confirmation Modal -->
<div class="modal fade" id="bulkExpireModal" tabindex="-1" aria-labelledby="bulkExpireModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="bulkExpireModalLabel">
                    <i class="fas fa-clock me-2"></i>
                    Confirm Bulk Expire
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Are you sure you want to expire the selected announcements?</p>
                <p class="text-muted small mb-0">Expired announcements will be unpublished and hidden from public view.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmBulkExpireBtn">
                    <i class="fas fa-clock me-2"></i>
                    Expire All Selected
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for individual delete -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Hidden form for individual expire -->
<form id="expireForm" method="POST" style="display: none;">
    @csrf
    @method('PUT')
</form>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Select all checkbox
        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                document.querySelectorAll('.announcement-checkbox').forEach(cb => {
                    cb.checked = this.checked;
                });
                toggleBulkButtons();
            });
        }

        // Individual checkbox
        document.querySelectorAll('.announcement-checkbox').forEach(cb => {
            cb.addEventListener('change', toggleBulkButtons);
        });

        // Individual delete confirmation
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', function() {
                const form = document.getElementById('deleteForm');
                if (form && form.action) {
                    // Close the modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
                    if (modal) modal.hide();

                    // Show SweetAlert confirmation
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'This action cannot be undone!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Submit the form
                            form.submit();
                        }
                    });
                }
            });
        }

        // Bulk delete confirmation
        const confirmBulkDeleteBtn = document.getElementById('confirmBulkDeleteBtn');
        if (confirmBulkDeleteBtn) {
            confirmBulkDeleteBtn.addEventListener('click', function() {
                const ids = getSelectedIds();

                if (ids.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Selection',
                        text: 'Please select at least one announcement to delete.',
                        confirmButtonColor: '#0d6efd'
                    });
                    return;
                }

                // Close the modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('bulkDeleteModal'));
                if (modal) modal.hide();

                // Show processing state
                Swal.fire({
                    title: 'Deleting...',
                    text: `Deleting ${ids.length} announcement(s)`,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Perform bulk delete
                performBulkAction('{{ route("announcements.bulk-delete") }}', ids)
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: `${data.deleted || ids.length} announcement(s) deleted successfully.`,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Failed to delete announcements.',
                                confirmButtonColor: '#0d6efd'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while deleting announcements.',
                            confirmButtonColor: '#0d6efd'
                        });
                        console.error('Error:', error);
                    });
            });
        }

        // Bulk expire confirmation
        const confirmBulkExpireBtn = document.getElementById('confirmBulkExpireBtn');
        if (confirmBulkExpireBtn) {
            confirmBulkExpireBtn.addEventListener('click', function() {
                const ids = getSelectedIds();

                if (ids.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Selection',
                        text: 'Please select at least one announcement to expire.',
                        confirmButtonColor: '#0d6efd'
                    });
                    return;
                }

                // Close the modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('bulkExpireModal'));
                if (modal) modal.hide();

                // Show processing state
                Swal.fire({
                    title: 'Expiring...',
                    text: `Expiring ${ids.length} announcement(s)`,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Perform bulk expire
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Expired!',
                                text: `${data.expired || ids.length} announcement(s) expired successfully.`,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Failed to expire announcements.',
                                confirmButtonColor: '#0d6efd'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while expiring announcements.',
                            confirmButtonColor: '#0d6efd'
                        });
                        console.error('Error:', error);
                    });
            });
        }
    });

    function getSelectedIds() {
        const ids = [];
        document.querySelectorAll('.announcement-checkbox:checked').forEach(cb => {
            ids.push(cb.value);
        });
        return ids;
    }

    function toggleBulkButtons() {
        const checked = document.querySelectorAll('.announcement-checkbox:checked');
        const deleteBtn = document.getElementById('bulkDeleteBtn');
        const expireBtn = document.getElementById('bulkExpireBtn');
        
        if (deleteBtn) {
            deleteBtn.style.display = checked.length > 0 ? 'inline-block' : 'none';
            deleteBtn.innerHTML = `<i class="fas fa-trash me-2"></i> Delete Selected (${checked.length})`;
        }
        
        if (expireBtn) {
            expireBtn.style.display = checked.length > 0 ? 'inline-block' : 'none';
            expireBtn.innerHTML = `<i class="fas fa-clock me-2"></i> Expire Selected (${checked.length})`;
        }
    }

    function deleteAnnouncement(id) {
        const form = document.getElementById('deleteForm');
        if (form) {
            form.action = `{{ route('announcements.index') }}/${id}`;
        }
        
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }

    function expireAnnouncement(id) {
        Swal.fire({
            title: 'Expire Announcement?',
            text: 'This will unpublish the announcement and mark it as expired.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, expire it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Expiring...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Submit expire request
                fetch(`{{ route('announcements.index') }}/${id}/expire`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Expired!',
                            text: 'Announcement expired successfully.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to expire announcement.',
                            confirmButtonColor: '#0d6efd'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while expiring the announcement.',
                        confirmButtonColor: '#0d6efd'
                    });
                    console.error('Error:', error);
                });
            }
        });
    }

    function bulkDelete() {
        const ids = getSelectedIds();
        if (ids.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Selection',
                text: 'Please select at least one announcement to delete.',
                confirmButtonColor: '#0d6efd'
            });
            return;
        }

        const modal = new bootstrap.Modal(document.getElementById('bulkDeleteModal'));
        modal.show();
    }

    function bulkExpire() {
        const ids = getSelectedIds();
        if (ids.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Selection',
                text: 'Please select at least one announcement to expire.',
                confirmButtonColor: '#0d6efd'
            });
            return;
        }

        const modal = new bootstrap.Modal(document.getElementById('bulkExpireModal'));
        modal.show();
    }

    async function performBulkAction(url, ids) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: ids })
        });

        return response.json();
    }
</script>
@endpush