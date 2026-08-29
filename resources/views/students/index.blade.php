@extends('layouts.master')

@section('title', 'Students')

@section('content')

<div class="container-fluid py-3">

    {{-- =========================================================
         PAGE HEADER
    ========================================================== --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

        <div>
            <h5 class="mb-1 fw-bold">
                <i class="fas fa-users text-primary me-2"></i>
                Students List
            </h5>

            <small class="text-muted">
                Manage student records
            </small>
        </div>

        <a href="{{ route('students.create') }}"
           class="btn btn-primary shadow-sm">

            <i class="fas fa-plus me-1"></i>
            Add Student

        </a>

    </div>


    {{-- =========================================================
         SUCCESS MESSAGE
    ========================================================== --}}
    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show shadow-sm"
             role="alert">

            <i class="fas fa-check-circle me-2"></i>

            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close">
            </button>

        </div>

    @endif


    {{-- =========================================================
         ERROR MESSAGE
    ========================================================== --}}
    @if(session('error'))

        <div class="alert alert-danger alert-dismissible fade show shadow-sm"
             role="alert">

            <i class="fas fa-exclamation-circle me-2"></i>

            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close">
            </button>

        </div>

    @endif


    {{-- =========================================================
         VALIDATION ERRORS
    ========================================================== --}}
    @if($errors->any())

        <div class="alert alert-danger alert-dismissible fade show shadow-sm"
             role="alert">

            <strong>
                <i class="fas fa-exclamation-triangle me-2"></i>
                Please correct the following:
            </strong>

            <ul class="mb-0 mt-2">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close">
            </button>

        </div>

    @endif


    {{-- =========================================================
         SEARCH AND FILTERS
    ========================================================== --}}
    <div class="card border-0 shadow-sm mb-3">

        <div class="card-body">

            <div class="row g-3">

                {{-- SEARCH --}}
                <div class="col-xl-4 col-lg-4 col-md-6">

                    <div class="input-group">

                        <span class="input-group-text bg-white">
                            <i class="fas fa-search text-muted"></i>
                        </span>

                        <input type="text"
                               id="searchInput"
                               class="form-control"
                               placeholder="Search by name, ID or nationality..."
                               autocomplete="off">

                    </div>

                </div>


                {{-- GENDER --}}
                <div class="col-xl-2 col-lg-2 col-md-3">

                    <select id="genderFilter"
                            class="form-select">

                        <option value="">All Genders</option>

                        <option value="Male">
                            Male
                        </option>

                        <option value="Female">
                            Female
                        </option>

                        <option value="Other">
                            Other
                        </option>

                    </select>

                </div>


                {{-- DISABILITY --}}
                <div class="col-xl-2 col-lg-2 col-md-3">

                    <select id="disabilityFilter"
                            class="form-select">

                        <option value="">
                            All Students
                        </option>

                        <option value="yes">
                            With Disability
                        </option>

                        <option value="no">
                            Without Disability
                        </option>

                    </select>

                </div>


                {{-- SORT --}}
                <div class="col-xl-2 col-lg-2 col-md-6">

                    <select id="sortFilter"
                            class="form-select">

                        <option value="name_asc">
                            Name (A-Z)
                        </option>

                        <option value="name_desc">
                            Name (Z-A)
                        </option>

                        <option value="id_asc">
                            ID (Ascending)
                        </option>

                        <option value="id_desc">
                            ID (Descending)
                        </option>

                        <option value="newest">
                            Newest First
                        </option>

                        <option value="oldest">
                            Oldest First
                        </option>

                    </select>

                </div>


                {{-- RESET --}}
                <div class="col-xl-2 col-lg-2 col-md-6">

                    <button type="button"
                            id="resetFiltersBtn"
                            class="btn btn-outline-secondary w-100">

                        <i class="fas fa-sync-alt me-1"></i>
                        Reset

                    </button>

                </div>

            </div>


            {{-- ACTIVE FILTERS --}}
            <div id="activeFilters"
                 class="d-flex flex-wrap gap-2 mt-3">
            </div>

        </div>

    </div>


    {{-- =========================================================
         STUDENT RECORDS CARD
    ========================================================== --}}
    <div class="card border-0 shadow-sm">

        {{-- CARD HEADER --}}
        <div class="card-header bg-white py-3">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                <h5 class="mb-0">

                    <i class="fas fa-list text-primary me-2"></i>

                    Student Records

                    <span id="visibleCount"
                          class="badge bg-dark ms-2">
                        {{ $students->count() }}
                    </span>

                </h5>


                <button type="button"
                        id="exportCsvBtn"
                        class="btn btn-sm btn-success">

                    <i class="fas fa-download me-1"></i>

                    Export CSV

                </button>

            </div>

        </div>


        {{-- =====================================================
             TABLE
        ====================================================== --}}
        <div class="card-body p-0">

            <div class="table-responsive">

                <table id="studentsTable"
                       class="table table-bordered table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th width="45">
                                <input type="checkbox"
                                       id="selectAll"
                                       class="form-check-input"
                                       title="Select all visible students">
                            </th>

                            <th>
                                Student ID
                            </th>

                            <th>
                                Full Name
                            </th>

                            <th>
                                Gender
                            </th>

                            <th>
                                Date of Birth
                            </th>

                            <th>
                                Nationality
                            </th>

                            <th>
                                Religion
                            </th>

                            <th>
                                Address
                            </th>

                            <th>
                                Admission Date
                            </th>

                            <th>
                                Disability
                            </th>

                            <th>
                                Disability Type
                            </th>

                            <th width="120">
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody id="studentsTableBody">

                    @forelse($students as $student)

                        @php

                            $fullName = trim(
                                $student->first_name . ' ' .
                                ($student->middle_name ?? '') . ' ' .
                                $student->last_name
                            );

                        @endphp


                        <tr class="student-row"

                            data-id="{{ $student->id }}"

                            data-student-id="{{ strtolower($student->student_id ?? '') }}"

                            data-name="{{ strtolower($fullName) }}"

                            data-gender="{{ $student->gender ?? '' }}"

                            data-nationality="{{ strtolower($student->nationality ?? '') }}"

                            data-disability="{{ $student->has_disability ? 'yes' : 'no' }}"

                            data-created="{{ optional($student->created_at)->timestamp ?? 0 }}">


                            {{-- CHECKBOX --}}
                            <td>

                                <input type="checkbox"
                                       class="form-check-input student-checkbox"
                                       value="{{ $student->id }}">

                            </td>


                            {{-- STUDENT ID --}}
                            <td>

                                <span class="fw-semibold">
                                    {{ $student->student_id }}
                                </span>

                            </td>


                            {{-- NAME --}}
                            <td>

                                <span class="fw-semibold">
                                    {{ $fullName }}
                                </span>

                            </td>


                            {{-- GENDER --}}
                            <td>
                                {{ $student->gender ?? '-' }}
                            </td>


                            {{-- DATE OF BIRTH --}}
                            <td>

                                @if($student->date_of_birth)

                                    {{ \Carbon\Carbon::parse($student->date_of_birth)->format('M d, Y') }}

                                @else

                                    -

                                @endif

                            </td>


                            {{-- NATIONALITY --}}
                            <td>
                                {{ $student->nationality ?? '-' }}
                            </td>


                            {{-- RELIGION --}}
                            <td>
                                {{ $student->religion ?? '-' }}
                            </td>


                            {{-- ADDRESS --}}
                            <td style="max-width:220px;">

                                <span class="d-inline-block text-truncate"
                                      style="max-width:210px;"
                                      title="{{ $student->address ?? '' }}">

                                    {{ $student->address ?? '-' }}

                                </span>

                            </td>


                            {{-- ADMISSION DATE --}}
                            <td>

                                @if($student->admission_date)

                                    {{ \Carbon\Carbon::parse($student->admission_date)->format('M d, Y') }}

                                @else

                                    -

                                @endif

                            </td>


                            {{-- DISABILITY --}}
                            <td>

                                @if($student->has_disability)

                                    <span class="badge bg-warning text-dark">
                                        Yes
                                    </span>

                                @else

                                    <span class="badge bg-light text-dark border">
                                        No
                                    </span>

                                @endif

                            </td>


                            {{-- DISABILITY TYPE --}}
                            <td>
                                {{ $student->disability_type ?? 'None' }}
                            </td>


                            {{-- ACTIONS --}}
                            <td>

                                <div class="btn-group btn-group-sm"
                                     role="group">

                                    {{-- VIEW --}}
                                    <a href="{{ route('students.show', $student->id) }}"
                                       class="btn btn-light border"
                                       title="View Student">

                                        <i class="fas fa-eye"></i>

                                    </a>


                                    {{-- EDIT --}}
                                    <a href="{{ route('students.edit', $student->id) }}"
                                       class="btn btn-light border"
                                       title="Edit Student">

                                        <i class="fas fa-edit"></i>

                                    </a>


                                    {{-- DELETE --}}
                                    @if(auth()->check() && auth()->user()->hasRole('Super Admin'))

                                        <form method="POST"
                                              action="{{ route('students.destroy', $student->id) }}"
                                              class="d-inline delete-student-form"
                                              data-student-id="{{ $student->student_id }}"
                                              data-student-name="{{ $fullName }}">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="btn btn-light border text-danger delete-student-btn"
                                                    title="Delete Student">

                                                <i class="fas fa-trash"></i>

                                            </button>

                                        </form>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr id="emptyRow">

                            <td colspan="12"
                                class="text-center py-5">

                                <div class="text-muted">

                                    <i class="fas fa-folder-open fa-3x mb-3 d-block"></i>

                                    <h6 class="fw-bold">
                                        No Students Found
                                    </h6>

                                    <p class="mb-0">
                                        There are currently no student records.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    @endforelse


                    {{-- NO FILTER RESULTS --}}
                    <tr id="noResultsRow"
                        style="display:none;">

                        <td colspan="12"
                            class="text-center py-5">

                            <div class="text-muted">

                                <i class="fas fa-search fa-3x mb-3 d-block"></i>

                                <h6 class="fw-bold">
                                    No Matching Students
                                </h6>

                                <p class="mb-0">
                                    Try changing your search or filters.
                                </p>

                            </div>

                        </td>

                    </tr>

                    </tbody>

                </table>

            </div>

        </div>


        {{-- =====================================================
             PAGINATION
        ====================================================== --}}
        @if($students->hasPages())

            <div class="card-footer bg-white">

                <div class="d-flex justify-content-center">

                    {{ $students->withQueryString()->links() }}

                </div>

            </div>

        @endif

    </div>

</div>



{{-- =============================================================
     JAVASCRIPT
============================================================= --}}
<script>

(function () {

    'use strict';


    /* ============================================================
       PAGE INITIALIZATION
    ============================================================ */

    document.addEventListener('DOMContentLoaded', function () {

        initializeFilters();

        initializeSelection();

        initializeDeleteConfirmation();

        updateVisibleCount();

    });



    /* ============================================================
       FILTER INITIALIZATION
    ============================================================ */

    function initializeFilters()
    {

        const searchInput =
            document.getElementById('searchInput');


        const genderFilter =
            document.getElementById('genderFilter');


        const disabilityFilter =
            document.getElementById('disabilityFilter');


        const sortFilter =
            document.getElementById('sortFilter');


        const resetButton =
            document.getElementById('resetFiltersBtn');


        if (searchInput) {

            searchInput.addEventListener(
                'input',
                debounce(applyFilters, 250)
            );

        }


        if (genderFilter) {

            genderFilter.addEventListener(
                'change',
                applyFilters
            );

        }


        if (disabilityFilter) {

            disabilityFilter.addEventListener(
                'change',
                applyFilters
            );

        }


        if (sortFilter) {

            sortFilter.addEventListener(
                'change',
                applyFilters
            );

        }


        if (resetButton) {

            resetButton.addEventListener(
                'click',
                resetFilters
            );

        }

    }



    /* ============================================================
       DEBOUNCE
    ============================================================ */

    function debounce(callback, delay)
    {

        let timeout;

        return function () {

            const context = this;

            const args = arguments;


            clearTimeout(timeout);


            timeout = setTimeout(function () {

                callback.apply(context, args);

            }, delay);

        };

    }



    /* ============================================================
       APPLY FILTERS
    ============================================================ */

    function applyFilters()
    {

        const searchInput =
            document.getElementById('searchInput');


        const genderFilter =
            document.getElementById('genderFilter');


        const disabilityFilter =
            document.getElementById('disabilityFilter');


        const sortFilter =
            document.getElementById('sortFilter');


        const search =
            searchInput
                ? searchInput.value.trim().toLowerCase()
                : '';


        const gender =
            genderFilter
                ? genderFilter.value
                : '';


        const disability =
            disabilityFilter
                ? disabilityFilter.value
                : '';


        const sort =
            sortFilter
                ? sortFilter.value
                : 'name_asc';


        const rows =
            Array.from(
                document.querySelectorAll('.student-row')
            );


        let visible = 0;


        rows.forEach(function (row) {

            const name =
                row.dataset.name || '';


            const studentId =
                row.dataset.studentId || '';


            const nationality =
                row.dataset.nationality || '';


            const rowGender =
                row.dataset.gender || '';


            const rowDisability =
                row.dataset.disability || '';


            const searchMatch =
                search === '' ||
                name.includes(search) ||
                studentId.includes(search) ||
                nationality.includes(search);


            const genderMatch =
                gender === '' ||
                rowGender === gender;


            const disabilityMatch =
                disability === '' ||
                rowDisability === disability;


            const shouldShow =
                searchMatch &&
                genderMatch &&
                disabilityMatch;


            row.style.display =
                shouldShow
                    ? ''
                    : 'none';


            if (shouldShow) {

                visible++;

            }

        });


        sortRows(sort);


        updateVisibleCount();


        updateActiveFilters(
            search,
            gender,
            disability
        );


        const noResults =
            document.getElementById('noResultsRow');


        if (noResults) {

            noResults.style.display =
                rows.length > 0 && visible === 0
                    ? ''
                    : 'none';

        }


        updateSelectAll();

    }



    /* ============================================================
       SORT
    ============================================================ */

    function sortRows(sort)
    {

        const tbody =
            document.getElementById(
                'studentsTableBody'
            );


        if (!tbody) {
            return;
        }


        const rows =
            Array.from(
                tbody.querySelectorAll(
                    '.student-row'
                )
            );


        rows.sort(function (a, b) {

            switch (sort) {

                case 'name_asc':

                    return (a.dataset.name || '')
                        .localeCompare(
                            b.dataset.name || ''
                        );


                case 'name_desc':

                    return (b.dataset.name || '')
                        .localeCompare(
                            a.dataset.name || ''
                        );


                case 'id_asc':

                    return (a.dataset.studentId || '')
                        .localeCompare(
                            b.dataset.studentId || '',
                            undefined,
                            {
                                numeric: true
                            }
                        );


                case 'id_desc':

                    return (b.dataset.studentId || '')
                        .localeCompare(
                            a.dataset.studentId || '',
                            undefined,
                            {
                                numeric: true
                            }
                        );


                case 'newest':

                    return Number(
                        b.dataset.created || 0
                    ) -
                    Number(
                        a.dataset.created || 0
                    );


                case 'oldest':

                    return Number(
                        a.dataset.created || 0
                    ) -
                    Number(
                        b.dataset.created || 0
                    );


                default:

                    return 0;

            }

        });


        rows.forEach(function (row) {

            tbody.appendChild(row);

        });


        const noResults =
            document.getElementById(
                'noResultsRow'
            );


        if (noResults) {

            tbody.appendChild(noResults);

        }

    }



    /* ============================================================
       UPDATE VISIBLE COUNT
    ============================================================ */

    function updateVisibleCount()
    {

        const rows =
            document.querySelectorAll(
                '.student-row'
            );


        let count = 0;


        rows.forEach(function (row) {

            if (row.style.display !== 'none') {

                count++;

            }

        });


        const counter =
            document.getElementById(
                'visibleCount'
            );


        if (counter) {

            counter.textContent = count;

        }

    }



    /* ============================================================
       ACTIVE FILTER BADGES
    ============================================================ */

    function updateActiveFilters(
        search,
        gender,
        disability
    )
    {

        const container =
            document.getElementById(
                'activeFilters'
            );


        if (!container) {
            return;
        }


        container.innerHTML = '';


        if (search) {

            addFilterBadge(
                container,
                'Search: ' + search,
                'search'
            );

        }


        if (gender) {

            addFilterBadge(
                container,
                'Gender: ' + gender,
                'gender'
            );

        }


        if (disability) {

            addFilterBadge(
                container,

                disability === 'yes'
                    ? 'With Disability'
                    : 'Without Disability',

                'disability'
            );

        }

    }



    /* ============================================================
       ADD FILTER BADGE
    ============================================================ */

    function addFilterBadge(
        container,
        text,
        type
    )
    {

        const badge =
            document.createElement('span');


        badge.className =
            'badge bg-light text-dark border px-3 py-2';


        badge.innerHTML =

            escapeHtml(text) +

            `

            <button type="button"
                    class="btn btn-sm border-0 bg-transparent p-0 ms-2 text-danger"
                    data-filter="${type}"
                    title="Remove filter">

                <i class="fas fa-times-circle"></i>

            </button>

            `;


        const button =
            badge.querySelector('button');


        if (button) {

            button.addEventListener(
                'click',
                function () {

                    removeFilter(
                        this.dataset.filter
                    );

                }
            );

        }


        container.appendChild(badge);

    }



    /* ============================================================
       REMOVE FILTER
    ============================================================ */

    function removeFilter(type)
    {

        if (type === 'search') {

            const element =
                document.getElementById(
                    'searchInput'
                );


            if (element) {

                element.value = '';

            }

        }


        if (type === 'gender') {

            const element =
                document.getElementById(
                    'genderFilter'
                );


            if (element) {

                element.value = '';

            }

        }


        if (type === 'disability') {

            const element =
                document.getElementById(
                    'disabilityFilter'
                );


            if (element) {

                element.value = '';

            }

        }


        applyFilters();

    }



    /* ============================================================
       RESET FILTERS
    ============================================================ */

    function resetFilters()
    {

        const search =
            document.getElementById(
                'searchInput'
            );


        const gender =
            document.getElementById(
                'genderFilter'
            );


        const disability =
            document.getElementById(
                'disabilityFilter'
            );


        const sort =
            document.getElementById(
                'sortFilter'
            );


        if (search) {

            search.value = '';

        }


        if (gender) {

            gender.value = '';

        }


        if (disability) {

            disability.value = '';

        }


        if (sort) {

            sort.value = 'name_asc';

        }


        applyFilters();

        showToast(
            'Filters reset successfully.',
            'info'
        );

    }



    /* ============================================================
       CHECKBOX SELECTION
    ============================================================ */

    function initializeSelection()
    {

        const selectAll =
            document.getElementById(
                'selectAll'
            );


        if (selectAll) {

            selectAll.addEventListener(
                'change',
                function () {

                    const checked =
                        this.checked;


                    document.querySelectorAll(
                        '.student-checkbox'
                    ).forEach(function (checkbox) {

                        const row =
                            checkbox.closest('tr');


                        if (
                            row &&
                            row.style.display !== 'none'
                        ) {

                            checkbox.checked =
                                checked;

                        }

                    });

                }
            );

        }


        document.querySelectorAll(
            '.student-checkbox'
        ).forEach(function (checkbox) {

            checkbox.addEventListener(
                'change',
                updateSelectAll
            );

        });

    }



    /* ============================================================
       UPDATE SELECT ALL
    ============================================================ */

    function updateSelectAll()
    {

        const selectAll =
            document.getElementById(
                'selectAll'
            );


        if (!selectAll) {
            return;
        }


        const visibleCheckboxes =
            Array.from(
                document.querySelectorAll(
                    '.student-checkbox'
                )
            ).filter(function (checkbox) {

                const row =
                    checkbox.closest('tr');


                return row &&
                       row.style.display !== 'none';

            });


        const checked =
            visibleCheckboxes.filter(
                function (checkbox) {

                    return checkbox.checked;

                }
            );


        selectAll.checked =
            visibleCheckboxes.length > 0 &&
            checked.length === visibleCheckboxes.length;


        selectAll.indeterminate =
            checked.length > 0 &&
            checked.length < visibleCheckboxes.length;

    }



    /* ============================================================
       DELETE CONFIRMATION - USING BROWSER CONFIRM
    ============================================================ */

    function initializeDeleteConfirmation()
    {

        document.querySelectorAll(
            '.delete-student-form'
        ).forEach(function (form) {

            form.addEventListener(
                'submit',
                function (event) {

                    const studentName =
                        form.dataset.studentName || 'Unknown Student';


                    const studentId =
                        form.dataset.studentId || 'Unknown ID';


                    const message =
                        'Are you sure you want to delete student "' +
                        studentName +
                        '" (ID: ' +
                        studentId +
                        ')?\n\n' +
                        'This action cannot be undone!';


                    if (!confirm(message)) {

                        event.preventDefault();

                    }

                }
            );

        });

    }



    /* ============================================================
       CSV EXPORT
    ============================================================ */

    function exportToCSV()
    {

        const rows =
            Array.from(
                document.querySelectorAll(
                    '.student-row'
                )
            ).filter(function (row) {

                return row.style.display !== 'none';

            });


        if (rows.length === 0) {

            showToast(
                'There are no student records to export.',
                'warning'
            );


            return;

        }


        const csv = [];


        csv.push([

            'Student ID',
            'Full Name',
            'Gender',
            'Date of Birth',
            'Nationality',
            'Religion',
            'Address',
            'Admission Date',
            'Disability',
            'Disability Type'

        ]);


        rows.forEach(function (row) {

            const cells =
                row.cells;


            csv.push([

                csvEscape(
                    cells[1]?.innerText.trim() || ''
                ),

                csvEscape(
                    cells[2]?.innerText.trim() || ''
                ),

                csvEscape(
                    cells[3]?.innerText.trim() || ''
                ),

                csvEscape(
                    cells[4]?.innerText.trim() || ''
                ),

                csvEscape(
                    cells[5]?.innerText.trim() || ''
                ),

                csvEscape(
                    cells[6]?.innerText.trim() || ''
                ),

                csvEscape(
                    cells[7]?.innerText.trim() || ''
                ),

                csvEscape(
                    cells[8]?.innerText.trim() || ''
                ),

                csvEscape(
                    cells[9]?.innerText.trim() || ''
                ),

                csvEscape(
                    cells[10]?.innerText.trim() || ''
                )

            ]);

        });


        const csvContent =
            csv
                .map(function (row) {

                    return row.join(',');

                })
                .join('\n');


        const blob =
            new Blob(
                [csvContent],
                {
                    type:
                        'text/csv;charset=utf-8;'
                }
            );


        const url =
            URL.createObjectURL(blob);


        const link =
            document.createElement('a');


        link.href =
            url;


        link.download =
            'students_' +
            new Date()
                .toISOString()
                .slice(0, 10) +
            '.csv';


        document.body.appendChild(link);


        link.click();


        document.body.removeChild(link);


        URL.revokeObjectURL(url);


        showToast(
            'Student data exported successfully.',
            'success'
        );

    }



    /* ============================================================
       CSV ESCAPE
    ============================================================ */

    function csvEscape(value)
    {

        return '"' +
            String(value)
                .replace(/"/g, '""') +
            '"';

    }



    /* ============================================================
       HTML ESCAPE
    ============================================================ */

    function escapeHtml(value)
    {

        const div =
            document.createElement('div');


        div.textContent =
            value;


        return div.innerHTML;

    }



    /* ============================================================
       TOAST
    ============================================================ */

    function showToast(
        message,
        type
    )
    {

        type =
            type || 'success';


        const alertType =
            type === 'error'
                ? 'danger'
                : type;


        const icon =
            type === 'success'
                ? 'check-circle'
                : type === 'error'
                    ? 'exclamation-circle'
                    : type === 'warning'
                        ? 'exclamation-triangle'
                        : 'info-circle';


        const toast =
            document.createElement('div');


        toast.className =
            'alert alert-' +
            alertType +
            ' alert-dismissible fade show position-fixed top-0 end-0 m-3 shadow';


        toast.style.zIndex =
            '99999';


        toast.style.minWidth =
            '300px';


        toast.innerHTML = `

            <i class="fas fa-${icon} me-2"></i>

            ${escapeHtml(message)}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close">
            </button>

        `;


        document.body.appendChild(
            toast
        );


        setTimeout(function () {

            if (
                toast &&
                toast.parentNode
            ) {

                toast.remove();

            }

        }, 3500);

    }


    /* ============================================================
       CSV BUTTON
    ============================================================ */

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            const exportButton =
                document.getElementById(
                    'exportCsvBtn'
                );


            if (exportButton) {

                exportButton.addEventListener(
                    'click',
                    exportToCSV
                );

            }

        }
    );


})();

</script>



{{-- =============================================================
     STYLES
============================================================= --}}
<style>

/* ================================================================
   GENERAL
================================================================ */

.card {
    border-radius: 12px;
}


.card-header {
    border-bottom: 1px solid #eee;
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


.table-hover tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.03);
}


.form-control,
.form-select {
    border-radius: 8px;
}


.input-group-text {
    border-radius: 8px 0 0 8px;
    background: #fff;
}


.btn {
    border-radius: 8px;
}


.badge {
    border-radius: 8px;
}


.student-checkbox,
#selectAll {
    cursor: pointer;
}


/* ================================================================
   DELETE BUTTON
================================================================ */

.delete-student-btn {
    cursor: pointer;
}


.delete-student-btn:hover {
    background-color: rgba(220, 53, 69, 0.08);
}


.delete-student-form {
    display: inline;
}


/* ================================================================
   ALERT ANIMATION
================================================================ */

@keyframes fadeIn {

    from {
        opacity: 0;
        transform: translateY(-8px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }

}


.alert {
    animation:
        fadeIn 0.25s ease;
}


/* ================================================================
   PAGINATION
================================================================ */

.pagination {
    margin-bottom: 0;
}


/* ================================================================
   ACTIVE FILTERS
================================================================ */

#activeFilters .badge {
    font-size: 12px;
    font-weight: 500;
}

</style>

@endsection