@extends('layouts.master')

@section('title', 'Student Class Assignments')

@section('content')

<div class="container-fluid py-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-2">
        <div>
            <h5 class="fw-bold mb-1">
                <i class="fas fa-user-graduate me-2 text-primary"></i>
                Student Class Enrolled
            </h5>
        </div>
        <a href="{{ route('student-class-assignments.create') }}" class="btn btn-primary text-white">
            <i class="fas fa-plus-circle me-1"></i> Assign Student
        </a>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-primary alert-dismissible fade show shadow-sm mb-4">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- TABLE CARD --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2 text-primary"></i>
                    Assignment Records
                    <span id="visibleCount" class="badge bg-dar ms-2 text-dark">{{ $assignments->count() }}</span>
                </h5>
                <div class="row g-3 align-items-end">
                <div class="card border-0 shadow-sm rounded-4 ">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search...................... ">
                </div>
                </div>
                <div class="d-flex gap-2">
                    <button onclick="exportToCSV()" class="btn btn-sm btn-success">
                        <i class="fas fa-file-excel me-1"></i> Export
                    </button>
                    <button onclick="window.print()" class="btn btn-sm btn-secondary">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="assignmentsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="50">#</th>
                            <th><i class="fas fa-user me-1"></i> Student</th>
                            <th><i class="fas fa-school me-1"></i> Class</th>
                            <th><i class="fas fa-calendar me-1"></i> Academic Year</th>
                            <th><i class="fas fa-tag me-1"></i> Status</th>
                            <th><i class="fas fa-flag me-1"></i> Current</th>
                            <th width="150"><i class="fas fa-cog me-1"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $index => $assignment)
                            @php
                                $statusClass = match($assignment->status) {
                                    'active' => 'success',
                                    'promoted' => 'primary',
                                    'graduated' => 'dark',
                                    'repeated' => 'warning',
                                    default => 'secondary'
                                };
                                $statusIcon = match($assignment->status) {
                                    'active' => 'fa-check-circle',
                                    'promoted' => 'fa-arrow-up',
                                    'graduated' => 'fa-graduation-cap',
                                    'repeated' => 'fa-undo',
                                    default => 'fa-circle'
                                };
                            @endphp
                            <tr class="assignment-row"
                                data-id="{{ $assignment->id }}"
                                data-student="{{ strtolower($assignment->student->first_name ?? '') }} {{ strtolower($assignment->student->last_name ?? '') }}"
                                data-class="{{ strtolower($assignment->studentClass->name ?? '') }}"
                                data-status="{{ $assignment->status }}"
                                data-current="{{ $assignment->is_current ? '1' : '0' }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div>
                                            <strong>{{ $assignment->student->first_name ?? '' }} {{ $assignment->student->last_name ?? '' }}</strong>
                                            <br>
                                            <small class="text-muted">ID: {{ $assignment->student->student_id ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-3 py-2">
                                        <i class="fas fa-building me-1"></i>
                                        {{ $assignment->studentClass->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    {{ $assignment->academicYear->name ?? 'N/A' }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $statusClass }} px-3 py-2">
                                        <i class="fas {{ $statusIcon }} me-1"></i>
                                        {{ ucfirst($assignment->status) }}
                                    </span>
                                </td>
                                <td>
                                @if($assignment->is_current)

                                <span class="badge bg-success px-3 py-2">
                                    <i class="fas fa-check-circle me-1"></i> Current
                                </span>

                                @elseif($assignment->status == 'Graduated')

                                <span class="badge bg-dark px-3 py-2">
                                    <i class="fas fa-graduation-cap me-1"></i> Graduated
                                </span>

                                @else

                                <span class="badge bg-secondary px-3 py-2">
                                    <i class="fas fa-history me-1"></i> Old
                                </span>

                                @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('student-class-assignments.show', $assignment->id) }}" 
                                           class="btn btn-outline-white" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('student-class-assignments.edit', $assignment->id) }}" 
                                           class="btn btn-outline-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-user-graduate fa-3x mb-3 d-block"></i>
                                    No assignments found. Click "Assign Student" to create one.
                                </td>
                            </td>
                        @endforelse
                        <tr id="noResultsRow" style="display: none;">
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-search fa-3x mb-3 d-block"></i>
                                No matching assignments found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3">
            <div class="text-muted small">
                Showing <span id="showingCount">{{ $assignments->count() }}</span> of <span id="totalRecords">{{ $assignments->count() }}</span> assignments
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-trash me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the class assignment for <strong id="deleteStudentName"></strong>?</p>
                <p class="text-muted small mb-0">This action cannot be undone. The student will be unassigned from this class.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete Assignment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let deleteId = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeFilters();
    updateStats();
});

function initializeFilters() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const currentFilter = document.getElementById('currentFilter');

    if (searchInput) searchInput.addEventListener('keyup', debounce(applyFilters, 300));
    if (statusFilter) statusFilter.addEventListener('change', applyFilters);
    if (currentFilter) currentFilter.addEventListener('change', applyFilters);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function applyFilters() {
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const status = document.getElementById('statusFilter')?.value || '';
    const current = document.getElementById('currentFilter')?.value || '';

    const rows = document.querySelectorAll('.assignment-row');
    let visibleCount = 0;
    let currentCount = 0;
    let promotedCount = 0;
    let activeStudentsCount = 0;

    rows.forEach(row => {
        const studentName = row.dataset.student || '';
        const className = row.dataset.class || '';
        const rowStatus = row.dataset.status || '';
        const rowCurrent = row.dataset.current || '';

        const matchesSearch = studentName.includes(searchTerm) || className.includes(searchTerm);
        const matchesStatus = status === '' || rowStatus === status;
        const matchesCurrent = current === '' || rowCurrent === current;

        if (matchesSearch && matchesStatus && matchesCurrent) {
            row.style.display = '';
            visibleCount++;
            
            if (rowCurrent === '1') currentCount++;
            if (rowStatus === 'promoted') promotedCount++;
            if (rowStatus === 'active') activeStudentsCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Update counts
    document.getElementById('totalCount').innerText = visibleCount;
    document.getElementById('currentCount').innerText = currentCount;
    document.getElementById('promotedCount').innerText = promotedCount;
    document.getElementById('activeStudentsCount').innerText = activeStudentsCount;
    document.getElementById('visibleCount').innerText = visibleCount;
    document.getElementById('showingCount').innerText = visibleCount;
    document.getElementById('totalRecords').innerText = visibleCount;

    // Show/hide no results
    const noResultsRow = document.getElementById('noResultsRow');
    
    if (visibleCount === 0 && rows.length > 0) {
        noResultsRow.style.display = '';
    } else {
        noResultsRow.style.display = 'none';
    }
}

function updateStats() {
    const rows = document.querySelectorAll('.assignment-row');
    let current = 0, promoted = 0, active = 0;
    
    rows.forEach(row => {
        if (row.dataset.current === '1') current++;
        if (row.dataset.status === 'promoted') promoted++;
        if (row.dataset.status === 'active') active++;
    });
    
    document.getElementById('totalCount').innerText = rows.length;
    document.getElementById('currentCount').innerText = current;
    document.getElementById('promotedCount').innerText = promoted;
    document.getElementById('activeStudentsCount').innerText = active;
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('currentFilter').value = '';
    
    const rows = document.querySelectorAll('.assignment-row');
    rows.forEach(row => row.style.display = '');
    document.getElementById('noResultsRow').style.display = 'none';
    
    applyFilters();
    showToast('Filters reset successfully', 'info');
}

function confirmDelete(id, studentName) {
    deleteId = id;
    document.getElementById('deleteStudentName').innerText = studentName || 'this student';
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Handle delete form submission
document.getElementById('deleteForm')?.addEventListener('submit', function(e) {
    if (deleteId) {
        this.action = `/student-class-assignments/${deleteId}`;
    }
});

function exportToCSV() {
    const rows = document.querySelectorAll('.assignment-row:not([style*="display: none"])');
    if (rows.length === 0) {
        showToast('No data to export', 'warning');
        return;
    }
    
    let csv = [["#", "Student Name", "Student ID", "Class", "Academic Year", "Status", "Current"]];
    
    rows.forEach((row, index) => {
        const cells = row.querySelectorAll('td');
        if (cells.length >= 6) {
            // Extract student name from the cell (remove the ID part)
            const studentCell = cells[1]?.innerText || '';
            const studentName = studentCell.split('\n')[0]?.trim() || '';
            
            // Extract student ID
            const studentIdMatch = studentCell.match(/ID:\s*(\S+)/);
            const studentId = studentIdMatch ? studentIdMatch[1] : '';
            
            const className = cells[2]?.innerText?.trim() || '';
            const academicYear = cells[3]?.innerText?.trim() || '';
            const status = cells[4]?.innerText?.trim() || '';
            const current = cells[5]?.innerText?.trim() || '';
            
            csv.push([
                `"${index + 1}"`,
                `"${studentName}"`,
                `"${studentId}"`,
                `"${className}"`,
                `"${academicYear}"`,
                `"${status}"`,
                `"${current}"`
            ].join(','));
        }
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `class_assignments_${new Date().toISOString().slice(0, 19)}.csv`;
    a.click();
    URL.revokeObjectURL(url);
    
    showToast('Data exported successfully!', 'success');
}

function showToast(message, type) {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.toast-notification');
    existingToasts.forEach(toast => toast.remove());
    
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type === 'error' ? 'danger' : type}`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : (type === 'error' ? 'exclamation-circle' : 'info-circle')} me-2"></i>
        ${message}
    `;
    document.body.appendChild(toast);
    setTimeout(() => {
        if (toast && toast.remove) toast.remove();
    }, 3000);
}
</script>

<style>
    .table th {
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
    }
    
    .table td {
        font-size: 13px;
        vertical-align: middle;
    }
    
    .avatar-sm {
        font-weight: bold;
    }
    
    .btn-group-sm .btn {
        padding: 4px 10px;
    }
    
    .card {
        border-radius: 12px;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.03);
        cursor: pointer;
    }
    
    .badge {
        font-weight: 500;
        border-radius: 8px;
    }
    
    .toast-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        font-size: 13px;
        animation: slideIn 0.3s ease;
    }
    .toast-success { border-left: 4px solid #28a745; }
    .toast-warning { border-left: 4px solid #ffc107; }
    .toast-info { border-left: 4px solid #17a2b8; }
    .toast-danger { border-left: 4px solid #dc3545; }
    
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @media print {
        .btn, .btn-group, .alert, .card-header .btn, #resetFilters, .toast-notification {
            display: none !important;
        }
        .card {
            box-shadow: none !important;
        }
        .table th, .table td {
            border: 1px solid #ddd !important;
        }
    }
</style>

@endsection