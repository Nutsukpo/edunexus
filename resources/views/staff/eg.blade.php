@extends('layouts.master')

@section('title', 'Staff Management')

@section('content')

<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    * {
        font-family: 'Inter', sans-serif;
    }
    
    :root {
        --primary-green: #0b6b57;
        --primary-yellow: #f0b400;
        --soft-bg: #f5f6f8;
    }
    
    .main-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .card-header {
        background: linear-gradient(135deg, var(--primary-green), #0d8a6d);
        color: white;
        padding: 25px 30px;
        border-bottom: none;
    }
    
    .card-header h2 {
        margin: 0;
        font-weight: 700;
        font-size: 28px;
    }
    
    .card-header p {
        margin: 8px 0 0 0;
        opacity: 0.9;
        font-size: 14px;
    }
    
    .btn-add {
        background: rgba(255,255,255,0.2);
        border: 2px solid rgba(255,255,255,0.3);
        color: white;
        border-radius: 12px;
        padding: 10px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-add:hover {
        background: white;
        color: var(--primary-green);
        border-color: white;
        transform: translateY(-2px);
    }
    
    .table-container {
        padding: 25px 30px;
        background: #f8f9fa;
    }
    
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 20px;
    }
    
    .dataTables_wrapper .dataTables_filter input {
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        padding: 8px 16px;
        margin-left: 10px;
        transition: all 0.3s ease;
    }
    
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: var(--primary-green);
        outline: none;
        box-shadow: 0 0 0 3px rgba(11,107,87,0.1);
    }
    
    table.dataTable {
        border-collapse: separate;
        border-spacing: 0 12px;
        margin-top: -12px;
    }
    
    table.dataTable thead th {
        background: white;
        padding: 15px 20px;
        font-weight: 700;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #4a5568;
        border-bottom: 2px solid #e2e8f0;
    }
    
    table.dataTable tbody tr {
        background: white;
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    
    table.dataTable tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    }
    
    table.dataTable tbody td {
        padding: 18px 20px;
        vertical-align: middle;
        border: none;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .staff-photo {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        object-fit: cover;
        border: 2px solid #e2e8f0;
    }
    
    .staff-name {
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 4px;
    }
    
    .staff-id {
        font-size: 12px;
        color: var(--primary-green);
        font-weight: 600;
    }
    
    .badge-status {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .badge-active {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .badge-inactive {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }
    
    .badge-on-leave {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }
    
    .action-buttons {
        white-space: nowrap;
    }
    
    .action-buttons .btn {
        padding: 6px 10px;
        margin: 0 2px;
        border-radius: 8px;
        font-size: 12px;
        transition: all 0.2s ease;
    }
    
    .btn-view {
        background: #667eea;
        color: white;
    }
    
    .btn-edit {
        background: #48bb78;
        color: white;
    }
    
    .btn-delete {
        background: #f56565;
        color: white;
    }
    
    /* Modal styles */
    .modal-content {
        border-radius: 20px;
        border: none;
    }
    
    .modal-header {
        background: linear-gradient(135deg, var(--primary-green), #0d8a6d);
        color: white;
        border: none;
        border-radius: 20px 20px 0 0;
    }
    
    .delete-icon {
        font-size: 60px;
        color: #f56565;
        margin-bottom: 20px;
    }
    
    /* Loading Overlay */
    #loadingOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 10000;
    }
    
    .loading-spinner {
        background: white;
        padding: 30px;
        border-radius: 15px;
        text-align: center;
    }
    
    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            text-align: center;
            gap: 15px;
        }
        .table-container {
            padding: 15px;
            overflow-x: auto;
        }
    }
</style>

<div class="main-container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h2>
                    <i class="fas fa-users me-2"></i> Staff Management
                </h2>
                <p>Manage and oversee all staff members</p>
            </div>
            <div>
                <a href="{{ route('staff.create') }}" class="btn btn-add">
                    <i class="fas fa-plus-circle me-2"></i>Add New Staff
                </a>
            </div>
        </div>
        
        <div class="table-container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            <table id="staffTable" class="table table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Photo</th>
                        <th>Staff Info</th>
                        <th>Contact</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staff as $member)
                    <tr id="staff-row-{{ $member->id }}">
                        <td>
                            <span class="staff-id">#{{ $member->staff_id }}</span>
                        </td>
                        <td>
                            @if($member->photo && file_exists(public_path('uploads/staff/'.$member->photo)))
                                <img src="{{ asset('uploads/staff/'.$member->photo) }}" alt="{{ $member->first_name }}" class="staff-photo">
                            @else
                                <div class="staff-photo bg-light d-flex align-items-center justify-content-center">
                                    <i class="fas fa-user text-secondary" style="font-size: 20px;"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="staff-name">
                                {{ $member->first_name }} {{ $member->last_name }}
                                @if($member->other_name)
                                    <span class="text-muted">({{ $member->other_name }})</span>
                                @endif
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-calendar-alt me-1"></i> 
                                {{ $member->date_of_birth ? date('M d, Y', strtotime($member->date_of_birth)) : 'N/A' }}
                            </small>
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-venus-mars me-1"></i> {{ $member->gender ?? 'N/A' }}
                            </small>
                        </td>
                        <td>
                            @if($member->email)
                                <div>
                                    <i class="fas fa-envelope me-1 text-muted"></i>
                                    <small>{{ $member->email }}</small>
                                </div>
                            @endif
                            @if($member->phone)
                                <div class="mt-1">
                                    <i class="fas fa-phone me-1 text-muted"></i>
                                    <small>{{ $member->phone }}</small>
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-info bg-opacity-10 text-dark p-2">
                                <i class="fas fa-building me-1"></i> {{ $member->department ?? 'N/A' }}
                            </span>
                        </td>
                        <td>{{ $member->position ?? 'N/A' }}</td>
                        <td>
                            @php
                                $statusClass = '';
                                switch($member->status) {
                                    case 'active':
                                        $statusClass = 'badge-active';
                                        break;
                                    case 'inactive':
                                        $statusClass = 'badge-inactive';
                                        break;
                                    case 'on_leave':
                                        $statusClass = 'badge-on-leave';
                                        break;
                                    default:
                                        $statusClass = 'badge-active';
                                }
                            @endphp
                            <span class="badge-status {{ $statusClass }}">
                                <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                                {{ ucfirst(str_replace('_', ' ', $member->status ?? 'active')) }}
                            </span>
                        </td>
                        <td class="action-buttons">
                            <a href="{{ route('staff.show', $member) }}" class="btn btn-view btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('staff.edit', $member) }}" class="btn btn-edit btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-delete btn-sm delete-btn" 
                                    data-id="{{ $member->id }}"
                                    data-name="{{ $member->first_name }} {{ $member->last_name }}"
                                    data-code="{{ $member->staff_id }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-trash-alt me-2"></i> Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="delete-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h4 class="mb-3">Are you sure?</h4>
                <p class="text-muted">You are about to delete <strong id="deleteStaffName"></strong><br>
                <span id="deleteStaffCode" class="text-muted small"></span></p>
                <p class="text-danger small mt-3">
                    <i class="fas fa-exclamation-circle me-1"></i> This action cannot be undone!
                </p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash-alt me-2"></i> Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay">
    <div class="loading-spinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 mb-0">Deleting...</p>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#staffTable').DataTable({
        responsive: true,
        order: [[0, 'desc']],
        pageLength: 10,
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ staff members",
            infoEmpty: "Showing 0 to 0 of 0 staff members",
            zeroRecords: "No matching staff records found",
        },
        columnDefs: [
            { orderable: true, targets: [0,1,2,3,4,5,6] },
            { orderable: false, targets: [7] },
        ]
    });
    
    // Variable to store current delete ID
    var currentDeleteId = null;
    
    // Handle delete button click
    $(document).on('click', '.delete-btn', function() {
        currentDeleteId = $(this).data('id');
        var staffName = $(this).data('name');
        var staffCode = $(this).data('code');
        
        $('#deleteStaffName').text(staffName);
        $('#deleteStaffCode').html('<small>Staff ID: ' + staffCode + '</small>');
        
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    });
    
    // Handle confirm delete
    $('#confirmDeleteBtn').on('click', function() {
        if (!currentDeleteId) return;
        
        // Show loading overlay
        $('#loadingOverlay').fadeIn(200);
        
        // Close modal first
        var modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
        if (modal) {
            modal.hide();
        }
        
        // Remove any lingering backdrops
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $('body').css('overflow', '');
        
        // Perform AJAX delete
        $.ajax({
            url: '/staff/' + currentDeleteId,
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                // Show success message
                var alertHtml = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                    '<i class="fas fa-check-circle me-2"></i> ' + (response.message || 'Staff deleted successfully!') +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>';
                $('.table-container').prepend(alertHtml);
                
                // Remove the row
                $('#staff-row-' + currentDeleteId).fadeOut(300, function() {
                    table.row($(this)).remove().draw();
                });
                
                // Auto dismiss alert after 3 seconds
                setTimeout(function() {
                    $('.alert').fadeOut(500, function() {
                        $(this).remove();
                    });
                }, 3000);
            },
            error: function(xhr) {
                var errorMsg = 'Error deleting staff member.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                var alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                    '<i class="fas fa-exclamation-circle me-2"></i> ' + errorMsg +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>';
                $('.table-container').prepend(alertHtml);
                
                setTimeout(function() {
                    $('.alert').fadeOut(500, function() {
                        $(this).remove();
                    });
                }, 5000);
            },
            complete: function() {
                $('#loadingOverlay').fadeOut(200);
                currentDeleteId = null;
            }
        });
    });
    
    // Clean up modal when closed
    $('#deleteModal').on('hidden.bs.modal', function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $('body').css('overflow', '');
    });
});
</script>

@endsection