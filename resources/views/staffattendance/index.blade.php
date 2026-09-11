@extends('layouts.master')

@section('title', 'Staff Attendance Management')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="fas fa-users text-primary me-2"></i>
                Staff Attendance Management
            </h3>
            <p class="text-muted mb-0">
                View staff attendance by Academic Year and Term.
            </p>
        </div>

        <a href="{{ route('staff-attendance.create', array_filter([
            'academic_year_id' => $selectedAcademicYear,
            'term_id' => $selectedTerm,
        ], fn($v) => $v !== null)) }}"
           class="btn btn-primary">
            <i class="fas fa-fingerprint me-1"></i>
            Take Attendance
        </a>
    </div>

    @foreach(['success'=>'success','error'=>'primary','warning'=>'warning','info'=>'info'] as $msg => $type)
        @if(session($msg))
            <div class="alert alert-{{ $type }} alert-dismissible fade show shadow-sm">
                {{ session($msg) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endforeach

    {{-- PERIOD FILTER --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fas fa-filter text-primary me-2"></i>
                Academic Period Filter
            </h5>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('staff-attendance.index') }}" id="periodForm">

                <div class="row g-3">

                    <div class="col-md-5">
                        <label for="academic_year_id" class="form-label fw-semibold">
                            Academic Year
                        </label>

                        <select name="academic_year_id"
                                id="academic_year_id"
                                class="form-select">
                            <option value="">All Academic Years</option>

                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}"
                                    {{ (string)$selectedAcademicYear === (string)$year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-5">
                        <label for="term_id" class="form-label fw-semibold">
                            Term
                        </label>

                        <select name="term_id"
                                id="term_id"
                                class="form-select"
                                {{ !$selectedAcademicYear ? 'disabled' : '' }}>
                            <option value="">All Terms</option>

                            @foreach($terms as $term)
                                <option value="{{ $term->id }}"
                                    {{ (string)$selectedTerm === (string)$term->id ? 'selected' : '' }}>
                                    {{ $term->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100" type="submit">
                            <i class="fas fa-search me-1"></i>
                            Apply
                        </button>
                    </div>

                </div>
            </form>

            <div class="alert alert-light border mt-3 mb-0">
                <i class="fas fa-info-circle text-primary me-2"></i>
                Academic Year and Term are stored on each attendance record, so historical
                attendance remains available after the school changes its current period.
            </div>
        </div>
    </div>

    {{-- LOCAL FILTERS --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">

                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold">Search Staff</label>
                    <input type="text" id="searchInput"
                           class="form-control"
                           placeholder="Search by staff name...">
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-semibold">From Date</label>
                    <input type="date" id="fromDate" class="form-control">
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-semibold">To Date</label>
                    <input type="date" id="toDate" class="form-control">
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-semibold">Status</label>
                    <select id="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="present">Present</option>
                        <option value="late">Late</option>
                        <option value="absent">Absent</option>
                    </select>
                </div>

                <div class="col-lg-3 d-flex align-items-end gap-2">
                    <button type="button"
                            class="btn btn-outline-secondary flex-grow-1"
                            onclick="resetFilters()">
                        <i class="fas fa-undo me-1"></i>
                        Reset
                    </button>

                    <button type="button"
                            class="btn btn-success"
                            onclick="exportVisible()">
                        <i class="fas fa-file-csv"></i>
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">

            <div>
                <h5 class="fw-bold mb-1">Attendance Records</h5>

                <small class="text-muted">
                    Academic Year:
                    <strong>
                        {{ $academicYears->firstWhere('id', $selectedAcademicYear)?->name ?? 'All' }}
                    </strong>

                    · Term:
                    <strong>
                        {{ $terms->firstWhere('id', $selectedTerm)?->name ?? 'All Terms' }}
                    </strong>
                </small>
            </div>

            <span class="badge bg-light text-dark border">
                {{ $attendances->total() }} total records
            </span>

        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover table-bordered mb-0 align-middle"
                       id="attendanceTable">

                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Staff Name</th>
                            <th>Academic Year</th>
                            <th>Term</th>
                            <th>Date</th>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($attendances as $key => $attendance)

                        @php
                            $statusClass = match($attendance->status) {
                                'present' => 'success',
                                'late' => 'warning',
                                'absent' => 'primary',
                                default => 'secondary',
                            };
                        @endphp

                        <tr class="attendance-row"
                            data-staff="{{ strtolower(trim(($attendance->staff->first_name ?? '') . ' ' . ($attendance->staff->last_name ?? ''))) }}"
                            data-date="{{ $attendance->date?->format('Y-m-d') }}"
                            data-status="{{ $attendance->status }}">

                            <td>{{ $attendances->firstItem() + $key }}</td>

                            <td class="fw-semibold">
                                {{ trim(($attendance->staff->first_name ?? '') . ' ' . ($attendance->staff->last_name ?? '')) }}
                            </td>

                            <td>
                                {{ $attendance->academicYear?->name ?? 'Not Assigned' }}
                            </td>

                            <td>
                                {{ $attendance->term?->name ?? 'Not Assigned' }}
                            </td>

                            <td>
                                {{ $attendance->date?->format('M d, Y') }}
                            </td>

                            <td>
                                @if($attendance->clock_in_time)
                                    {{ \Carbon\Carbon::parse($attendance->clock_in_time)->format('h:i A') }}
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>

                            <td>
                                @if($attendance->clock_out_time)
                                    {{ \Carbon\Carbon::parse($attendance->clock_out_time)->format('h:i A') }}
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>

                            <td>
                                <span class="badge bg-{{ $statusClass }} px-3 py-2">
                                    {{ ucfirst($attendance->status) }}
                                </span>
                            </td>

                            <td>
                                <div class="btn-group btn-group-sm">

                                    <a href="{{ route('staff-attendance.show', $attendance->id) }}"
                                       class="btn btn-outline-primary"
                                       title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <a href="{{ route('staff-attendance.edit', $attendance->id) }}"
                                       class="btn btn-outline-secondary"
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <button type="button"
                                            class="btn btn-outline-primary"
                                            onclick="deleteAttendance({{ $attendance->id }})"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                </div>
                            </td>

                        </tr>

                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3 d-block"></i>
                                <h5 class="text-muted">No Attendance Records</h5>
                                <p class="text-muted mb-0">
                                    No records exist for the selected Academic Year and Term.
                                </p>
                            </td>
                        </tr>
                    @endforelse

                    </tbody>
                </table>
            </div>
        </div>

        @if($attendances->hasPages())
            <div class="card-footer bg-white">
                {{ $attendances->withQueryString()->links() }}
            </div>
        @endif

    </div>
</div>

{{-- DELETE --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-trash me-2"></i>
                    Delete Attendance
                </h5>
                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                Are you sure you want to delete this attendance record?
            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Cancel
                </button>

                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-primary">
                        Delete Record
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const yearSelect = document.getElementById('academic_year_id');
    const termSelect = document.getElementById('term_id');

    const termsUrl = @json(route(
        'settings.academic-years.terms',
        ['academicYear' => '__YEAR__']
    ));

    yearSelect.addEventListener('change', async function () {

        const yearId = this.value;

        termSelect.disabled = true;
        termSelect.innerHTML =
            '<option value="">Loading terms...</option>';

        if (!yearId) {
            termSelect.innerHTML =
                '<option value="">All Terms</option>';
            return;
        }

        try {

            const response = await fetch(
                termsUrl.replace(
                    '__YEAR__',
                    encodeURIComponent(yearId)
                ),
                {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }
            );

            if (!response.ok) {
                throw new Error('Unable to load terms.');
            }

            const payload = await response.json();

            const terms = Array.isArray(payload)
                ? payload
                : (payload.terms || payload.data || []);

            termSelect.innerHTML =
                '<option value="">All Terms</option>';

            terms.forEach(term => {

                const option =
                    document.createElement('option');

                option.value = term.id;
                option.textContent = term.name;

                termSelect.appendChild(option);
            });

            termSelect.disabled = false;

        } catch (error) {

            console.error(error);

            termSelect.innerHTML =
                '<option value="">Unable to load terms</option>';

            termSelect.disabled = false;
        }
    });

    const search = document.getElementById('searchInput');
    const from = document.getElementById('fromDate');
    const to = document.getElementById('toDate');
    const status = document.getElementById('statusFilter');

    [search, from, to, status].forEach(element => {
        element.addEventListener('input', filterRows);
        element.addEventListener('change', filterRows);
    });
});

function filterRows() {

    const search = document.getElementById('searchInput')
        .value.toLowerCase();

    const from = document.getElementById('fromDate').value;
    const to = document.getElementById('toDate').value;
    const status = document.getElementById('statusFilter').value;

    document.querySelectorAll('.attendance-row').forEach(row => {

        const staff = row.dataset.staff || '';
        const date = row.dataset.date || '';
        const rowStatus = row.dataset.status || '';

        const matchSearch =
            !search || staff.includes(search);

        const matchFrom =
            !from || date >= from;

        const matchTo =
            !to || date <= to;

        const matchStatus =
            !status || rowStatus === status;

        row.style.display =
            matchSearch &&
            matchFrom &&
            matchTo &&
            matchStatus
                ? ''
                : 'none';
    });
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('fromDate').value = '';
    document.getElementById('toDate').value = '';
    document.getElementById('statusFilter').value = '';

    filterRows();
}

function exportVisible() {

    const rows = [...document.querySelectorAll('.attendance-row')]
        .filter(row => row.style.display !== 'none');

    if (!rows.length) {
        alert('No visible records to export.');
        return;
    }

    const csv = [[
        '#',
        'Staff Name',
        'Academic Year',
        'Term',
        'Date',
        'Clock In',
        'Clock Out',
        'Status'
    ]];

    rows.forEach((row, index) => {
        csv.push([
            index + 1,
            csvEscape(row.cells[1]?.innerText.trim() || ''),
            csvEscape(row.cells[2]?.innerText.trim() || ''),
            csvEscape(row.cells[3]?.innerText.trim() || ''),
            csvEscape(row.cells[4]?.innerText.trim() || ''),
            csvEscape(row.cells[5]?.innerText.trim() || ''),
            csvEscape(row.cells[6]?.innerText.trim() || ''),
            csvEscape(row.cells[7]?.innerText.trim() || '')
        ]);
    });

    const blob = new Blob(
        [csv.map(row => row.join(',')).join('\n')],
        {type: 'text/csv;charset=utf-8;'}
    );

    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');

    link.href = url;
    link.download =
        'staff_attendance_{{ $selectedAcademicYear ?: "all" }}_{{ $selectedTerm ?: "all" }}.csv';

    link.click();

    URL.revokeObjectURL(url);
}

function csvEscape(value) {
    return '"' + String(value).replace(/"/g, '""') + '"';
}

function deleteAttendance(id) {

    const form = document.getElementById('deleteForm');

    form.action = '/staff-attendance/' + id;

    bootstrap.Modal
        .getOrCreateInstance(
            document.getElementById('deleteModal')
        )
        .show();
}
</script>
@endpush

@push('styles')
<style>
.card {
    border-radius: 12px;
}

.table th {
    white-space: nowrap;
    font-size: 13px;
}

.table td {
    font-size: 13px;
    vertical-align: middle;
}

.badge {
    border-radius: 8px;
}

.btn,
.form-control,
.form-select {
    border-radius: 8px;
}
</style>
@endpush
