@extends('layouts.master')

@section('title', 'Leave List')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaves List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a73e8;
            --primary-light: #e8f0fe;
            --primary-dark: #1557b0;
            --primary-gradient: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
            --header-bg: linear-gradient(135deg, #1a73e8 0%, #1557b0 100%);
            --shadow-color: rgba(26, 115, 232, 0.15);
            --bg-light: #f5f9ff;
        }
        
        body {
            background-color: var(--bg-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 30px var(--shadow-color);
            overflow: hidden;
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid var(--primary-light);
            padding: 1.2rem 1.5rem;
        }
        
        .card-header h5 {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        #leavesTable {
            font-size: 0.875rem;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        #leavesTable thead th {
            background: var(--header-bg);
            border: none;
            position: sticky;
            top: 0;
            z-index: 1;
            color: white;
            font-weight: 600;
            padding: 12px 16px;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        
        #leavesTable thead th:first-child {
            border-radius: 8px 0 0 0;
        }
        
        #leavesTable thead th:last-child {
            border-radius: 0 8px 0 0;
        }
        
        #leavesTable tbody tr {
            transition: all 0.2s ease;
        }
        
        #leavesTable tbody tr:hover {
            background-color: var(--primary-light);
            transform: scale(1.002);
        }
        
        #leavesTable tbody td {
            padding: 10px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f0f4ff;
        }
        
        .badge {
            font-size: 0.7rem;
            padding: 0.35em 0.8em;
            font-weight: 600;
            border-radius: 20px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }
        
        .dropdown-item.active .badge {
            background-color: white !important;
            color: var(--bs-primary) !important;
        }
        
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_filter input {
            font-size: 0.875rem;
        }
        
        .dataTables_filter input {
            border-radius: 10px !important;
            border: 1.5px solid #d4e4ff !important;
            padding: 0.5rem 1rem !important;
            transition: all 0.3s ease;
        }
        
        .dataTables_filter input:focus {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 4px rgba(26, 115, 232, 0.15) !important;
        }
        
        .dataTables_length select {
            border-radius: 10px !important;
            border: 1.5px solid #d4e4ff !important;
            padding: 0.3rem 0.8rem !important;
        }
        
        .table-hover tbody tr:hover td {
            background-color: transparent;
        }
        
        .btn-outline {
            border-color: rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-outline:hover {
            background-color: var(--primary-light);
            border-color: var(--primary-color);
        }
        
        /* Status Badge Colors - Blue Theme */
        .status-badge-approved {
            background: linear-gradient(135deg, #34a853, #2d8f47);
            color: white;
        }
        
        .status-badge-pending {
            background: linear-gradient(135deg, #fbbc04, #f9a825);
            color: #1a1a1a;
        }
        
        .status-badge-rejected {
            background: linear-gradient(135deg, #ea4335, #d33426);
            color: white;
        }
        
        .status-badge-draft {
            background: linear-gradient(135deg, #9aa0a6, #80868b);
            color: white;
        }
        
        .status-badge-cancelled {
            background: linear-gradient(135deg, #9aa0a6, #80868b);
            color: white;
        }
        
        /* Button Styles */
        .btn-view {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 0.3rem 0.7rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(26, 115, 232, 0.3);
            color: white;
        }
        
        .btn-edit {
            background: linear-gradient(135deg, #fbbc04, #f9a825);
            color: #1a1a1a;
            border: none;
            padding: 0.3rem 0.7rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(251, 188, 4, 0.4);
            color: #1a1a1a;
        }
        
        .btn-new-leave {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 0.5rem 1.2rem;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-new-leave:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(26, 115, 232, 0.35);
            color: white;
        }
        
        /* Dropdown Button */
        .btn-filter {
            background: white;
            border: 1.5px solid #d4e4ff;
            color: var(--text-secondary);
            border-radius: 10px;
            padding: 0.4rem 1rem;
            transition: all 0.3s ease;
        }
        
        .btn-filter:hover {
            border-color: var(--primary-color);
            background: var(--primary-light);
        }
        
        .dropdown-menu {
            border: 1px solid #d4e4ff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 0.5rem;
        }
        
        .dropdown-item {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
        }
        
        .dropdown-item:hover {
            background-color: var(--primary-light);
        }
        
        .dropdown-item .badge {
            font-size: 0.65rem;
        }
        
        /* Alert Styles */
        .alert-info {
            background-color: var(--primary-light);
            border: none;
            color: var(--primary-dark);
            border-radius: 12px;
            padding: 1rem;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #e6f4ea, #d4edda);
            border: none;
            color: #1e7e34;
            border-radius: 12px;
            padding: 1rem;
        }
        
        /* Pagination */
        .dataTables_paginate .paginate_button {
            border-radius: 8px !important;
            border: 1px solid #d4e4ff !important;
            margin: 0 3px !important;
            transition: all 0.3s ease !important;
        }
        
        .dataTables_paginate .paginate_button.current {
            background: var(--primary-gradient) !important;
            color: white !important;
            border-color: var(--primary-color) !important;
        }
        
        .dataTables_paginate .paginate_button:hover {
            background: var(--primary-light) !important;
            border-color: var(--primary-color) !important;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .card-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .card-header .d-flex {
                width: 100%;
                justify-content: space-between;
            }
            
            #leavesTable thead th {
                font-size: 0.6rem;
                padding: 8px 10px;
            }
            
            #leavesTable tbody td {
                font-size: 0.75rem;
                padding: 8px 10px;
            }
        }
        
        /* DataTables info */
        .dataTables_info {
            color: var(--text-secondary) !important;
            font-size: 0.85rem !important;
        }
        
        /* Custom scroll for table */
        .table-responsive {
            border-radius: 12px;
            overflow-x: auto;
        }
        
        /* Empty state */
        .empty-state {
            padding: 3rem;
            text-align: center;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: var(--primary-light);
            margin-bottom: 1rem;
        }
        
        .empty-state h6 {
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-check me-2" style="color: var(--primary-color);"></i>
                    Leaves
                </h5>
                <div class="d-flex gap-2">
                    <!-- Status Filter Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-filter btn-sm dropdown-toggle" type="button" 
                                id="statusFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-filter me-1"></i> Status
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="statusFilterDropdown">
                            <li><a class="dropdown-item filter-status" href="#" data-status="">All Statuses</a></li>
                            <li>
                                <a class="dropdown-item filter-status" href="#" data-status="approved">
                                    <span class="badge status-badge-approved me-2">Approved</span> Approved
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item filter-status" href="#" data-status="pending">
                                    <span class="badge status-badge-pending me-2">Pending</span> Pending
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item filter-status" href="#" data-status="rejected">
                                    <span class="badge status-badge-rejected me-2">Rejected</span> Rejected
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item filter-status" href="#" data-status="draft">
                                    <span class="badge status-badge-draft me-2">Draft</span> Draft
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item filter-status" href="#" data-status="cancelled">
                                    <span class="badge status-badge-cancelled me-2">Cancelled</span> Cancelled
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <a href="{{ route('leaves.create') }}" class="btn btn-new-leave btn-sm">
                        <i class="fas fa-plus me-1"></i> New Leave
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show py-2 mb-3">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($leaves->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h6>No leaves found.</h6>
                        <p class="text-muted small">Click "New Leave" to apply for a leave.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table id="leavesTable" class="table table-hover table-sm" style="width:100%">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th width="10%">Start Date</th>
                                    <th width="10%">End Date</th>
                                    <th width="12%">Days Applied</th>
                                    <th width="12%">Days Granted</th>
                                    <th width="9%">Status</th>
                                    <th width="10%">Applied On</th>
                                    <th width="10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($leaves as $leave)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <span class="badge bg-light text-dark rounded-circle p-2">
                                                        {{ strtoupper(substr($leave->full_name ?? 'N/A', 0, 1)) }}
                                                    </span>
                                                </div>
                                                {{ $leave->full_name ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td>{{ $leave->leave_type }}</td>
                                        <td data-order="{{ $leave->date_commencement }}">
                                            {{ \Carbon\Carbon::parse($leave->date_commencement)->format('d M, Y') }}
                                        </td>
                                        <td data-order="{{ $leave->date_resumption }}">
                                            {{ \Carbon\Carbon::parse($leave->date_resumption)->format('d M, Y') }}
                                        </td>
                                        <td class="text-center">{{ $leave->days_applied_for ?? 0 }}</td>
                                        <td class="text-center">{{ $leave->days_granted ?? 'N/A' }}</td>
                                        <td data-order="{{ $leave->status }}">
                                            @php
                                                $statusClass = 'status-badge-' . $leave->status;
                                            @endphp
                                            <span class="badge rounded-pill {{ $statusClass }}">
                                                {{ ucfirst($leave->status) }}
                                            </span>
                                        </td>
                                        <td data-order="{{ $leave->created_at }}">
                                            {{ \Carbon\Carbon::parse($leave->created_at)->format('d M, Y') }}
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('leaves.watch', $leave->id) }}" 
                                                   class="btn btn-view btn-sm" title="View">
                                                    <i class="far fa-eye"></i>
                                                </a>
                                                @if($leave->status === 'draft' || $leave->status === 'pending')
                                                    <a href="{{ route('leaves.edit', $leave->id) }}" 
                                                       class="btn btn-edit btn-sm" title="Edit">
                                                        <i class="far fa-edit"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#leavesTable').DataTable({
            "order": [[8, "desc"]], // Default sort by applied date
            "columnDefs": [
                { "orderable": false, "targets": [0, 9] }, // Disable sorting for # and Actions
                { "type": "date", "targets": [3, 4, 8] }, // Date sorting
                { "type": "num", "targets": [5, 6] }  // Numeric sorting for days
            ],
            "responsive": true,
            "pageLength": 25,
            "dom": '<"top"lf>rt<"bottom"ip><"clear">',
            "language": {
                "search": "",
                "searchPlaceholder": "Search leaves...",
                "lengthMenu": "Show _MENU_ leaves",
                "info": "Showing _START_ to _END_ of _TOTAL_ leaves",
                "infoEmpty": "No leaves available",
                "paginate": {
                    "previous": "<i class='fas fa-chevron-left'></i>",
                    "next": "<i class='fas fa-chevron-right'></i>"
                }
            },
            "initComplete": function() {
                $('.dataTables_filter input').addClass('form-control form-control-sm');
                $('.dataTables_filter input').attr('placeholder', 'Search leaves...');
                $('.dataTables_length select').addClass('form-select form-select-sm');
                
                // Style the search wrapper
                $('.dataTables_filter').addClass('mb-3');
                $('.dataTables_length').addClass('mb-3');
            }
        });

        // Status filter functionality
        $('.filter-status').on('click', function(e) {
            e.preventDefault();
            var status = $(this).data('status');
            var badge = $(this).find('.badge').clone();
            
            // Update dropdown button text
            if (status) {
                badge.addClass('me-2');
                $('#statusFilterDropdown').html(
                    `<i class="fas fa-filter me-1"></i>` + 
                    badge[0].outerHTML + 
                    $(this).text().trim()
                );
            } else {
                $('#statusFilterDropdown').html('<i class="fas fa-filter me-1"></i> Status');
            }
            
            // Filter the table
            table.column(7).search(status).draw();
            
            // Highlight selected item
            $('.filter-status').removeClass('active');
            $(this).addClass('active');
        });
        
        // Clear filter when clicking "All Statuses"
        $('.filter-status[data-status=""]').on('click', function() {
            $('#statusFilterDropdown').html('<i class="fas fa-filter me-1"></i> Status');
        });
    });
    </script>
</body>
</html>
@endsection