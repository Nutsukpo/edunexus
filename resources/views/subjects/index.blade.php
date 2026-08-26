@extends('layouts.master')

@section('title', 'Subjects')

@section('content')

<div class="container-fluid">

    {{-- Header Section --}}
    <div class="card bg-white text-dark border-0 shadow-sm mb-4 rounded-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="fw-bold mb-2">
                        <i class="fas fa-book-open me-2 text-primary"></i> Subject Management
                    </h4>
                    <p class="text-muted mb-0">
                        Manage all subjects, assign teachers, and organize curriculum
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('subjects.create') }}" class="btn btn-primary text-white">
                    <i class="fas fa-plus-circle me-1"></i> New Subject
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Subjects Table Card --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-list me-2 text-primary"></i> Subjects List
                        <span class="badge bg-white text-dark ms-2">{{ $subjects->count() }} Total</span>
                    </h5>
                </div>
                <div class="col-md-8">
                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                        <div class="position-relative">
                            <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="z-index: 10;"></i>
                            <input type="text" id="searchInput" class="form-control ps-5" placeholder="Search..." style="min-width: 250px;">
                        </div>
                        <div class="btn-group">
                            <button onclick="refreshData()" class="btn btn-outline-info" title="Refresh Data">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button onclick="exportToExcel()" class="btn btn-outline-success" title="Export to Excel">
                                <i class="fas fa-file-excel"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0" id="subjectsTable">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th width="50" class="text-center">#</th>
                            <th>Subject Name</th>
                            <th>Code</th>
                            <th>Education Level</th>
                            <th>Assigned Teacher</th>
                            <th width="140" class="text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody id="tableBody">
                        @forelse($subjects as $index => $subject)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $subject->name }}</strong>
                                    @if($subject->description)
                                        <br>
                                        <small class="text-muted">{{ Str::limit($subject->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <code class=" p-1 rounded">{{ $subject->code ?? '—' }}</code>
                                </td>
                                <td>
                                    <span">
                                        {{ $subject->education_level ?? '—' }}
                                    </span>
                                </td>
                                <td>
                                    @if($subject->staff)
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <strong>{{ $subject->staff->first_name ?? '' }} {{ $subject->staff->last_name ?? '' }}</strong>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">
                                            <i class="fas fa-user-slash me-1"></i> Not Assigned
                                        </span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('subjects.show', $subject->id) }}"
                                           class="btn btn-outline-white text-dark"
                                           data-bs-toggle="tooltip"
                                           title="View Subject">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <a href="{{ route('subjects.edit', $subject->id) }}"
                                           class="btn btn-white text-dark"
                                           data-bs-toggle="tooltip"
                                           title="Edit Subject">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="{{ route('subjects.destroy', $subject->id) }}"
                                              method="POST"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                    class="btn btn-outline-white text-dark"
                                                    data-bs-toggle="tooltip"
                                                    title="Delete Subject"
                                                    data-id="{{ $subject->id }}"
                                                    data-name="{{ $subject->name }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-book-open fa-3x text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted">No Subjects Found</h5>
                                    <p class="text-muted">Get started by adding your first subject</p>
                                    <a href="{{ route('subjects.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i> Add Subject
                                    </a>
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                    @if($subjects->count() > 0)
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="6" class="py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-database me-1"></i>
                                        Displaying all <strong id="totalCount">{{ $subjects->count() }}</strong> subjects from database
                                    </small>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        Last updated: {{ now()->format('d M Y, h:i A') }}
                                    </small>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                    @endif

                </table>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
    // Store original data for filtering
    let allSubjects = [];
    
    // Initialize tooltips and data
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Store all rows data
        storeAllRows();
        
        initializeSearch();
        initializeDeleteButtons();
    });
    
    // Store all rows for efficient filtering
    function storeAllRows() {
        const tbody = document.getElementById('tableBody');
        const rows = tbody.getElementsByTagName('tr');
        
        allSubjects = [];
        for (let row of rows) {
            // Skip empty state row
            if (row.querySelector('.fa-book-open')) continue;
            if (row.cells.length < 6) continue;
            
            allSubjects.push({
                element: row,
                name: row.cells[1]?.innerText.toLowerCase() || '',
                code: row.cells[2]?.innerText.toLowerCase() || '',
                teacher: row.cells[4]?.innerText.toLowerCase() || ''
            });
        }
    }
    
    // Search functionality
    function initializeSearch() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                
                let visibleCount = 0;
                
                for (let subject of allSubjects) {
                    if (subject.name.includes(searchTerm) || 
                        subject.code.includes(searchTerm) || 
                        subject.teacher.includes(searchTerm)) {
                        subject.element.style.display = '';
                        visibleCount++;
                    } else {
                        subject.element.style.display = 'none';
                    }
                }
                
                // Update footer count
                updateFooterCount(visibleCount, allSubjects.length);
                
                // Show/hide no results message
                const existingNoData = document.getElementById('noSearchResults');
                if (visibleCount === 0 && allSubjects.length > 0) {
                    if (!existingNoData) {
                        const tbody = document.getElementById('tableBody');
                        const noDataRow = document.createElement('tr');
                        noDataRow.id = 'noSearchResults';
                        noDataRow.innerHTML = `
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-search fa-3x text-muted mb-3 d-block"></i>
                                <h5 class="text-muted">No matching subjects found</h5>
                                <p class="text-muted">Try a different search term or clear the search box</p>
                                <button onclick="clearSearch()" class="btn btn-outline-primary mt-2">
                                    <i class="fas fa-times me-1"></i> Clear Search
                                </button>
                            </td>
                        `;
                        tbody.appendChild(noDataRow);
                    }
                } else if (existingNoData) {
                    existingNoData.remove();
                }
            });
        }
    }
    
    function updateFooterCount(visible, total) {
        const footer = document.querySelector('#subjectsTable tfoot');
        if (footer) {
            const countCell = footer.querySelector('td');
            if (countCell) {
                if (visible !== total) {
                    countCell.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-filter me-1"></i>
                                Showing <strong>${visible}</strong> of <strong>${total}</strong> subjects
                            </small>
                            <button onclick="clearSearch()" class="btn btn-sm btn-link">
                                <i class="fas fa-times me-1"></i> Clear Filter
                            </button>
                        </div>
                    `;
                } else {
                    countCell.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-database me-1"></i>
                                Displaying all <strong>${total}</strong> subjects from database
                            </small>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                Last updated: {{ now()->format('d M Y, h:i A') }}
                            </small>
                        </div>
                    `;
                }
            }
        }
    }
    
    function clearSearch() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('keyup'));
        }
    }
    
    function refreshData() {
        Swal.fire({
            title: 'Refreshing...',
            text: 'Reloading subject data',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        window.location.reload();
    }
    
    // Delete confirmation with SweetAlert
    function initializeDeleteButtons() {
        const deleteButtons = document.querySelectorAll('.delete-btn');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const subjectName = this.getAttribute('data-name');
                const form = this.closest('form');
                
                Swal.fire({
                    title: 'Delete Subject?',
                    html: `Are you sure you want to delete <strong>${subjectName}</strong>?<br><small class="text-muted">This action cannot be undone.</small>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        form.submit();
                    }
                });
            });
        });
    }
    
    // Export to Excel
    function exportToExcel() {
        const table = document.getElementById('subjectsTable');
        
        Swal.fire({
            title: 'Exporting...',
            text: 'Preparing your Excel file',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        try {
            // Clone the table to avoid modifying the original
            const cloneTable = table.cloneNode(true);
            
            // Remove action buttons from clone for cleaner export
            const actionCells = cloneTable.querySelectorAll('td:last-child, th:last-child');
            actionCells.forEach(cell => {
                if (cell.tagName === 'TD') {
                    cell.innerHTML = '';
                }
            });
            
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.table_to_sheet(cloneTable, { raw: true });
            
            // Auto-size columns
            ws['!cols'] = [
                { wch: 8 },   // #
                { wch: 40 },  // Subject Name
                { wch: 15 },  // Code
                { wch: 18 },  // Level
                { wch: 35 }   // Teacher
            ];
            
            XLSX.utils.book_append_sheet(wb, ws, 'All Subjects');
            XLSX.writeFile(wb, `all_subjects_{{ date('Y-m-d') }}.xlsx`);
            
            Swal.fire({
                icon: 'success',
                title: 'Export Successful!',
                text: `Exported all ${allSubjects.length} subjects to Excel`,
                timer: 1500,
                showConfirmButton: false
            });
        } catch(error) {
            Swal.fire({
                icon: 'error',
                title: 'Export Failed',
                text: 'There was an error exporting the data',
            });
        }
    }
</script>
@endpush

@push('styles')
<style>
    @media print {
        .btn, .btn-group, .search-box, .export-buttons {
            display: none !important;
        }
        .table {
            font-size: 10pt !important;
        }
    }
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
    }
</style>
@endpush