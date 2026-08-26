@extends('layouts.master')

@section('title', 'Attendance Sessions')

@section('content')

<div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 mt-3">
        <div>
            <h5 class="mb-0">
                <i class="fas fa-list me-2 text-primary"></i>
                Attendance Records
            </h5>
        </div>

        <a href="{{ route('attendance-sessions.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i>
            Take Attendance
        </a>
    </div>

    {{-- FLASH MESSAGE --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-primary alert-dismissible fade show shadow-sm">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ENHANCED SEARCH + FILTER CARD --}}
    <div class="card border-0 shadow-sm mb-1">
        <div class="card-body">
            <div class="row g-3">
                {{-- SEARCH --}}
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text"
                               id="searchInput"
                               class="form-control"
                               placeholder="Search "
                               autocomplete="off">
                    </div>
                </div>

                {{-- STATUS FILTER --}}
                <div class="col-md-3">
                    <select id="statusFilter" class="form-select">
                        <option value="all">All Status</option>
                        <option value="completed">✓ Completed</option>
                        <option value="pending">⏳ Pending</option>
                        <option value="draft">📝 Draft</option>
                    </select>
                </div>

                {{-- DATE FILTER --}}
                <div class="col-md-2">
                    <input type="date" id="dateFilter" class="form-control">
                </div>

                {{-- RESET --}}
                <div class="col-md-2">
                    <!-- <label class="form-label d-block">&nbsp;</label> -->
                    <button type="button" class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                        <i class="fas fa-sync-alt me-1"></i> Reset
                    </button>
                </div>
            </div>

            {{-- ACTIVE FILTERS DISPLAY --}}
            <div class="row mt-3">
                <div class="col-12">
                    <div id="activeFilters" class="d-flex flex-wrap gap-2"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <!-- <h5 class="mb-0">
                    <i class="fas fa-list me-2 text-dark"></i>
                    Attendance Records
                </h5> -->
                <!-- <div class="d-flex gap-2">
                    <button onclick="exportToCSV()" class="btn btn-sm btn-success">
                        <i class="fas fa-download me-1"></i> Export CSV
                    </button>
                </div> -->
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="sessionsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="60">#</th>
                            <th><i class="fas fa-school me-1 text-primary"></i> Class</th>
                            <th><i class="fas fa-calendar-alt me-1 text-success"></i> Date</th>
                            <th><i class="fas fa-user-check me-1 text-info"></i> Taken By</th>
                            <th><i class="fas fa-tag me-1 text-warning"></i> Status</th>
                            <th class="text-center"><i class="fas fa-cogs me-1 text-secondary"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody id="sessionsTableBody">
                        @forelse($sessions as $index => $session)
                            @php
                                $status = strtolower($session->status);
                                $badge = match($status) {
                                    'completed' => 'success',
                                    'pending' => 'warning',
                                    'draft' => 'secondary',
                                    default => 'primary'
                                };
                                $statusIcon = match($status) {
                                    'completed' => 'fa-check-circle',
                                    'pending' => 'fa-clock',
                                    'draft' => 'fa-pen',
                                    default => 'fa-circle'
                                };
                            @endphp
                            <tr class="session-row"
                                data-id="{{ $session->id }}"
                                data-class="{{ strtolower($session->studentClass->name ?? '') }}"
                                data-date="{{ $session->attendance_date }}"
                                data-user="{{ strtolower($session->takenBy->name ?? '') }}"
                                data-status="{{ strtolower($session->status) }}">

                                <td class="fw-bold text-muted">
                                    {{ $sessions->firstItem() + $index }}
                                </td>

                                <td>
                                    <div class="fw-semibold">{{ $session->studentClass->name ?? 'N/A' }}</div>
                                </td>

                                <td>
                                    <span class="badge bg-light text-dark border px-3 py-2">
                                        <i class="fas fa-calendar-day me-1"></i>
                                        {{ \Carbon\Carbon::parse($session->attendance_date)->format('M d, Y') }}
                                    </span>
                                </td>

                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            {{ $session->takenBy->name ?? 'N/A' }}
                                            <br>
                                            <small class="text-muted">{{ $session->takenBy->email ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="badge bg-{{ $badge }} px-3 py-2">
                                        <i class="fas {{ $statusIcon }} me-1"></i>
                                        {{ ucfirst($session->status) }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('attendance-sessions.show', $session->id) }}"
                                           class="btn btn-outline-white" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyRow">
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3 d-block"></i>
                                    No attendance records found.
                                </td>
                            </tr>
                        @endforelse
                        {{-- NO RESULTS ROW --}}
                        <tr id="noResultsRow" style="display:none;">
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-search fa-3x mb-3 d-block"></i>
                                No matching sessions found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $sessions->links() }}
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal (Single Delete) --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-trash me-2"></i>Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this attendance session?</p>
                <p class="text-muted small">This action cannot be undone. All attendance records will be permanently removed.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-primary">Delete Session</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    initializeFilters();
    updateStats();
});

function initializeFilters() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const dateFilter = document.getElementById('dateFilter');

    if (searchInput) searchInput.addEventListener('keyup', debounce(filterTable, 300));
    if (statusFilter) statusFilter.addEventListener('change', filterTable);
    if (dateFilter) dateFilter.addEventListener('change', filterTable);
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
    const search = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const status = document.getElementById('statusFilter')?.value || 'all';
    const date = document.getElementById('dateFilter')?.value || '';

    const rows = document.querySelectorAll('.session-row');
    let visibleCount = 0;
    let completed = 0, pending = 0, draft = 0;

    rows.forEach(row => {
        const className = row.dataset.class || '';
        const rowDate = row.dataset.date || '';
        const user = row.dataset.user || '';
        const rowStatus = row.dataset.status || '';

        const matchesSearch = className.includes(search) || user.includes(search) || rowDate.includes(search);
        const matchesStatus = status === 'all' || rowStatus === status;
        const matchesDate = date === '' || rowDate === date;

        if (matchesSearch && matchesStatus && matchesDate) {
            row.style.display = '';
            visibleCount++;
            
            // Count statuses for stats
            if (rowStatus === 'completed') completed++;
            else if (rowStatus === 'pending') pending++;
            else if (rowStatus === 'draft') draft++;
        } else {
            row.style.display = 'none';
        }
    });

    // Update counts
    document.getElementById('totalCount').innerText = visibleCount;
    document.getElementById('completedCount').innerText = completed;
    document.getElementById('pendingCount').innerText = pending;
    document.getElementById('draftCount').innerText = draft;
    document.getElementById('visibleCount').innerText = visibleCount;

    // Show/hide no results message
    const noResultsRow = document.getElementById('noResultsRow');
    const emptyRow = document.getElementById('emptyRow');
    
    if (visibleCount === 0 && rows.length > 0) {
        noResultsRow.style.display = '';
        if (emptyRow) emptyRow.style.display = 'none';
    } else {
        noResultsRow.style.display = 'none';
        if (emptyRow && rows.length === 0) emptyRow.style.display = '';
    }

    updateActiveFilters({search, status, date});
}

function updateActiveFilters(filters) {
    const container = document.getElementById('activeFilters');
    if (!container) return;
    
    container.innerHTML = '';
    
    if (filters.search && filters.search !== '') {
        addFilterBadge(container, 'Search: ' + filters.search, 'search');
    }
    if (filters.status && filters.status !== 'all') {
        let statusText = filters.status === 'completed' ? 'Completed' : (filters.status === 'pending' ? 'Pending' : 'Draft');
        addFilterBadge(container, 'Status: ' + statusText, 'status');
    }
    if (filters.date && filters.date !== '') {
        addFilterBadge(container, 'Date: ' + filters.date, 'date');
    }
}

function addFilterBadge(container, text, type) {
    const badge = document.createElement('span');
    badge.className = 'badge bg-light text-dark border px-3 py-2';
    badge.innerHTML = `${text} <i class="fas fa-times-circle ms-2 text-primary" style="cursor:pointer;" onclick="removeFilter('${type}')"></i>`;
    container.appendChild(badge);
}

function removeFilter(type) {
    switch(type) {
        case 'search':
            document.getElementById('searchInput').value = '';
            break;
        case 'status':
            document.getElementById('statusFilter').value = 'all';
            break;
        case 'date':
            document.getElementById('dateFilter').value = '';
            break;
    }
    filterTable();
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = 'all';
    document.getElementById('dateFilter').value = '';
    
    const rows = document.querySelectorAll('.session-row');
    rows.forEach(row => row.style.display = '');
    document.getElementById('noResultsRow').style.display = 'none';
    document.getElementById('activeFilters').innerHTML = '';
    
    filterTable();
    showToast('Filters reset successfully', 'info');
}

function updateStats() {
    const rows = document.querySelectorAll('.session-row');
    let completed = 0, pending = 0, draft = 0;
    
    rows.forEach(row => {
        const status = row.dataset.status;
        if (status === 'completed') completed++;
        else if (status === 'pending') pending++;
        else if (status === 'draft') draft++;
    });
    
    document.getElementById('totalCount').innerText = rows.length;
    document.getElementById('completedCount').innerText = completed;
    document.getElementById('pendingCount').innerText = pending;
    document.getElementById('draftCount').innerText = draft;
    document.getElementById('visibleCount').innerText = rows.length;
}

function deleteSession(id) {
    const form = document.getElementById('deleteForm');
    form.action = `/attendance-sessions/${id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function exportToCSV() {
    const rows = document.querySelectorAll('.session-row:not([style*="display: none"])');
    if (rows.length === 0) {
        showToast('No data to export', 'warning');
        return;
    }
    
    let csv = [["#", "Class", "Date", "Taken By", "Status"]];
    
    rows.forEach(row => {
        const number = row.cells[0]?.innerText.trim() || '';
        const className = row.cells[1]?.innerText.trim().split('\n')[0] || '';
        const date = row.cells[2]?.innerText.trim().replace(/[^A-Za-z0-9,\s]/g, '').trim() || '';
        const takenBy = row.cells[3]?.innerText.trim().split('\n')[0] || '';
        const status = row.cells[4]?.innerText.trim() || '';
        
        csv.push([`"${number}"`, `"${className}"`, `"${date}"`, `"${takenBy}"`, `"${status}"`]);
    });
    
    const csvContent = csv.map(row => row.join(',')).join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `attendance_sessions_${new Date().toISOString().slice(0, 19)}.csv`;
    a.click();
    URL.revokeObjectURL(url);
    
    showToast('Data exported successfully!', 'success');
}

function printTable() {
    const printContent = document.getElementById('sessionsTable').cloneNode(true);
    const originalTitle = document.title;
    document.title = 'Attendance Sessions Report';
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Attendance Sessions Report</title>
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
                    <h3>Attendance Sessions Report</h3>
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
    toast.className = `alert alert-${type === 'error' ? 'primary' : type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
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
    border-radius: 14px;
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

#activeFilters .badge {
    font-size: 12px;
    font-weight: normal;
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

.stats-card {
    transition: all 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
}
</style>

@endsection