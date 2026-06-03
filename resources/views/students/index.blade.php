@extends('layouts.master')

@section('title', 'Students')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 mt-3">
        <div>
            <h5 class="mb-0 fw-bold mt-3">
                <i class="fas fa-users me-2 text-danger"></i>
                Students List
            </h5>
        </div>

        <a href="{{ route('students.create') }}" class="btn btn-white text-dark shadow-sm">
            <i class="fas fa-plus me-1"></i>
            Add Student
        </a>
    </div>

    {{-- FLASH MESSAGES --}}
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

    {{-- SEARCH AND FILTER CARD --}}
    <div class="card shadow-sm mb-2">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
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

                <div class="col-md-2">
                    <select id="genderFilter" class="form-select">
                        <option value="">All Genders</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select id="disabilityFilter" class="form-select">
                        <option value="">All</option>
                        <option value="yes">With Disability</option>
                        <option value="no">Without Disability</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select id="sortFilter" class="form-select">
                        <option value="name_asc">Name (A-Z)</option>
                        <option value="name_desc">Name (Z-A)</option>
                        <option value="id_asc">ID (Ascending)</option>
                        <option value="id_desc">ID (Descending)</option>
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
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

    {{-- STUDENTS TABLE --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2 text-danger"></i>
                    Student Records
                    <span id="visibleCount" class="badge bg-dark ms-2">{{ $students->count() }}</span>
                </h5>
                <div class="d-flex gap-2">
                    <button onclick="exportToCSV()" class="btn btn-sm btn-success">
                        <i class="fas fa-download me-1"></i> Export CSV
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0" id="studentsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                            </th>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Gender</th>
                            <th>Date of Birth</th>
                            <th>Nationality</th>
                            <th>Religion</th>
                            <th>Address</th>
                            <th>Admission Date</th>
                            <th>Disability</th>
                            <th>Disability Type</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="studentsTableBody">
                        @forelse($students as $index => $student)
                            <tr class="student-row"
                                data-id="{{ $student->id }}"
                                data-student-id="{{ $student->student_id }}"
                                data-name="{{ strtolower($student->first_name . ' ' . $student->last_name) }}"
                                data-gender="{{ $student->gender }}"
                                data-nationality="{{ strtolower($student->nationality ?? '') }}"
                                data-disability="{{ $student->has_disability ? 'yes' : 'no' }}"
                                data-dob="{{ $student->date_of_birth }}"
                                data-created="{{ $student->created_at }}">
                                <td><input type="checkbox" class="student-checkbox" value="{{ $student->id }}"></td>
                                <td>{{ $student->student_id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <strong>{{ $student->first_name }} {{ $student->middle_name }} {{ $student->last_name }}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $student->gender }}</td>
                                <td>{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('M d, Y') : '-' }}</td>
                                <td>{{ $student->nationality ?? '-' }}</td>
                                <td>{{ $student->religion ?? '-' }}</td>
                                <td style="max-width: 200px;">{{ $student->address ?? '-' }}</td>
                                <td>{{ ($student->admission_date? \Carbon\Carbon::parse($student->admission_date)->format('M d, Y') : '-') }}</td>
                                <td>
                                    @if($student->has_disability)
                                        <span class="badge bg-warning text-dark">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td>{{ $student->disability_type ?? 'None' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('students.show', $student->id) }}" class="btn btn-white text-dark" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('students.edit', $student->id) }}" class="btn btn-white text-dark" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-white text-dark" onclick="deleteStudent({{ $student->id }})" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyRow">
                                <td colspan="11" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3 d-block"></i>
                                    No students found.
                                </td>
                            </tr>
                        @endforelse
                        <tr id="noResultsRow" style="display: none;">
                            <td colspan="11" class="text-center py-5 text-muted">
                                <i class="fas fa-search fa-3x mb-3 d-block"></i>
                                No matching students found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    {{ $students->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal (Single) --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-trash me-2"></i>Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this student?</p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Student</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeFilters();
    updateStats();
    setupSorting();
});

function initializeFilters() {
    const searchInput = document.getElementById('searchInput');
    const genderFilter = document.getElementById('genderFilter');
    const disabilityFilter = document.getElementById('disabilityFilter');

    if (searchInput) searchInput.addEventListener('keyup', debounce(applyFilters, 300));
    if (genderFilter) genderFilter.addEventListener('change', applyFilters);
    if (disabilityFilter) disabilityFilter.addEventListener('change', applyFilters);
    
    // Individual checkbox listeners
    document.querySelectorAll('.student-checkbox').forEach(cb => {
        cb.addEventListener('change', updateBulkDeleteButton);
    });
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
    const gender = document.getElementById('genderFilter')?.value || '';
    const disability = document.getElementById('disabilityFilter')?.value || '';
    const sortBy = document.getElementById('sortFilter')?.value || 'name_asc';

    const rows = Array.from(document.querySelectorAll('.student-row:not(#emptyRow)'));
    let visibleCount = 0;
    let maleCount = 0, femaleCount = 0, disabilityCount = 0;

    rows.forEach(row => {
        const name = row.dataset.name || '';
        const studentId = row.dataset.studentId || '';
        const nationality = row.dataset.nationality || '';
        const rowGender = row.dataset.gender || '';
        const rowDisability = row.dataset.disability || '';

        const matchesSearch = name.includes(searchTerm) || studentId.includes(searchTerm) || nationality.includes(searchTerm);
        const matchesGender = gender === '' || rowGender === gender;
        const matchesDisability = disability === '' || rowDisability === disability;

        if (matchesSearch && matchesGender && matchesDisability) {
            row.style.display = '';
            visibleCount++;
            
            if (rowGender === 'Male') maleCount++;
            else if (rowGender === 'Female') femaleCount++;
            if (rowDisability === 'yes') disabilityCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Update counts
    document.getElementById('totalCount').innerText = visibleCount;
    document.getElementById('maleCount').innerText = maleCount;
    document.getElementById('femaleCount').innerText = femaleCount;
    document.getElementById('disabilityCount').innerText = disabilityCount;
    document.getElementById('visibleCount').innerText = visibleCount;

    // Sort the visible rows
    sortRows(rows.filter(row => row.style.display !== 'none'), sortBy);

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

    updateActiveFilters({searchTerm, gender, disability});
}

function sortRows(rows, sortBy) {
    const tbody = document.getElementById('studentsTableBody');
    if (!tbody || rows.length === 0) return;

    rows.sort((a, b) => {
        switch(sortBy) {
            case 'name_asc':
                return a.dataset.name.localeCompare(b.dataset.name);
            case 'name_desc':
                return b.dataset.name.localeCompare(a.dataset.name);
            case 'id_asc':
                return a.dataset.studentId.localeCompare(b.dataset.studentId);
            case 'id_desc':
                return b.dataset.studentId.localeCompare(a.dataset.studentId);
            case 'newest':
                return new Date(b.dataset.created) - new Date(a.dataset.created);
            case 'oldest':
                return new Date(a.dataset.created) - new Date(b.dataset.created);
            default:
                return 0;
        }
    });

    // Re-append sorted rows
    rows.forEach(row => tbody.appendChild(row));
}

function setupSorting() {
    const sortFilter = document.getElementById('sortFilter');
    if (sortFilter) sortFilter.addEventListener('change', applyFilters);
}

function updateActiveFilters(filters) {
    const container = document.getElementById('activeFilters');
    if (!container) return;
    
    container.innerHTML = '';
    
    if (filters.searchTerm && filters.searchTerm !== '') {
        addFilterBadge(container, 'Search: ' + filters.searchTerm, 'search');
    }
    if (filters.gender && filters.gender !== '') {
        addFilterBadge(container, 'Gender: ' + filters.gender, 'gender');
    }
    if (filters.disability && filters.disability !== '') {
        let text = filters.disability === 'yes' ? 'With Disability' : 'Without Disability';
        addFilterBadge(container, text, 'disability');
    }
}

function addFilterBadge(container, text, type) {
    const badge = document.createElement('span');
    badge.className = 'badge bg-light text-dark border px-3 py-2';
    badge.innerHTML = `${text} <i class="fas fa-times-circle ms-2 text-danger" style="cursor:pointer;" onclick="removeFilter('${type}')"></i>`;
    container.appendChild(badge);
}

function removeFilter(type) {
    switch(type) {
        case 'search':
            document.getElementById('searchInput').value = '';
            break;
        case 'gender':
            document.getElementById('genderFilter').value = '';
            break;
        case 'disability':
            document.getElementById('disabilityFilter').value = '';
            break;
    }
    applyFilters();
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('genderFilter').value = '';
    document.getElementById('disabilityFilter').value = '';
    document.getElementById('sortFilter').value = 'name_asc';
    
    const rows = document.querySelectorAll('.student-row');
    rows.forEach(row => row.style.display = '');
    document.getElementById('noResultsRow').style.display = 'none';
    document.getElementById('activeFilters').innerHTML = '';
    
    applyFilters();
    showToast('Filters reset successfully', 'info');
}

function updateStats() {
    const rows = document.querySelectorAll('.student-row');
    let male = 0, female = 0, disability = 0;
    
    rows.forEach(row => {
        if (row.dataset.gender === 'Male') male++;
        else if (row.dataset.gender === 'Female') female++;
        if (row.dataset.disability === 'yes') disability++;
    });
    
    document.getElementById('totalCount').innerText = rows.length;
    document.getElementById('maleCount').innerText = male;
    document.getElementById('femaleCount').innerText = female;
    document.getElementById('disabilityCount').innerText = disability;
    document.getElementById('visibleCount').innerText = rows.length;
}

function toggleSelectAll(selectAll) {
    const checkboxes = document.querySelectorAll('.student-checkbox');
    checkboxes.forEach(cb => {
        const row = cb.closest('tr');
        if (row && row.style.display !== 'none') {
            cb.checked = selectAll.checked;
        }
    });
    updateBulkDeleteButton();
}

function updateBulkDeleteButton() {
    const checkboxes = document.querySelectorAll('.student-checkbox:checked');
    const visibleCheckboxes = Array.from(checkboxes).filter(cb => {
        const row = cb.closest('tr');
        return row && row.style.display !== 'none';
    });
    const count = visibleCheckboxes.length;
    const btn = document.getElementById('bulkDeleteBtn');
    const selectedSpan = document.getElementById('selectedCount');
    
    if (count > 0) {
        btn.style.display = 'inline-block';
        selectedSpan.innerText = count;
    } else {
        btn.style.display = 'none';
    }
}

function deleteStudent(id) {
    const form = document.getElementById('deleteForm');
    form.action = `/students/${id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function bulkDelete() {
    const checkboxes = document.querySelectorAll('.student-checkbox:checked');
    const ids = Array.from(checkboxes).map(cb => cb.value);
    const count = ids.length;
    
    if (count === 0) {
        showToast('No students selected', 'warning');
        return;
    }
    
    document.getElementById('bulkCount').innerText = count;
    document.getElementById('bulkIds').value = JSON.stringify(ids);
    new bootstrap.Modal(document.getElementById('bulkDeleteModal')).show();
}

function exportToCSV() {
    const rows = document.querySelectorAll('.student-row:not([style*="display: none"])');
    if (rows.length === 0) {
        showToast('No data to export', 'warning');
        return;
    }
    
    let csv = [["Student ID", "Full Name", "Gender", "Date of Birth", "Nationality", "Religion", "Address", "Disability", "Disability Type"]];
    
    rows.forEach(row => {
        const studentId = row.cells[1]?.innerText.trim() || '';
        const fullName = row.cells[2]?.innerText.trim().split('\n')[0] || '';
        const gender = row.cells[3]?.innerText.trim() || '';
        const dob = row.cells[4]?.innerText.trim() || '';
        const nationality = row.cells[5]?.innerText.trim() || '';
        const religion = row.cells[6]?.innerText.trim() || '';
        const address = row.cells[7]?.innerText.trim() || '';
        const disability = row.cells[8]?.innerText.trim() || '';
        const disabilityType = row.cells[9]?.innerText.trim() || '';
        
        csv.push([`"${studentId}"`, `"${fullName}"`, `"${gender}"`, `"${dob}"`, `"${nationality}"`, `"${religion}"`, `"${address}"`, `"${disability}"`, `"${disabilityType}"`]);
    });
    
    const csvContent = csv.map(row => row.join(',')).join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `students_${new Date().toISOString().slice(0, 19)}.csv`;
    a.click();
    URL.revokeObjectURL(url);
    
    showToast('Data exported successfully!', 'success');
}

function printTable() {
    const printContent = document.getElementById('studentsTable').cloneNode(true);
    const originalTitle = document.title;
    document.title = 'Students Report';
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Students Report</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body { padding: 20px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    @media print { .no-print { display: none; } }
                </style>
            </head>
            <body>
                <div class="container-fluid">
                    <h3>Students Report</h3>
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

#activeFilters .badge {
    font-size: 12px;
    font-weight: normal;
}
</style>

@endsection