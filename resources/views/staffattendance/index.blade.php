@extends('layouts.master')

@section('title', 'Staff Attendance Management')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h5 class="mb-0 mt-3 fw-bold">
                <i class="fas fa-users me-2 text-danger"></i>
                Staff Attendance Management
            </h5>
        </div>

        <a href="{{ route('staff-attendance.create') }}" class="btn btn-white text-dark mt-3">
            <i class="fas fa-fingerprint me-1"></i>
            Take Attendance
        </a>
    </div>

    {{-- FILTERS AND SEARCH SECTION --}}
    <div class="card shadow-sm mb-1">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search by name...">
                </div>

                <div class="col-md-2">        
                    <input type="date" id="fromDate" class="form-control">
                </div>

                <div class="col-md-2">
                    <input type="date" id="toDate" class="form-control">
                </div>

                <div class="col-md-2">
                    
                    <select id="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="present">Present</option>
                        <option value="late">Late</option>
                        <option value="absent">Absent</option>
                    </select>
                </div>

                <div class="col-md-2">
                   
                    <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                        <i class="fas fa-sync-alt me-1"></i> Reset Filters
                    </button>
                </div>

                <div class="col-md-1">
                    
                    <button class="btn btn-success w-100" onclick="exportToExcel()">
                        <i class="fas fa-download me-1"></i> Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ATTENDANCE TABLE --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0 align-middle" id="attendanceTable">
                    <thead class="table-white text-dark">
                        <tr>
                            <th width="50">#</th>
                            <th><i class="fas fa-user me-1"></i> Staff Name</th>
                            <th><i class="fas fa-calendar me-1"></i> Date</th>
                            <th><i class="fas fa-clock me-1"></i> Clock In</th>
                            <th><i class="fas fa-clock me-1"></i> Clock Out</th>
                            <th><i class="fas fa-tag me-1"></i> Status</th>
                            <th width="150"><i class="fas fa-cog me-1"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody id="attendanceTableBody">
                        @forelse($attendances as $key => $attendance)
                            <tr class="attendance-row"
                                data-id="{{ $attendance->id }}"
                                data-staff="{{ strtolower($attendance->staff->first_name ?? '') }} {{ strtolower($attendance->staff->last_name ?? '') }}"
                                data-date="{{ $attendance->date }}"
                                data-status="{{ $attendance->status }}"
                                data-clock-in="{{ $attendance->clock_in_time ?? '' }}"
                                data-clock-out="{{ $attendance->clock_out_time ?? '' }}">

                                <td>{{ $key + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <strong>{{ $attendance->staff->first_name ?? '' }} {{ $attendance->staff->last_name ?? '' }}</strong>
                                            <br>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-3 py-2">
                                        <i class="fas fa-calendar-day me-1"></i>
                                        {{ \Carbon\Carbon::parse($attendance->date)->format('M d, Y') }}
                                    </span>
                                </td>
                                <td>
                                    @if($attendance->clock_in_time)
                                        <span class="badge bg-white text-dark">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ \Carbon\Carbon::parse($attendance->clock_in_time)->format('h:i A') }}
                                        </span>
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </td>
                                <td>
                                    @if($attendance->clock_out_time)
                                        <span class="badge  bg-white text-dark">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ \Carbon\Carbon::parse($attendance->clock_out_time)->format('h:i A') }}
                                        </span>
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $color = match($attendance->status) {
                                            'present' => 'success',
                                            'late' => 'warning',
                                            'absent' => 'danger',
                                            default => 'secondary'
                                        };
                                        $icon = match($attendance->status) {
                                            'present' => 'fa-check-circle',
                                            'late' => 'fa-clock',
                                            'absent' => 'fa-times-circle',
                                            default => 'fa-circle'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $color }} px-3 py-2">
                                        <i class="fas {{ $icon }} me-1"></i>
                                        {{ ucfirst($attendance->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('staff-attendance.show', $attendance->id) }}"
                                           class="btn tn-white text-darko" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('staff-attendance.edit', $attendance->id) }}"
                                           class="btn btn-white text-dark" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyRow">
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3 d-block"></i>
                                    No attendance records found.
                                </td>
                            </tr>
                        @endforelse
                        <tr id="noResultsRow" style="display: none;">
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-search fa-3x mb-3 d-block"></i>
                                No matching records found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    Showing <span id="showingCount">{{ $attendances->count() }}</span> of <span id="totalCount">{{ $attendances->count() }}</span> records
                </div>
                <div id="paginationLinks">
                    {{ $attendances->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-trash me-2"></i>Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this attendance record?</p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Record</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeFilters();
    updateStats();
});

function initializeFilters() {
    const searchInput = document.getElementById('searchInput');
    const fromDate = document.getElementById('fromDate');
    const toDate = document.getElementById('toDate');
    const statusFilter = document.getElementById('statusFilter');

    if (searchInput) searchInput.addEventListener('keyup', debounce(filterTable, 300));
    if (fromDate) fromDate.addEventListener('change', filterTable);
    if (toDate) toDate.addEventListener('change', filterTable);
    if (statusFilter) statusFilter.addEventListener('change', filterTable);
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

function filterTable() {
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const fromDate = document.getElementById('fromDate')?.value || '';
    const toDate = document.getElementById('toDate')?.value || '';
    const status = document.getElementById('statusFilter')?.value || '';

    const rows = document.querySelectorAll('.attendance-row');
    let visibleCount = 0;
    let present = 0, late = 0, absent = 0;

    rows.forEach(row => {
        const staffName = row.dataset.staff || '';
        const rowDate = row.dataset.date || '';
        const rowStatus = row.dataset.status || '';

        const matchesSearch = staffName.includes(searchTerm);
        const matchesStatus = status === '' || rowStatus === status;
        
        let matchesDate = true;
        if (fromDate && rowDate < fromDate) matchesDate = false;
        if (toDate && rowDate > toDate) matchesDate = false;

        if (matchesSearch && matchesStatus && matchesDate) {
            row.style.display = '';
            visibleCount++;
            
            if (rowStatus === 'present') present++;
            else if (rowStatus === 'late') late++;
            else if (rowStatus === 'absent') absent++;
        } else {
            row.style.display = 'none';
        }
    });

    // Update counts
    document.getElementById('totalRecords').innerText = visibleCount;
    document.getElementById('presentCount').innerText = present;
    document.getElementById('lateCount').innerText = late;
    document.getElementById('absentCount').innerText = absent;
    document.getElementById('visibleCount').innerText = visibleCount;
    document.getElementById('showingCount').innerText = visibleCount;

    // Show/hide no results
    const noResultsRow = document.getElementById('noResultsRow');
    const emptyRow = document.getElementById('emptyRow');
    
    if (visibleCount === 0 && rows.length > 0) {
        noResultsRow.style.display = '';
        if (emptyRow) emptyRow.style.display = 'none';
    } else {
        noResultsRow.style.display = 'none';
        if (emptyRow && rows.length === 0) emptyRow.style.display = '';
    }
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('fromDate').value = '';
    document.getElementById('toDate').value = '';
    document.getElementById('statusFilter').value = '';
    
    const rows = document.querySelectorAll('.attendance-row');
    rows.forEach(row => row.style.display = '');
    document.getElementById('noResultsRow').style.display = 'none';
    
    filterTable();
    showToast('Filters reset successfully', 'info');
}

function updateStats() {
    const rows = document.querySelectorAll('.attendance-row');
    let present = 0, late = 0, absent = 0;
    
    rows.forEach(row => {
        const status = row.dataset.status;
        if (status === 'present') present++;
        else if (status === 'late') late++;
        else if (status === 'absent') absent++;
    });
    
    document.getElementById('totalRecords').innerText = rows.length;
    document.getElementById('presentCount').innerText = present;
    document.getElementById('lateCount').innerText = late;
    document.getElementById('absentCount').innerText = absent;
    document.getElementById('visibleCount').innerText = rows.length;
    document.getElementById('showingCount').innerText = rows.length;
    document.getElementById('totalCount').innerText = rows.length;
}

function deleteAttendance(id) {
    const form = document.getElementById('deleteForm');
    form.action = `/staffattendance/${id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function exportToExcel() {
    const rows = document.querySelectorAll('.attendance-row:not([style*="display: none"])');
    if (rows.length === 0) {
        showToast('No data to export', 'warning');
        return;
    }
    
    let csv = [["#", "Staff Name", "Date", "Clock In", "Clock Out", "Status"]];
    
    rows.forEach((row, index) => {
        const staffName = row.cells[1]?.innerText.trim().split('\n')[0] || '';
        const date = row.cells[2]?.innerText.trim() || '';
        const clockIn = row.cells[3]?.innerText.trim() || '';
        const clockOut = row.cells[4]?.innerText.trim() || '';
        const status = row.cells[5]?.innerText.trim() || '';
        
        csv.push([`${index + 1}`, `"${staffName}"`, `"${date}"`, `"${clockIn}"`, `"${clockOut}"`, `"${status}"`]);
    });
    
    const csvContent = csv.map(row => row.join(',')).join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `staff_attendance_${new Date().toISOString().slice(0, 19)}.csv`;
    a.click();
    URL.revokeObjectURL(url);
    
    showToast('Data exported successfully!', 'success');
}

function printTable() {
    const printContent = document.getElementById('attendanceTable').cloneNode(true);
    const originalTitle = document.title;
    document.title = 'Staff Attendance Report';
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Staff Attendance Report</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body { padding: 20px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    @media print {
                        .no-print { display: none; }
                        body { padding: 0; }
                    }
                </style>
            </head>
            <body>
                <div class="container-fluid">
                    <h3>Staff Attendance Report</h3>
                    <p>Generated: ${new Date().toLocaleString()}</p>
                    <hr>
                    ${printContent.outerHTML}
                </div>
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
    document.title = originalTitle;
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    toast.style.zIndex = '9999';
    toast.style.minWidth = '280px';
    toast.style.zIndex = '10000';
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : (type === 'error' ? 'exclamation-circle' : 'info-circle')} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(toast);
    setTimeout(() => {
        if (toast && toast.remove) toast.remove();
    }, 3000);
}
</script>

<style>
.card {
    border-radius: 12px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
}

.table th {
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
}

.table td {
    vertical-align: middle;
    font-size: 13px;
}

.table-hover tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.03);
    transition: 0.2s ease;
}

.avatar-sm {
    font-size: 14px;
    font-weight: bold;
}

.badge {
    border-radius: 8px;
}

.btn {
    border-radius: 8px;
}

.input-group-text {
    background-color: white;
}

.form-select, .form-control {
    border-radius: 8px;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.alert {
    animation: fadeIn 0.3s ease;
}

.pagination {
    margin-bottom: 0;
}
</style>

@endsection