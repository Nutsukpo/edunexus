@extends('layouts.master')

@section('title', 'Student Classes')

@section('content')

<div class="container-fluid">
    
    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 mt-3">
        <div>
            <h5 class="fw-bold mb-1">
                <i class="fas fa-school me-2 text-danger"></i>Class/Forms
            </h5>
        </div>
        <a href="{{ route('student-classes.create') }}" class="btn btn-white text-dark">
            <i class="fas fa-plus-circle me-1"></i> Add New Class
        </a>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- SEARCH AND FILTER CARD --}}
    <div class="card mb-1">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text"
                           id="searchInput"
                           class="form-control"
                           placeholder="Search "
                           autocomplete="off">
                </div>
            </div>  
        </div>
    </div>

    {{-- CLASSES TABLE --}}
    <div class="card">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">
                <i class="fas fa-list me-2 text-danger"></i>
                Class Records
                <span id="visibleCount" class="badge bg-dark ms-2">{{ $classes->count() }}</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="classesTable">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Class Code</th>
                            <th>Education Type</th>
                            <th>Class Type</th>
                            <th>Stream</th>
                            <th>Students</th>
                            <th>Capacity</th>
                            <th>Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="classesTableBody">
                        @forelse($classes as $index => $class)
                            @php
                                $studentCount = $class->assignments->where('is_current', true)->count();
                                $capacityPercent = $class->capacity > 0 ? round(($studentCount / $class->capacity) * 100) : 0;
                            @endphp
                            <tr class="class-row"
                                data-name="{{ strtolower($class->name) }}"
                                data-code="{{ strtolower($class->student_class_code) }}"
                                data-education="{{ $class->education_type }}"
                                data-class-type="{{ $class->class_type }}"
                                data-status="{{ $class->is_active ? 'active' : 'inactive' }}">
                                <td>{{ $class->name }}</td>
                                <td>
                                    <span class="badge bg-white text-dark">{{ $class->student_class_code }}</span>
                                </td>
                                <td>{{ $class->education_type }}</td>
                                <td>{{ $class->class_type }}</td>
                                <td>{{ $class->stream ?? '-' }}</td>
                                <td>
                                    {{ $studentCount }} / {{ $class->capacity }}
                                    <br>
                                    <small class="text-muted">{{ $capacityPercent }}% full</small>
                                </td>
                                <td>{{ $class->capacity }} Students</td>
                                <td>
                                    @if($class->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('student-classes.show', $class->id) }}"
                                           class="btn btn-outline-white text-dark" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('student-classes.edit', $class->id) }}"
                                           class="btn btn-outline-white text-dark" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('student-classes.destroy', $class->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Are you sure you want to delete this class?')"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-white text-dark" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyRow">
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="fas fa-school fs-1 mb-3 d-block"></i>
                                    No classes found. Click "Add New Class" to create one.
                                </td>
                            </tr>
                        @endforelse
                        <tr id="noResultsRow" style="display: none;">
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="fas fa-search fa-2x mb-3 d-block"></i>
                                No matching classes found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3">
            <div class="text-muted small">
                Showing <span id="showingCount">{{ $classes->count() }}</span> of <span id="totalRecords">{{ $classes->count() }}</span> classes
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeFilters();
    calculateAvgCapacity();
});

function initializeFilters() {
    const searchInput = document.getElementById('searchInput');
    const educationFilter = document.getElementById('educationFilter');
    const classTypeFilter = document.getElementById('classTypeFilter');
    const statusFilter = document.getElementById('statusFilter');

    if (searchInput) searchInput.addEventListener('keyup', debounce(applyFilters, 300));
    if (educationFilter) educationFilter.addEventListener('change', applyFilters);
    if (classTypeFilter) classTypeFilter.addEventListener('change', applyFilters);
    if (statusFilter) statusFilter.addEventListener('change', applyFilters);
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
    const education = document.getElementById('educationFilter')?.value || '';
    const classType = document.getElementById('classTypeFilter')?.value || '';
    const status = document.getElementById('statusFilter')?.value || '';

    const rows = Array.from(document.querySelectorAll('.class-row:not(#emptyRow)'));
    let visibleCount = 0;
    let activeCount = 0;

    rows.forEach(row => {
        const name = row.dataset.name || '';
        const code = row.dataset.code || '';
        const rowEducation = row.dataset.education || '';
        const rowClassType = row.dataset.classType || '';
        const rowStatus = row.dataset.status || '';

        const matchesSearch = name.includes(searchTerm) || code.includes(searchTerm);
        const matchesEducation = education === '' || rowEducation === education;
        const matchesClassType = classType === '' || rowClassType === classType;
        const matchesStatus = status === '' || rowStatus === status;

        if (matchesSearch && matchesEducation && matchesClassType && matchesStatus) {
            row.style.display = '';
            visibleCount++;
            if (rowStatus === 'active') activeCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Update counts
    document.getElementById('totalCount').innerText = visibleCount;
    document.getElementById('activeCount').innerText = activeCount;
    document.getElementById('visibleCount').innerText = visibleCount;
    document.getElementById('showingCount').innerText = visibleCount;
    document.getElementById('totalRecords').innerText = visibleCount;

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
    
    calculateAvgCapacity();
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('educationFilter').value = '';
    document.getElementById('classTypeFilter').value = '';
    document.getElementById('statusFilter').value = '';
    
    const rows = document.querySelectorAll('.class-row');
    rows.forEach(row => row.style.display = '');
    document.getElementById('noResultsRow').style.display = 'none';
    
    applyFilters();
    showToast('Filters reset successfully', 'info');
}

function calculateAvgCapacity() {
    const rows = document.querySelectorAll('.class-row:not([style*="display: none"])');
    let totalPercent = 0;
    let count = 0;
    
    rows.forEach(row => {
        const studentsText = row.cells[5]?.innerText || '';
        const match = studentsText.match(/(\d+)\s*\/\s*(\d+)/);
        if (match) {
            const students = parseInt(match[1]);
            const capacity = parseInt(match[2]);
            if (capacity > 0) {
                totalPercent += (students / capacity) * 100;
                count++;
            }
        }
    });
    
    const avgPercent = count > 0 ? Math.round(totalPercent / count) : 0;
    document.getElementById('avgCapacity').innerText = avgPercent + '%';
}

function exportToCSV() {
    const rows = document.querySelectorAll('.class-row:not([style*="display: none"])');
    if (rows.length === 0) {
        showToast('No data to export', 'warning');
        return;
    }
    
    let csv = [["Name", "Code", "Education Type", "Class Type", "Stream", "Students", "Capacity", "Status"]];
    
    rows.forEach(row => {
        const name = row.cells[0]?.innerText.trim() || '';
        const code = row.cells[1]?.innerText.trim() || '';
        const education = row.cells[2]?.innerText.trim() || '';
        const classType = row.cells[3]?.innerText.trim() || '';
        const stream = row.cells[4]?.innerText.trim() || '';
        const students = row.cells[5]?.innerText.trim().split('\n')[0] || '';
        const capacity = row.cells[6]?.innerText.trim() || '';
        const status = row.cells[7]?.innerText.trim() || '';
        
        csv.push([`"${name}"`, `"${code}"`, `"${education}"`, `"${classType}"`, `"${stream}"`, `"${students}"`, `"${capacity}"`, `"${status}"`]);
    });
    
    const csvContent = csv.map(row => row.join(',')).join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `classes_${new Date().toISOString().slice(0, 19)}.csv`;
    a.click();
    URL.revokeObjectURL(url);
    
    showToast('Data exported successfully!', 'success');
}

function printTable() {
    const originalTitle = document.title;
    document.title = 'Classes Report';
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Classes Report</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body { padding: 20px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    @media print {
                        .btn { display: none; }
                    }
                </style>
            </head>
            <body>
                <div class="container-fluid">
                    <h3>Classes Report</h3>
                    <p>Generated: ${new Date().toLocaleString()}</p>
                    <hr>
                    <div class="table-responsive">
                        ${document.getElementById('classesTable').outerHTML}
                    </div>
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

@endsection