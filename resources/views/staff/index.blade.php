@extends('layouts.master')

@section('title', 'Staff Management')

@push('styles')
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<style>
    .dataTables_filter {
        display: none;
    }
    .dataTables_length {
        margin-bottom: 15px;
    }
    /* Hide default DataTables info to avoid duplicate */
    .dataTables_info {
        display: none;
    }
    .avatar-sm {
        width: 35px;
        height: 35px;
        font-size: 14px;
        font-weight: bold;
    }
    .card {
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
        font-size: 13px;
        vertical-align: middle;
    }
    /* Hide pagination info if we want custom display */
    .dataTables_paginate {
        margin-top: 10px;
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
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .alert {
        animation: fadeIn 0.3s ease;
    }
    
    /* Custom info bar styling */
    .custom-table-info {
        font-size: 13px;
        color: #6c757d;
        padding: 10px 0;
    }
</style>
@endpush

@section('content')

<div class="container-fluid py-4">

    <!-- HEADER -->
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="fw-semibold text-dark mb-0">
                        <i class="fas fa-users me-2 text-danger"></i>Staff Management
                    </h4>
                </div>

                <a href="{{ route('staff.create') }}" class="btn btn-white text-dark rounded-3">
                    <i class="fas fa-user-plus me-1"></i>
                    Add Staff
                </a>
            </div>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- FILTER PANEL -->
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <input type="text" id="globalSearch" class="form-control" placeholder="Search ">
                </div>

                <div class="col-md-2">
                    <select id="departmentFilter" class="form-select">
                        <option value="">All Departments</option>
                        @php
                            $departments = $staff->pluck('department')->unique()->filter()->sort();
                        @endphp
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}">{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select id="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                        <option value="On Leave">On Leave</option>
                        <option value="Suspended">Suspended</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select id="typeFilter" class="form-select">
                        <option value="">All Types</option>
                        @php
                            $types = $staff->pluck('staff_type')->unique()->filter()->sort();
                        @endphp
                        @foreach($types as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select id="genderFilter" class="form-select">
                        <option value="">All Genders</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="col-md-1">
                    <button id="resetFilters" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-sync-alt me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="staffTable" class="table table-hover align-middle w-100 mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">#</th>
                            <th>Staff ID</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date Employed</th>
                            <th width="120" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staff as $index => $item)
                            @php
                                $statusClass = match($item->status) {
                                    'Active' => 'success',
                                    'Inactive' => 'secondary',
                                    'On Leave' => 'warning',
                                    'Suspended' => 'danger',
                                    default => 'light'
                                };
                            @endphp
                            <tr>
                                <td class="text-muted">{{ $loop->iteration }}</td>
                                <td><code>{{ $item->staff_id }}</code></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <strong>{{ $item->first_name }} {{ $item->last_name }}</strong>
                                            @if($item->other_name)
                                                <br><small class="text-muted">{{ $item->other_name }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $item->gender ?? '-' }}</td>
                                <td>{{ $item->phone ?? '-' }}</td>
                                <td>{{ $item->email ?? '-' }}</td>
                                <td>{{ $item->department ?? '-' }}</td>
                                <td>{{ $item->position ?? '-' }}</td>
                                <td>
                                    @if($item->staff_type)
                                        <span class="badge bg-white text-dark">{{ $item->staff_type }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $statusClass }}">{{ $item->status ?? 'N/A' }}</span>
                                </td>
                                <td>{{ $item->date_employed ? \Carbon\Carbon::parse($item->date_employed)->format('d M, Y') : '-' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('staff.show', $item->id) }}" class="btn btn-outline-white" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('staff.edit', $item->id) }}" class="btn btn-outline-white" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-white" onclick="deleteStaff({{ $item->id }})" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-5 text-muted">
                                    <i class="fas fa-user-slash fa-3x mb-3 d-block"></i>
                                    No staff records found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3">
            <!-- Custom info display - only one info bar -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="custom-table-info">
                    <i class="fas fa-chart-line me-1"></i>
                    Showing <strong id="showingCount">{{ $staff->count() }}</strong> of <strong id="totalCount">{{ $staff->count() }}</strong> staff records
                </div>
                <div id="tableLength" class="custom-table-info">
                    <i class="fas fa-eye me-1"></i>
                    Rows per page: 
                    <select id="rowsPerPage" class="form-select form-select-sm d-inline-block w-auto">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="-1">All</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-trash me-2"></i>Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this staff member?</p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Staff</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
// Global variable to track DataTable instance
let staffDataTable = null;

$(document).ready(function() {
    initializeDataTable();
    
    // Custom rows per page selector
    $('#rowsPerPage').on('change', function() {
        var value = $(this).val();
        staffDataTable.page.len(value).draw();
    });
});

function initializeDataTable() {
    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#staffTable')) {
        staffDataTable.destroy();
        staffDataTable = null;
    }
    
    // Initialize DataTable
    staffDataTable = $('#staffTable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        order: [[1, 'desc']],
        language: {
            search: "",
            searchPlaceholder: "Search...",
            lengthMenu: "Show _MENU_ entries",
            info: "",  // Empty to hide default info
            infoEmpty: "",
            infoFiltered: "",
            zeroRecords: "No matching records found",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Prev"
            }
        },
        drawCallback: function() {
            var info = this.api().page.info();
            $('#showingCount').text(info.recordsDisplay);
            $('#totalCount').text(info.recordsTotal);
            updateStatsCounts();
            
            // Update rows per page selector
            $('#rowsPerPage').val(info.length);
        }
    });

    // Function to update statistics based on visible rows
    function updateStatsCounts() {
        var visibleRows = $('#staffTable tbody tr:visible');
        var totalVisible = visibleRows.length;
        var active = 0;
        var onLeave = 0;
        var departments = new Set();
        
        visibleRows.each(function() {
            var statusCell = $(this).find('td').eq(9);
            var deptCell = $(this).find('td').eq(6);
            
            if (statusCell.text().trim() === 'Active') active++;
            if (statusCell.text().trim() === 'On Leave') onLeave++;
            
            var dept = deptCell.text().trim();
            if (dept && dept !== '-') departments.add(dept);
        });
        
        $('#totalStaffCount').text(totalVisible);
        $('#activeCount').text(active);
        $('#onLeaveCount').text(onLeave);
        $('#deptCount').text(departments.size);
    }

    // GLOBAL SEARCH
    $('#globalSearch').off('keyup').on('keyup', function() {
        staffDataTable.search(this.value).draw();
    });

    // DEPARTMENT FILTER (column index 6)
    $('#departmentFilter').off('change').on('change', function() {
        staffDataTable.column(6).search(this.value).draw();
    });

    // STATUS FILTER (column index 9)
    $('#statusFilter').off('change').on('change', function() {
        staffDataTable.column(9).search(this.value).draw();
    });

    // TYPE FILTER (column index 8)
    $('#typeFilter').off('change').on('change', function() {
        staffDataTable.column(8).search(this.value).draw();
    });

    // GENDER FILTER (column index 3)
    $('#genderFilter').off('change').on('change', function() {
        staffDataTable.column(3).search(this.value).draw();
    });

    // RESET FILTERS
    $('#resetFilters').off('click').on('click', function() {
        $('#globalSearch').val('');
        $('#departmentFilter').val('');
        $('#statusFilter').val('');
        $('#typeFilter').val('');
        $('#genderFilter').val('');
        
        staffDataTable.search('').columns().search('').draw();
        showToast('Filters reset successfully', 'success');
    });
    
    // Initial stats update
    updateStatsCounts();
}

function deleteStaff(id) {
    const form = document.getElementById('deleteForm');
    form.action = `/staff/${id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function exportToCSV() {
    const rows = $('#staffTable tbody tr:visible');
    if (rows.length === 0) {
        showToast('No data to export', 'warning');
        return;
    }
    
    let csv = [["#", "Staff ID", "Name", "Gender", "Phone", "Email", "Department", "Position", "Type", "Status", "Date Employed"]];
    
    rows.each(function(index) {
        const cells = $(this).find('td');
        const rowData = [];
        
        rowData.push(index + 1);
        rowData.push(cells.eq(1).text().trim());
        rowData.push(cells.eq(2).text().trim().split('\n')[0]);
        rowData.push(cells.eq(3).text().trim());
        rowData.push(cells.eq(4).text().trim());
        rowData.push(cells.eq(5).text().trim());
        rowData.push(cells.eq(6).text().trim());
        rowData.push(cells.eq(7).text().trim());
        rowData.push(cells.eq(8).text().trim());
        rowData.push(cells.eq(9).text().trim());
        rowData.push(cells.eq(10).text().trim());
        
        csv.push(rowData.map(cell => `"${cell.replace(/"/g, '""')}"`).join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `staff_export_${new Date().toISOString().slice(0, 19)}.csv`;
    a.click();
    URL.revokeObjectURL(url);
    
    showToast('Data exported successfully!', 'success');
}

function printTable() {
    const printContent = $('#staffTable').clone();
    const originalTitle = document.title;
    document.title = 'Staff Report';
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Staff Report</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body { padding: 20px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    @media print {
                        .btn, .btn-group { display: none; }
                    }
                </style>
            </head>
            <body>
                <div class="container-fluid">
                    <h3>Staff Report</h3>
                    <p>Generated: ${new Date().toLocaleString()}</p>
                    <hr>
                    ${printContent[0].outerHTML}
                </div>
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
    document.title = originalTitle;
}

function copyTable() {
    const rows = $('#staffTable tbody tr:visible');
    if (rows.length === 0) {
        showToast('No data to copy', 'warning');
        return;
    }
    
    let copyText = "";
    
    // Headers
    $('#staffTable thead th:not(:last-child)').each(function() {
        copyText += $(this).text().trim() + "\t";
    });
    copyText += "\n";
    
    // Data
    rows.each(function() {
        $(this).find('td:not(:last-child)').each(function() {
            copyText += $(this).text().trim().replace(/\n/g, ' ') + "\t";
        });
        copyText += "\n";
    });
    
    navigator.clipboard.writeText(copyText);
    showToast('Table copied to clipboard!', 'success');
}

function showToast(message, type) {
    // Remove existing toasts
    $('.toast-notification').remove();
    
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
@endpush