@extends('layouts.master')

@section('title', 'Assign Staff to Payroll Period')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm" style="border-radius: 12px; border: none;">
                <div class="card-header" style="background: linear-gradient(135deg, #1a3c5e 0%, #2a5f7a 100%); color: white; border-radius: 12px 12px 0 0; padding: 20px 25px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1 fw-bold">
                                <i class="fas fa-user-plus me-2"></i> Assign Staff
                            </h5>
                            <small>
                                <i class="fas fa-calendar-alt me-1"></i> {{ $payrollPeriod->name }} ({{ $payrollPeriod->period_code }})
                            </small>
                        </div>
                        <div>
                            <a href="{{ route('payroll-periods.show', $payrollPeriod->id) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.15); color: white;">
                                <i class="fas fa-arrow-left me-1"></i> Back to Details
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body" style="background: #f8f9fa; padding: 25px;">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-8">
                            <div class="card" style="border: none; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="text-uppercase mb-0" style="color: #6c757d; font-size: 12px; letter-spacing: 0.5px;">
                                            <i class="fas fa-users me-1"></i> Available Staff
                                            <span class="badge bg-primary ms-2" id="availableCount">{{ $availableStaff->count() }}</span>
                                        </h6>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllBtn">
                                                <i class="fas fa-check-double me-1"></i> Select All
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllBtn">
                                                <i class="fas fa-times me-1"></i> Deselect All
                                            </button>
                                        </div>
                                    </div>
                                    <hr>
                                    
                                    <form action="{{ route('payroll.assign-staff.store', $payrollPeriod->id) }}" method="POST" id="assignStaffForm">
                                        @csrf
                                        
                                        <!-- Staff Selection with Advanced Options -->
                                        <div class="mb-3">
                                            <div class="row g-2 mb-2">
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold">Filter by Department</label>
                                                    <select id="departmentFilter" class="form-select form-select-sm">
                                                        <option value="">All Departments</option>
                                                        @php
                                                            $departments = $availableStaff->pluck('department')->unique()->filter()->values();
                                                        @endphp
                                                        @foreach($departments as $dept)
                                                            <option value="{{ $dept }}">{{ $dept }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold">Filter by Position</label>
                                                    <select id="positionFilter" class="form-select form-select-sm">
                                                        <option value="">All Positions</option>
                                                        @php
                                                            $positions = $availableStaff->pluck('position')->unique()->filter()->values();
                                                        @endphp
                                                        @foreach($positions as $pos)
                                                            <option value="{{ $pos }}">{{ $pos }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Search and Quick Select -->
                                        <div class="input-group mb-3">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-search"></i>
                                            </span>
                                            <input type="text" id="staffSearch" class="form-control" placeholder="Search staff by name, code, or position...">
                                            <button class="btn btn-outline-primary" type="button" id="quickSelectDeptBtn">
                                                <i class="fas fa-users me-1"></i> Select Department
                                            </button>
                                        </div>

                                        <!-- Staff List with Checkboxes -->
                                        <div class="staff-list-container" style="max-height: 400px; overflow-y: auto; border: 1px solid #e9ecef; border-radius: 8px;">
                                            <table class="table table-hover mb-0" id="staffTable">
                                                <thead class="table-light sticky-top">
                                                    <tr>
                                                        <th width="40">
                                                            <input type="checkbox" id="selectAllCheckbox" class="form-check-input">
                                                        </th>
                                                        <th>Staff Name</th>
                                                        <th>Staff Code</th>
                                                        <th>Department</th>
                                                        <th>Position</th>
                                                        <th class="text-end">Basic Salary</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($availableStaff as $staffMember)
                                                        <tr class="staff-row" 
                                                            data-department="{{ $staffMember->department ?? 'Unassigned' }}"
                                                            data-position="{{ $staffMember->position ?? '' }}"
                                                            data-name="{{ strtolower($staffMember->first_name ?? '') }} {{ strtolower($staffMember->last_name ?? '') }}"
                                                            data-code="{{ strtolower($staffMember->staff_code ?? '') }}">
                                                            <td>
                                                                <input type="checkbox" name="staff_ids[]" value="{{ $staffMember->id }}" 
                                                                       class="form-check-input staff-checkbox" 
                                                                       id="staff_{{ $staffMember->id }}"
                                                                       {{ in_array($staffMember->id, $selectedStaff ?? []) ? 'checked' : '' }}>
                                                            </td>
                                                            <td>
                                                                <div class="fw-bold">{{ $staffMember->first_name ?? $staffMember->name }} {{ $staffMember->last_name ?? '' }}</div>
                                                                @if(isset($staffMember->staff_code))
                                                                    <small class="text-muted">ID: {{ $staffMember->staff_code }}</small>
                                                                @endif
                                                            </td>
                                                            <td>{{ $staffMember->staff_code ?? 'N/A' }}</td>
                                                            <td>
                                                                <span class="badge bg-info bg-opacity-10 text-info">
                                                                    {{ $staffMember->department ?? 'Unassigned' }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $staffMember->position ?? 'N/A' }}</td>
                                                            <td class="text-end">
                                                                ${{ number_format($staffMember->basic_salary ?? 0, 2) }}
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="6" class="text-center py-4">
                                                                <i class="fas fa-users fa-2x text-muted mb-2 d-block"></i>
                                                                <p class="text-muted mb-0">No available staff to assign.</p>
                                                                <small class="text-muted">All staff may already be assigned to this payroll period.</small>
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Selection Summary and Actions -->
                                        <div class="mt-3 p-3 bg-light rounded">
                                            <div class="row align-items-center">
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <span>
                                                            <i class="fas fa-check-circle text-success"></i>
                                                            <span id="selectedCount">0</span> staff selected
                                                        </span>
                                                        <span>
                                                            <i class="fas fa-users text-primary"></i>
                                                            <span id="totalVisibleCount">{{ $availableStaff->count() }}</span> visible
                                                        </span>
                                                        <span>
                                                            <i class="fas fa-user-check text-info"></i>
                                                            <span id="assignedCount">{{ $payrollPeriod->staff->count() }}</span> already assigned
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 text-end">
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-success" id="selectVisibleBtn">
                                                            <i class="fas fa-eye me-1"></i> Select Visible
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" id="deselectVisibleBtn">
                                                            <i class="fas fa-eye-slash me-1"></i> Deselect Visible
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Submit Buttons -->
                                        <div class="mt-3 d-flex gap-2">
                                            <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #1a3c5e 0%, #2a5f7a 100%); border: none;">
                                                <i class="fas fa-user-plus me-1"></i> Assign Selected Staff
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" id="previewBtn">
                                                <i class="fas fa-eye me-1"></i> Preview Selection
                                            </button>
                                            <button type="reset" class="btn btn-outline-danger">
                                                <i class="fas fa-undo me-1"></i> Reset
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Currently Assigned Staff -->
                            <div class="card" style="border: none; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                                <div class="card-body">
                                    <h6 class="text-uppercase" style="color: #6c757d; font-size: 12px; letter-spacing: 0.5px;">
                                        <i class="fas fa-user-check me-1"></i> Currently Assigned
                                        <span class="badge bg-success ms-2">{{ $payrollPeriod->staff->count() }}</span>
                                    </h6>
                                    <hr>
                                    
                                    @if($payrollPeriod->staff->count() > 0)
                                        <div class="assigned-staff-list" style="max-height: 400px; overflow-y: auto;">
                                            @foreach($payrollPeriod->staff as $staff)
                                                <div class="assigned-staff-item d-flex justify-content-between align-items-center p-2 mb-1" 
                                                     style="background: #f8f9fa; border-radius: 6px;">
                                                    <div>
                                                        <div class="fw-bold" style="font-size: 13px;">{{ $staff->name }}</div>
                                                        <div style="font-size: 11px; color: #6c757d;">
                                                            {{ $staff->position ?? 'N/A' }}
                                                            <span class="badge bg-light text-dark ms-1">{{ $staff->staff_code ?? 'N/A' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="fw-bold text-success" style="font-size: 13px;">
                                                            ${{ number_format($staff->pivot->net_pay ?? 0, 2) }}
                                                        </div>
                                                        <small class="text-muted" style="font-size: 10px;">
                                                            {{ $staff->pivot->worked_days ?? 0 }} days
                                                        </small>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="fas fa-user-slash fa-2x text-muted mb-2 d-block"></i>
                                            <p class="text-muted mb-0">No staff assigned yet.</p>
                                            <small class="text-muted">Staff will appear here after assignment.</small>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Quick Stats -->
                            <div class="card mt-3" style="border: none; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                                <div class="card-body">
                                    <h6 class="text-uppercase" style="color: #6c757d; font-size: 12px; letter-spacing: 0.5px;">
                                        <i class="fas fa-chart-bar me-1"></i> Quick Stats
                                    </h6>
                                    <hr>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="p-2 bg-primary bg-opacity-10 rounded text-center">
                                                <div class="small text-muted">Available</div>
                                                <div class="fw-bold text-primary">{{ $availableStaff->count() }}</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-2 bg-success bg-opacity-10 rounded text-center">
                                                <div class="small text-muted">Assigned</div>
                                                <div class="fw-bold text-success">{{ $payrollPeriod->staff->count() }}</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-2 bg-warning bg-opacity-10 rounded text-center">
                                                <div class="small text-muted">Pending</div>
                                                <div class="fw-bold text-warning">{{ max(0, $availableStaff->count() - $payrollPeriod->staff->count()) }}</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-2 bg-info bg-opacity-10 rounded text-center">
                                                <div class="small text-muted">Total</div>
                                                <div class="fw-bold text-info">{{ $availableStaff->count() + $payrollPeriod->staff->count() }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1a3c5e 0%, #2a5f7a 100%); color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i> Staff Selection Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent">
                    <p class="text-muted">No staff selected.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="confirmAssignBtn">
                    <i class="fas fa-user-plus me-1"></i> Confirm Assignment
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .staff-list-container::-webkit-scrollbar {
        width: 6px;
    }
    .staff-list-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 8px;
    }
    .staff-list-container::-webkit-scrollbar-thumb {
        background: #c1c7cd;
        border-radius: 8px;
    }
    .staff-list-container::-webkit-scrollbar-thumb:hover {
        background: #a8b0b8;
    }
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .assigned-staff-item:hover {
        background: #e9ecef !important;
        transition: background 0.2s;
    }
    .staff-row:hover {
        background-color: #f8f9fa;
        cursor: pointer;
    }
    .staff-row.selected {
        background-color: #e3f2fd !important;
    }
    .staff-row .form-check-input:checked {
        background-color: #1a3c5e;
        border-color: #1a3c5e;
    }
    .select-all-actions .btn {
        font-size: 12px;
        padding: 4px 12px;
    }
    .badge.bg-opacity-10 {
        background-color: rgba(13, 110, 253, 0.1) !important;
    }
    .table th {
        font-size: 12px;
        font-weight: 600;
        color: #495057;
        border-bottom: 2px solid #dee2e6;
    }
    .table td {
        font-size: 13px;
        vertical-align: middle;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Track selected state
    let selectedCount = 0;
    
    // Initialize select all checkbox
    $('#selectAllCheckbox').on('change', function() {
        const isChecked = $(this).prop('checked');
        $('.staff-checkbox:visible').prop('checked', isChecked);
        updateSelectionSummary();
    });

    // Individual checkbox change
    $(document).on('change', '.staff-checkbox', function() {
        updateSelectionSummary();
        updateRowSelection($(this));
    });

    // Update row selection styling
    function updateRowSelection($checkbox) {
        const $row = $checkbox.closest('tr');
        if ($checkbox.prop('checked')) {
            $row.addClass('selected');
        } else {
            $row.removeClass('selected');
        }
    }

    // Update selection summary
    function updateSelectionSummary() {
        const totalVisible = $('.staff-checkbox:visible').length;
        const checked = $('.staff-checkbox:visible:checked').length;
        selectedCount = checked;
        
        $('#selectedCount').text(checked);
        $('#totalVisibleCount').text(totalVisible);
        
        // Update select all checkbox state
        const allChecked = totalVisible > 0 && checked === totalVisible;
        $('#selectAllCheckbox').prop('checked', allChecked);
        $('#selectAllCheckbox').prop('indeterminate', checked > 0 && checked < totalVisible);
    }

    // Select All button
    $('#selectAllBtn').on('click', function() {
        $('.staff-checkbox:visible').prop('checked', true);
        $('.staff-checkbox:visible').closest('tr').addClass('selected');
        updateSelectionSummary();
    });

    // Deselect All button
    $('#deselectAllBtn').on('click', function() {
        $('.staff-checkbox:visible').prop('checked', false);
        $('.staff-checkbox:visible').closest('tr').removeClass('selected');
        updateSelectionSummary();
    });

    // Select Visible button
    $('#selectVisibleBtn').on('click', function() {
        $('.staff-checkbox:visible').prop('checked', true);
        $('.staff-checkbox:visible').closest('tr').addClass('selected');
        updateSelectionSummary();
        showToast('info', 'Selected all visible staff');
    });

    // Deselect Visible button
    $('#deselectVisibleBtn').on('click', function() {
        $('.staff-checkbox:visible').prop('checked', false);
        $('.staff-checkbox:visible').closest('tr').removeClass('selected');
        updateSelectionSummary();
        showToast('info', 'Deselected all visible staff');
    });

    // Department Filter
    $('#departmentFilter').on('change', function() {
        const dept = $(this).val();
        filterStaff();
    });

    // Position Filter
    $('#positionFilter').on('change', function() {
        const pos = $(this).val();
        filterStaff();
    });

    // Staff Search
    $('#staffSearch').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        filterStaff();
    });

    // Combined filter function
    function filterStaff() {
        const dept = $('#departmentFilter').val().toLowerCase();
        const pos = $('#positionFilter').val().toLowerCase();
        const search = $('#staffSearch').val().toLowerCase();

        $('.staff-row').each(function() {
            const $row = $(this);
            const rowDept = $row.data('department').toLowerCase();
            const rowPos = $row.data('position').toLowerCase();
            const rowName = $row.data('name');
            const rowCode = $row.data('code');
            
            let show = true;
            
            if (dept && rowDept !== dept) show = false;
            if (pos && rowPos !== pos) show = false;
            if (search && !rowName.includes(search) && !rowCode.includes(search)) show = false;
            
            $row.toggle(show);
            // Update checkbox visibility
            $row.find('.staff-checkbox').prop('hidden', !show);
        });
        
        updateSelectionSummary();
    }

    // Quick select by department
    $('#quickSelectDeptBtn').on('click', function() {
        const dept = $('#departmentFilter').val();
        if (!dept) {
            showToast('warning', 'Please select a department filter first');
            return;
        }
        
        const count = $('.staff-row:visible').length;
        if (count === 0) {
            showToast('warning', 'No staff found in this department');
            return;
        }
        
        $('.staff-row:visible .staff-checkbox').prop('checked', true);
        $('.staff-row:visible').addClass('selected');
        updateSelectionSummary();
        showToast('success', `Selected ${count} staff from ${dept} department`);
    });

    // Preview selection
    $('#previewBtn').on('click', function() {
        const selected = $('.staff-checkbox:checked');
        const count = selected.length;
        
        if (count === 0) {
            showToast('warning', 'No staff selected to preview');
            return;
        }
        
        let html = `<div class="alert alert-info">
            <i class="fas fa-info-circle me-1"></i> 
            <strong>${count}</strong> staff member(s) will be assigned.
        </div>
        <div class="list-group">`;
        
        selected.each(function() {
            const $row = $(this).closest('tr');
            const name = $row.find('td:eq(1) .fw-bold').text().trim();
            const dept = $row.find('td:eq(3) .badge').text().trim();
            const position = $row.find('td:eq(4)').text().trim();
            
            html += `<div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong>${name}</strong>
                    <small class="text-muted ms-2">${position}</small>
                </div>
                <span class="badge bg-secondary">${dept}</span>
            </div>`;
        });
        
        html += '</div>';
        
        $('#previewContent').html(html);
        $('#previewModal').modal('show');
    });

    // Confirm assignment from preview
    $('#confirmAssignBtn').on('click', function() {
        $('#previewModal').modal('hide');
        setTimeout(() => {
            $('#assignStaffForm').submit();
        }, 300);
    });

    // Toast notification
    function showToast(type, message) {
        const colors = {
            success: '#d4edda',
            warning: '#fff3cd',
            info: '#d1ecf1',
            error: '#f8d7da'
        };
        
        const icons = {
            success: 'fa-check-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle',
            error: 'fa-times-circle'
        };
        
        const toast = $(`
            <div class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true" 
                 style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; background: ${colors[type]}; color: #333;">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas ${icons[type]} me-2"></i> ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);
        
        $('body').append(toast);
        const toastInstance = new bootstrap.Toast(toast, { delay: 3000 });
        toastInstance.show();
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // Click on row to toggle checkbox
    $('.staff-row').on('click', function(e) {
        // Don't toggle if clicking on checkbox directly
        if ($(e.target).is('input[type="checkbox"]') || $(e.target).closest('input[type="checkbox"]').length) {
            return;
        }
        
        const $checkbox = $(this).find('.staff-checkbox');
        $checkbox.prop('checked', !$checkbox.prop('checked'));
        updateRowSelection($checkbox);
        updateSelectionSummary();
    });

    // Initial selection summary
    updateSelectionSummary();

    // Reset form
    $('button[type="reset"]').on('click', function(e) {
        e.preventDefault();
        $('.staff-checkbox').prop('checked', false);
        $('.staff-row').removeClass('selected');
        $('#departmentFilter').val('');
        $('#positionFilter').val('');
        $('#staffSearch').val('');
        filterStaff();
        showToast('info', 'Selection has been reset');
    });

    // Form validation before submit
    $('#assignStaffForm').on('submit', function(e) {
        const selected = $('.staff-checkbox:checked');
        if (selected.length === 0) {
            e.preventDefault();
            showToast('error', 'Please select at least one staff member to assign');
            return false;
        }
        
        // Show loading state
        const $btn = $(this).find('button[type="submit"]');
        const originalHtml = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Assigning...');
        $btn.prop('disabled', true);
        
        // Re-enable after form submission
        setTimeout(() => {
            $btn.html(originalHtml);
            $btn.prop('disabled', false);
        }, 5000);
    });

    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl+A to select all visible
        if (e.ctrlKey && e.key === 'a') {
            e.preventDefault();
            $('#selectAllBtn').click();
        }
        // Ctrl+D to deselect all
        if (e.ctrlKey && e.key === 'd') {
            e.preventDefault();
            $('#deselectAllBtn').click();
        }
    });

    // Export selected staff (optional feature)
    function exportSelectedStaff() {
        const selected = $('.staff-checkbox:checked');
        if (selected.length === 0) {
            showToast('warning', 'No staff selected to export');
            return;
        }
        
        const data = [];
        selected.each(function() {
            const $row = $(this).closest('tr');
            data.push({
                name: $row.find('td:eq(1) .fw-bold').text().trim(),
                code: $row.find('td:eq(2)').text().trim(),
                department: $row.find('td:eq(3) .badge').text().trim(),
                position: $row.find('td:eq(4)').text().trim(),
                salary: $row.find('td:eq(5)').text().trim()
            });
        });
        
        console.log('Selected Staff Data:', data);
        // You can implement export to CSV here
    }

    console.log('Assign Staff Page Loaded');
    console.log('Available Staff:', {{ $availableStaff->count() }});
    console.log('Already Assigned:', {{ $payrollPeriod->staff->count() }});
});
</script>
@endpush