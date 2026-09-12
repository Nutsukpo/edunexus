@extends('layouts.master')

@section('title', 'Student Classes')

@section('content')

@php
    $user = auth()->user();
    $authorization = app(\App\Services\EdunexusAuthorizationService::class);
@endphp

<div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 mt-3">
        <div>
            <h5 class="fw-bold mb-1">
                <i class="fas fa-school me-2 text-primary"></i>
                Class/Forms
            </h5>

            @if($authorization->isScopedStaff($user))
                <small class="text-muted">
                    Showing classes assigned to you.
                </small>
            @endif
        </div>

        @can('classes.create')
            <a href="{{ route('student-classes.create') }}"
               class="btn btn-primary text-white">
                <i class="fas fa-plus-circle me-1"></i>
                Add New Class
            </a>
        @endcan
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- SEARCH --}}
    <div class="card mb-1">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="searchInput"
                           class="form-label small text-muted mb-1">
                        Search Classes
                    </label>

                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-search text-muted"></i>
                        </span>

                        <input type="text"
                               id="searchInput"
                               class="form-control"
                               placeholder="Search by class name or code..."
                               autocomplete="off">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CLASSES TABLE --}}
    <div class="card">

        <div class="card-header bg-white py-3">
            <h5 class="mb-0">
                <i class="fas fa-list me-2 text-primary"></i>
                Class Records

                <span id="visibleCount"
                      class="badge bg-dark ms-2">
                    {{ $classes->count() }}
                </span>
            </h5>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover mb-0"
                       id="classesTable">

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

                        @forelse($classes as $class)

                            @php
                                /*
                                |--------------------------------------------------------------------------
                                | STUDENT COUNT
                                |--------------------------------------------------------------------------
                                |
                                | Keep the existing behavior used by the page.
                                |
                                */
                                $studentCount = $class->assignments
                                    ->where('is_current', true)
                                    ->count();

                                $capacityPercent = $class->capacity > 0
                                    ? round(
                                        ($studentCount / $class->capacity) * 100
                                    )
                                    : 0;

                                /*
                                |--------------------------------------------------------------------------
                                | AUTHORIZATION
                                |--------------------------------------------------------------------------
                                |
                                | The controller is the real security boundary.
                                | These checks only control which buttons are displayed.
                                |
                                */
                                $canViewClass = $authorization->canAccessClass(
                                    $user,
                                    $class->id
                                );

                                $canEditClass = $authorization->canEditClass(
                                    $user,
                                    $class->id
                                );

                                $canDeleteClass = $canEditClass &&
                                    $user->can('classes.delete');
                            @endphp

                            <tr class="class-row"
                                data-name="{{ strtolower($class->name ?? '') }}"
                                data-code="{{ strtolower($class->student_class_code ?? '') }}"
                                data-education="{{ strtolower($class->education_type ?? '') }}"
                                data-class-type="{{ strtolower($class->class_type ?? '') }}"
                                data-status="{{ $class->is_active ? 'active' : 'inactive' }}">

                                {{-- NAME --}}
                                <td>
                                    <span class="fw-semibold">
                                        {{ $class->name }}
                                    </span>
                                </td>

                                {{-- CODE --}}
                                <td>
                                    <span class="badge bg-white text-dark border">
                                        {{ $class->student_class_code }}
                                    </span>
                                </td>

                                {{-- EDUCATION TYPE --}}
                                <td>
                                    {{ $class->education_type }}
                                </td>

                                {{-- CLASS TYPE --}}
                                <td>
                                    {{ $class->class_type }}
                                </td>

                                {{-- STREAM --}}
                                <td>
                                    {{ $class->stream ?? '-' }}
                                </td>

                                {{-- STUDENTS --}}
                                <td>
                                    {{ $studentCount }} /
                                    {{ $class->capacity }}

                                    <br>

                                    <small class="text-muted">
                                        {{ $capacityPercent }}% full
                                    </small>
                                </td>

                                {{-- CAPACITY --}}
                                <td>
                                    {{ $class->capacity }}
                                    Students
                                </td>

                                {{-- STATUS --}}
                                <td>
                                    @if($class->is_active)
                                        <span class="badge bg-success">
                                            Active
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                {{-- ACTIONS --}}
                                <td>

                                    <div class="btn-group btn-group-sm">

                                        {{-- VIEW --}}
                                        @if($canViewClass)
                                            <a href="{{ route('student-classes.show', $class->id) }}"
                                               class="btn btn-outline-white text-dark"
                                               title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endif

                                        {{-- EDIT --}}
                                        @if($canEditClass && $user->can('classes.edit'))
                                            <a href="{{ route('student-classes.edit', $class->id) }}"
                                               class="btn btn-outline-white text-dark"
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif

                                        {{-- DELETE --}}
                                        @if($canDeleteClass)
                                            <form action="{{ route('student-classes.destroy', $class->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Are you sure you want to delete this class?')"
                                                  class="d-inline">

                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="btn btn-outline-white text-dark"
                                                        title="Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>

                                            </form>
                                        @endif

                                    </div>

                                    {{-- NO ACTION MESSAGE --}}
                                    @if(
                                        !$canViewClass &&
                                        !$canEditClass &&
                                        !$canDeleteClass
                                    )
                                        <span class="text-muted small">
                                            No access
                                        </span>
                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr id="emptyRow">
                                <td colspan="9"
                                    class="text-center text-muted py-5">

                                    <i class="fas fa-school fs-1 mb-3 d-block"></i>

                                    No classes found.

                                    @can('classes.create')
                                        <br>
                                        <span class="small">
                                            Click
                                            <strong>Add New Class</strong>
                                            to create one.
                                        </span>
                                    @endcan

                                </td>
                            </tr>

                        @endforelse

                        {{-- SEARCH NO RESULTS --}}
                        <tr id="noResultsRow"
                            style="display: none;">

                            <td colspan="9"
                                class="text-center text-muted py-5">

                                <i class="fas fa-search fa-2x mb-3 d-block"></i>

                                No matching classes found.

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

        {{-- FOOTER --}}
        <div class="card-footer bg-white py-3">

            <div class="text-muted small">

                Showing
                <span id="showingCount">
                    {{ $classes->count() }}
                </span>

                of

                <span id="totalRecords">
                    {{ $classes->count() }}
                </span>

                classes

            </div>

        </div>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    initializeFilters();

});


/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/
function initializeFilters() {

    const searchInput =
        document.getElementById('searchInput');

    if (searchInput) {

        searchInput.addEventListener(
            'input',
            debounce(applyFilters, 250)
        );

    }

}


/*
|--------------------------------------------------------------------------
| DEBOUNCE
|--------------------------------------------------------------------------
*/
function debounce(func, wait) {

    let timeout;

    return function (...args) {

        clearTimeout(timeout);

        timeout = setTimeout(
            () => func(...args),
            wait
        );

    };

}


/*
|--------------------------------------------------------------------------
| APPLY SEARCH
|--------------------------------------------------------------------------
*/
function applyFilters() {

    const searchTerm =
        document.getElementById('searchInput')
            ?.value
            .toLowerCase()
            .trim() || '';

    const rows =
        Array.from(
            document.querySelectorAll('.class-row')
        );

    let visibleCount = 0;

    rows.forEach(function (row) {

        const name =
            row.dataset.name || '';

        const code =
            row.dataset.code || '';

        const matchesSearch =
            searchTerm === '' ||
            name.includes(searchTerm) ||
            code.includes(searchTerm);

        if (matchesSearch) {

            row.style.display = '';
            visibleCount++;

        } else {

            row.style.display = 'none';

        }

    });


    /*
    |--------------------------------------------------------------------------
    | UPDATE COUNTS
    |--------------------------------------------------------------------------
    */

    const visibleCountElement =
        document.getElementById('visibleCount');

    const showingCountElement =
        document.getElementById('showingCount');

    const totalRecordsElement =
        document.getElementById('totalRecords');

    if (visibleCountElement) {
        visibleCountElement.innerText =
            visibleCount;
    }

    if (showingCountElement) {
        showingCountElement.innerText =
            visibleCount;
    }

    if (totalRecordsElement) {
        totalRecordsElement.innerText =
            rows.length;
    }


    /*
    |--------------------------------------------------------------------------
    | NO RESULTS
    |--------------------------------------------------------------------------
    */

    const noResultsRow =
        document.getElementById('noResultsRow');

    const emptyRow =
        document.getElementById('emptyRow');

    if (
        visibleCount === 0 &&
        rows.length > 0
    ) {

        if (noResultsRow) {
            noResultsRow.style.display = '';
        }

        if (emptyRow) {
            emptyRow.style.display = 'none';
        }

    } else {

        if (noResultsRow) {
            noResultsRow.style.display = 'none';
        }

        if (
            emptyRow &&
            rows.length === 0
        ) {
            emptyRow.style.display = '';
        }

    }

}


/*
|--------------------------------------------------------------------------
| RESET SEARCH
|--------------------------------------------------------------------------
*/
function resetFilters() {

    const searchInput =
        document.getElementById('searchInput');

    if (searchInput) {
        searchInput.value = '';
    }

    applyFilters();

}


/*
|--------------------------------------------------------------------------
| CSV EXPORT
|--------------------------------------------------------------------------
*/
function exportToCSV() {

    const rows =
        document.querySelectorAll(
            '.class-row:not([style*="display: none"])'
        );

    if (rows.length === 0) {

        showToast(
            'No data to export',
            'warning'
        );

        return;
    }


    let csv = [
        [
            'Name',
            'Code',
            'Education Type',
            'Class Type',
            'Stream',
            'Students',
            'Capacity',
            'Status'
        ]
    ];


    rows.forEach(function (row) {

        const name =
            row.cells[0]?.innerText.trim() || '';

        const code =
            row.cells[1]?.innerText.trim() || '';

        const education =
            row.cells[2]?.innerText.trim() || '';

        const classType =
            row.cells[3]?.innerText.trim() || '';

        const stream =
            row.cells[4]?.innerText.trim() || '';

        const students =
            row.cells[5]?.innerText
                .trim()
                .split('\n')[0] || '';

        const capacity =
            row.cells[6]?.innerText.trim() || '';

        const status =
            row.cells[7]?.innerText.trim() || '';


        csv.push([
            `"${name.replace(/"/g, '""')}"`,
            `"${code.replace(/"/g, '""')}"`,
            `"${education.replace(/"/g, '""')}"`,
            `"${classType.replace(/"/g, '""')}"`,
            `"${stream.replace(/"/g, '""')}"`,
            `"${students.replace(/"/g, '""')}"`,
            `"${capacity.replace(/"/g, '""')}"`,
            `"${status.replace(/"/g, '""')}"`
        ]);

    });


    const csvContent =
        csv
            .map(row => row.join(','))
            .join('\n');


    const blob =
        new Blob(
            [csvContent],
            {
                type: 'text/csv;charset=utf-8;'
            }
        );


    const url =
        URL.createObjectURL(blob);


    const a =
        document.createElement('a');

    a.href = url;

    a.download =
        `classes_${new Date()
            .toISOString()
            .slice(0, 19)
            .replace(/:/g, '-')}.csv`;

    document.body.appendChild(a);

    a.click();

    a.remove();

    URL.revokeObjectURL(url);


    showToast(
        'Data exported successfully!',
        'success'
    );

}


/*
|--------------------------------------------------------------------------
| PRINT
|--------------------------------------------------------------------------
*/
function printTable() {

    const printWindow =
        window.open(
            '',
            '_blank'
        );

    if (!printWindow) {

        showToast(
            'Unable to open print window. Please allow pop-ups.',
            'warning'
        );

        return;
    }


    printWindow.document.write(`
        <html>

        <head>

            <title>Classes Report</title>

            <link
                href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
                rel="stylesheet"
            >

            <style>

                body {
                    padding: 20px;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                }

                th,
                td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                }

                th {
                    background-color: #f2f2f2;
                }

                .btn-group {
                    display: none !important;
                }

                @media print {

                    .btn {
                        display: none !important;
                    }

                }

            </style>

        </head>

        <body>

            <div class="container-fluid">

                <h3>Classes Report</h3>

                <p>
                    Generated:
                    ${new Date().toLocaleString()}
                </p>

                <hr>

                ${document
                    .getElementById('classesTable')
                    .outerHTML}

            </div>

        </body>

        </html>
    `);


    printWindow.document.close();

    printWindow.focus();

    setTimeout(function () {

        printWindow.print();

        printWindow.close();

    }, 250);

}


/*
|--------------------------------------------------------------------------
| TOAST
|--------------------------------------------------------------------------
*/
function showToast(
    message,
    type = 'success'
) {

    const toast =
        document.createElement('div');

    toast.className =
        `alert alert-${
            type === 'error'
                ? 'danger'
                : type
        } alert-dismissible fade show position-fixed top-0 end-0 m-3`;

    toast.style.zIndex = '9999';

    toast.style.minWidth = '280px';


    const icon =
        type === 'success'
            ? 'check-circle'
            : type === 'error'
                ? 'exclamation-circle'
                : 'info-circle';


    toast.innerHTML = `
        <i class="fas fa-${icon} me-2"></i>
        ${message}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>
    `;


    document.body.appendChild(toast);


    setTimeout(function () {

        if (toast) {
            toast.remove();
        }

    }, 3000);

}

</script>

@endsection