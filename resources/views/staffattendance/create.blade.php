@extends('layouts.master')

@section('title', 'Staff Attendance')

@section('content')
@php
    $selectedTermModel = $terms->firstWhere('id', $selectedTerm);
@endphp

<div class="container-fluid py-4">

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h3 class="fw-bold mb-1">
                    <i class="fas fa-clock text-primary me-2"></i>
                    Staff Attendance
                </h3>
                <small class="text-muted">
                    Record staff clock-in and clock-out with GPS verification.
                </small>
            </div>

            <span class="badge bg-light text-dark border p-2 fs-6">
                <i class="fas fa-calendar-alt me-1"></i>
                {{ now()->format('l, d M Y') }}
            </span>
        </div>
    </div>

    @foreach(['success'=>'success','error'=>'primary','warning'=>'warning','info'=>'info'] as $msg => $type)
        @if(session($msg))
            <div class="alert alert-{{ $type }} alert-dismissible fade show shadow-sm">
                {{ session($msg) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endforeach

    @if($errors->any())
        <div class="alert alert-primary shadow-sm">
            <strong>Please correct the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ACADEMIC PERIOD --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fas fa-graduation-cap text-primary me-2"></i>
                Academic Period
            </h5>
        </div>

        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-6">
                    <label for="academic_year_id" class="form-label fw-semibold">
                        Academic Year <span class="text-danger">*</span>
                    </label>

                    <select id="academic_year_id" class="form-select" required>
                        <option value="">-- Select Academic Year --</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}"
                                {{ (string)$selectedAcademicYear === (string)$year->id ? 'selected' : '' }}>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="term_id" class="form-label fw-semibold">
                        Term <span class="text-danger">*</span>
                    </label>

                    <select id="term_id" class="form-select" required>
                        <option value="">-- Select Term --</option>
                        @foreach($terms as $term)
                            <option value="{{ $term->id }}"
                                {{ (string)$selectedTerm === (string)$term->id ? 'selected' : '' }}>
                                {{ $term->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            <div class="alert alert-light border mt-3 mb-0">
                <i class="fas fa-info-circle text-primary me-2"></i>
                Attendance records are permanently linked to the selected Academic Year and Term.
            </div>
        </div>
    </div>

    {{-- ATTENDANCE --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fas fa-fingerprint text-primary me-2"></i>
                Attendance Form
            </h5>
        </div>

        <div class="card-body p-4">

            <form id="attendanceForm" method="POST">
                @csrf

                <input type="hidden" name="academic_year_id" id="formAcademicYearId"
                       value="{{ $selectedAcademicYear }}">

                <input type="hidden" name="term_id" id="formTermId"
                       value="{{ $selectedTerm }}">

                <div class="row g-4">

                    <div class="col-md-6">
                        <label for="staff_id" class="form-label fw-semibold">
                            Staff Member <span class="text-danger">*</span>
                        </label>

                        <select name="staff_id" id="staff_id" class="form-select" required>
                            <option value="">-- Select Staff --</option>
                            @foreach($staff as $member)
                                <option value="{{ $member->id }}">
                                    {{ trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="attendanceDate" class="form-label fw-semibold">
                            Date <span class="text-danger">*</span>
                        </label>

                        <input type="date"
                               name="date"
                               id="attendanceDate"
                               class="form-control"
                               value="{{ now()->format('Y-m-d') }}"
                               min="{{ $selectedTermModel?->start_date?->format('Y-m-d') }}"
                               max="{{ $selectedTermModel?->end_date?->format('Y-m-d') }}"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label for="latitude" class="form-label fw-semibold">Latitude</label>
                        <input type="text" id="latitude" name="latitude"
                               class="form-control" readonly required>
                    </div>

                    <div class="col-md-6">
                        <label for="longitude" class="form-label fw-semibold">Longitude</label>
                        <input type="text" id="longitude" name="longitude"
                               class="form-control" readonly required>
                    </div>

                    <div class="col-12">
                        <div class="alert alert-light border mb-0" id="gpsStatus">
                            <i class="fas fa-spinner fa-spin me-2"></i>
                            Detecting GPS location...
                        </div>
                    </div>

                    <div class="col-12 text-center mt-4">

                        <button type="button"
                                class="btn btn-success btn-lg px-5 me-2"
                                onclick="submitAttendance('{{ route('staff-attendance.gps-clock-in') }}')">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Clock In
                        </button>

                        <button type="button"
                                class="btn btn-danger btn-lg px-5"
                                onclick="submitAttendance('{{ route('staff-attendance.gps-clock-out') }}')">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            Clock Out
                        </button>

                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const yearSelect = document.getElementById('academic_year_id');
    const termSelect = document.getElementById('term_id');
    const formYear = document.getElementById('formAcademicYearId');
    const formTerm = document.getElementById('formTermId');
    const dateInput = document.getElementById('attendanceDate');

    const termsUrl = @json(route(
        'settings.academic-years.terms',
        ['academicYear' => '__YEAR__']
    ));

    const termDates = {};

    @foreach($terms as $term)
        termDates['{{ $term->id }}'] = {
            start: @json($term->start_date?->format('Y-m-d')),
            end: @json($term->end_date?->format('Y-m-d'))
        };
    @endforeach

    function syncFields() {
        formYear.value = yearSelect.value || '';
        formTerm.value = termSelect.value || '';
    }

    function updateDateRange() {
        const dates = termDates[String(termSelect.value)];

        if (!dates) {
            dateInput.removeAttribute('min');
            dateInput.removeAttribute('max');
            return;
        }

        if (dates.start) dateInput.min = dates.start;
        else dateInput.removeAttribute('min');

        if (dates.end) dateInput.max = dates.end;
        else dateInput.removeAttribute('max');

        if (dateInput.value && dates.start && dateInput.value < dates.start) {
            dateInput.value = dates.start;
        }

        if (dateInput.value && dates.end && dateInput.value > dates.end) {
            dateInput.value = dates.end;
        }
    }

    async function loadTerms(yearId, selectedTerm = null) {
        termSelect.disabled = true;
        termSelect.innerHTML = '<option value="">Loading terms...</option>';

        if (!yearId) {
            termSelect.innerHTML = '<option value="">-- Select Term --</option>';
            termSelect.disabled = false;
            syncFields();
            return;
        }

        try {
            const response = await fetch(
                termsUrl.replace('__YEAR__', encodeURIComponent(yearId)),
                {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }
            );

            if (!response.ok) throw new Error('Failed to load terms.');

            const payload = await response.json();
            const items = Array.isArray(payload)
                ? payload
                : (payload.terms || payload.data || []);

            termSelect.innerHTML = '<option value="">-- Select Term --</option>';

            items.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.name;

                termDates[String(item.id)] = {
                    start: item.start_date || null,
                    end: item.end_date || null
                };

                if (selectedTerm !== null &&
                    String(selectedTerm) === String(item.id)) {
                    option.selected = true;
                }

                termSelect.appendChild(option);
            });

            termSelect.disabled = false;
            syncFields();
            updateDateRange();

        } catch (error) {
            console.error(error);
            termSelect.innerHTML =
                '<option value="">Unable to load terms</option>';
            termSelect.disabled = false;
        }
    }

    yearSelect.addEventListener('change', function () {
        loadTerms(this.value);
    });

    termSelect.addEventListener('change', function () {
        syncFields();
        updateDateRange();
    });

    syncFields();
    updateDateRange();
});

/* GPS */
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
        function (position) {
            document.getElementById('latitude').value =
                position.coords.latitude.toFixed(7);

            document.getElementById('longitude').value =
                position.coords.longitude.toFixed(7);

            document.getElementById('gpsStatus').innerHTML =
                '<span class="text-success">' +
                '<i class="fas fa-check-circle me-2"></i>' +
                'GPS location captured successfully.' +
                '</span>';
        },
        function () {
            document.getElementById('gpsStatus').innerHTML =
                '<span class="text-primary">' +
                '<i class="fas fa-times-circle me-2"></i>' +
                'Unable to detect location. Please enable GPS.' +
                '</span>';
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
} else {
    document.getElementById('gpsStatus').innerHTML =
        '<span class="text-primary">' +
        '<i class="fas fa-times-circle me-2"></i>' +
        'Geolocation is not supported by this browser.' +
        '</span>';
}

function submitAttendance(url) {
    const year = document.getElementById('formAcademicYearId').value;
    const term = document.getElementById('formTermId').value;
    const staff = document.getElementById('staff_id').value;
    const lat = document.getElementById('latitude').value;
    const lng = document.getElementById('longitude').value;

    if (!year) {
        alert('Please select an Academic Year.');
        return;
    }

    if (!term) {
        alert('Please select a Term.');
        return;
    }

    if (!staff) {
        alert('Please select a staff member.');
        return;
    }

    if (!lat || !lng) {
        alert('GPS location has not been captured.');
        return;
    }

    const form = document.getElementById('attendanceForm');
    form.action = url;
    form.submit();
}
</script>
@endpush
