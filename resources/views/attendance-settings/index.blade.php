@extends('layouts.master')

@section('title', 'Attendance Settings')

@section('content')

<div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

        <div>
            <h2 class="fw-bold mb-1 mt-3">
                <i class="fas fa-cogs text-danger me-2"></i>
                Attendance Settings
            </h2>

            <p class="text-muted mb-0">
                Configure GPS attendance rules, radius validation, and clock timing
            </p>
        </div>

        <div class="d-flex gap-2">

            @if(!$setting)
                <a href="{{ route('attendance-settings.create') }}"
                   class="btn btn-primary shadow-sm">

                    <i class="fas fa-plus-circle me-1"></i>
                    Add Settings

                </a>
            @endif

        </div>

    </div>

    {{-- FLASH MESSAGE --}}
    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show shadow-sm">

            <i class="fas fa-check-circle me-2"></i>

            {{ session('success') }}

            <button class="btn-close" data-bs-dismiss="alert"></button>

        </div>

    @endif

    {{-- SEARCH + FILTER --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="row g-3">

                {{-- SEARCH --}}
                <div class="col-md-6">

                    <label class="form-label fw-semibold">
                        Search Settings
                    </label>

                    <div class="input-group">

                        <span class="input-group-text bg-white">
                            <i class="fas fa-search"></i>
                        </span>

                        <input type="text"
                               id="searchInput"
                               class="form-control"
                               placeholder="Search ">

                    </div>

                </div>

                {{-- FILTER --}}
                <div class="col-md-3">

                    <label class="form-label fw-semibold">
                        GPS Status
                    </label>

                    <select id="gpsFilter" class="form-select">

                        <option value="">All</option>
                        <option value="enabled">Enabled</option>
                        <option value="disabled">Disabled</option>

                    </select>

                </div>

                {{-- RESET --}}
                <div class="col-md-3">

                    <label class="form-label fw-semibold d-block">
                        &nbsp;
                    </label>

                    <button type="button"
                            class="btn btn-outline-secondary w-100"
                            onclick="resetFilters()">

                        <i class="fas fa-sync-alt me-1"></i>
                        Reset Filters

                    </button>

                </div>

            </div>

        </div>

    </div>

    {{-- SETTINGS CARD --}}
    @if($setting)

        <div class="card border-0 shadow-sm" id="settingsCard">

            <div class="card-body">

                <div class="row g-4 searchable-content">

                    {{-- LATITUDE --}}
                    <div class="col-md-4 setting-item"
                         data-search="{{ $setting->latitude }}">

                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">

                            <small class="text-muted d-block mb-2">
                                <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                Latitude
                            </small>

                            <h5 class="fw-bold mb-0">
                                {{ $setting->latitude }}
                            </h5>

                        </div>

                    </div>

                    {{-- LONGITUDE --}}
                    <div class="col-md-4 setting-item"
                         data-search="{{ $setting->longitude }}">

                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">

                            <small class="text-muted d-block mb-2">
                                <i class="fas fa-globe text-primary me-1"></i>
                                Longitude
                            </small>

                            <h5 class="fw-bold mb-0">
                                {{ $setting->longitude }}
                            </h5>

                        </div>

                    </div>

                    {{-- RADIUS --}}
                    <div class="col-md-4 setting-item"
                         data-search="{{ $setting->radius }}">

                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">

                            <small class="text-muted d-block mb-2">
                                <i class="fas fa-bullseye text-success me-1"></i>
                                Allowed Radius
                            </small>

                            <h5 class="fw-bold mb-0">
                                {{ $setting->radius }} meters
                            </h5>

                        </div>

                    </div>

                    {{-- CLOCK IN START --}}
                    <div class="col-md-3 setting-item"
                         data-search="{{ $setting->clock_in_start }}">

                        <div class="border rounded-3 p-3 bg-white">

                            <small class="text-muted d-block mb-2">
                                Clock In Start
                            </small>

                            <h6 class="fw-bold mb-0 text-success">
                                {{ \Carbon\Carbon::parse($setting->clock_in_start)->format('h:i A') }}
                            </h6>

                        </div>

                    </div>

                    {{-- CLOCK IN END --}}
                    <div class="col-md-3 setting-item"
                         data-search="{{ $setting->clock_in_end }}">

                        <div class="border rounded-3 p-3 bg-white">

                            <small class="text-muted d-block mb-2">
                                Clock In End
                            </small>

                            <h6 class="fw-bold mb-0 text-danger">
                                {{ \Carbon\Carbon::parse($setting->clock_in_end)->format('h:i A') }}
                            </h6>

                        </div>

                    </div>

                    {{-- CLOCK OUT START --}}
                    <div class="col-md-3 setting-item"
                         data-search="{{ $setting->clock_out_start }}">

                        <div class="border rounded-3 p-3 bg-white">

                            <small class="text-muted d-block mb-2">
                                Clock Out Start
                            </small>

                            <h6 class="fw-bold mb-0 text-warning">
                                {{ \Carbon\Carbon::parse($setting->clock_out_start)->format('h:i A') }}
                            </h6>

                        </div>

                    </div>

                    {{-- CLOCK OUT END --}}
                    <div class="col-md-3 setting-item"
                         data-search="{{ $setting->clock_out_end }}">

                        <div class="border rounded-3 p-3 bg-white">

                            <small class="text-muted d-block mb-2">
                                Clock Out End
                            </small>

                            <h6 class="fw-bold mb-0 text-primary">
                                {{ \Carbon\Carbon::parse($setting->clock_out_end)->format('h:i A') }}
                            </h6>

                        </div>

                    </div>

                    {{-- GPS STATUS --}}
                    <div class="col-12 setting-item"
                         id="gpsStatusItem"
                         data-gps="{{ $setting->gps_enabled ? 'enabled' : 'disabled' }}">

                        <div class="mt-2">

                            @if($setting->gps_enabled)

                                <span class="badge bg-success p-3 fs-6">
                                    <i class="fas fa-check-circle me-1"></i>
                                    GPS Attendance Enabled
                                </span>

                            @else

                                <span class="badge bg-danger p-3 fs-6">
                                    <i class="fas fa-times-circle me-1"></i>
                                    GPS Attendance Disabled
                                </span>

                            @endif

                        </div>

                    </div>

                </div>

            </div>

            {{-- FOOTER --}}
            <div class="card-footer bg-white border-0">

                <a href="{{ route('attendance-settings.edit', $setting->id) }}"
                   class="btn btn-white text-dark shadow-sm">

                    <i class="fas fa-edit me-1"></i>
                    Edit Settings

                </a>

            </div>

        </div>

    @else

        {{-- EMPTY STATE --}}
        <div class="card border-0 shadow-sm">

            <div class="card-body text-center py-5">

                <i class="fas fa-map-marker-alt fa-4x text-muted mb-3"></i>

                <h4 class="fw-bold">
                    No Attendance Settings Found
                </h4>

                <p class="text-muted mb-4">
                    Configure attendance GPS settings to start tracking staff attendance.
                </p>

                <a href="{{ route('attendance-settings.create') }}"
                   class="btn btn-primary">

                    <i class="fas fa-plus-circle me-1"></i>
                    Create Settings

                </a>

            </div>

        </div>

    @endif

</div>

{{-- AJAX SEARCH + FILTER --}}
<script>

document.addEventListener('DOMContentLoaded', function () {

    const searchInput = document.getElementById('searchInput');
    const gpsFilter = document.getElementById('gpsFilter');

    function filterSettings() {

        const search = searchInput.value.toLowerCase();
        const gps = gpsFilter.value;

        const items = document.querySelectorAll('.setting-item');

        items.forEach(item => {

            const text = item.dataset.search
                ? item.dataset.search.toLowerCase()
                : '';

            const gpsStatus = item.dataset.gps || '';

            let matchesSearch = text.includes(search) || search === '';

            let matchesGps = true;

            if (gps !== '') {
                matchesGps = gpsStatus === gps || gpsStatus === '';
            }

            if (matchesSearch && matchesGps) {

                item.style.display = '';

            } else {

                item.style.display = 'none';

            }

        });

    }

    searchInput.addEventListener('keyup', filterSettings);
    gpsFilter.addEventListener('change', filterSettings);

});

function resetFilters() {

    document.getElementById('searchInput').value = '';
    document.getElementById('gpsFilter').value = '';

    const items = document.querySelectorAll('.setting-item');

    items.forEach(item => {
        item.style.display = '';
    });

}

</script>

{{-- CUSTOM STYLING --}}
<style>

.card {
    border-radius: 14px;
}

.setting-item .border {
    transition: all 0.25s ease;
}

.setting-item .border:hover {
    transform: translateY(-3px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.08);
}

.badge {
    border-radius: 8px;
}

</style>

@endsection