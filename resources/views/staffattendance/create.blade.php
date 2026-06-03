@extends('layouts.master')

@section('title', 'Staff Attendance')

@section('content')
<div class="container-fluid py-4">

    {{-- HEADER --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap">

            <div>
                <h3 class="mb-1">
                    <i class="fas fa-clock text-danger me-2"></i>
                    Staff Attendance
                </h3>
                <small class="text-muted">
                    Record clock-in and clock-out with GPS verification
                </small>
            </div>

            <div class="mt-2 mt-md-0">
                <span class="badge bg-white text-dark fs-6 p-2">
                    <i class="fas fa-calendar-alt me-1"></i>
                    {{ now()->format('l, d M Y') }}
                </span>
            </div>

        </div>
    </div>

    {{-- FLASH MESSAGES --}}
    @foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $msg => $type)
        @if(session($msg))
            <div class="alert alert-{{ $type }} alert-dismissible fade show shadow-sm">
                <i class="fas fa-info-circle me-2"></i>
                {{ session($msg) }}
                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endforeach

    {{-- FORM CARD --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0">
            <h5 class="mb-0">
                <i class="fas fa-fingerprint text-danger me-2"></i>
                Attendance Form
            </h5>
        </div>

        <div class="card-body p-4">

            <form id="attendanceForm" method="POST">
                @csrf

                <div class="row g-4">

                    {{-- STAFF --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Staff Member</label>
                        <select name="staff_id" id="staff_id" class="form-select form-select-lg" required>
                            <option value="">-- Select Staff --</option>
                            @foreach($staff as $s)
                                <option value="{{ $s->id }}">
                                    {{ $s->first_name }} {{ $s->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- DATE --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Date</label>
                        <input type="date"
                               name="date"
                               class="form-control form-control-lg"
                               value="{{ date('Y-m-d') }}"
                               required>
                    </div>

                    {{-- GPS BOX --}}
                    <div class="col-12">
                        <div class="p-3 rounded bg-light border">

                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                <strong>GPS Location (Auto detected)</strong>
                            </div>

                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Latitude</label>
                                    <input type="text" id="latitude" name="latitude"
                                           class="form-control font-monospace" readonly required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Longitude</label>
                                    <input type="text" id="longitude" name="longitude"
                                           class="form-control font-monospace" readonly required>
                                </div>

                            </div>

                            <small id="gpsStatus" class="text-muted d-block mt-2"></small>

                        </div>
                    </div>

                    {{-- BUTTONS --}}
                    <div class="col-12 text-center mt-2">

                        <div class="d-flex justify-content-center gap-3 flex-wrap">

                            <button type="button"
                                    class="btn btn-success btn-lg px-4"
                                    onclick="submitAttendance('clock-in')">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Clock In
                            </button>

                            <button type="button"
                                    class="btn btn-danger btn-lg px-4"
                                    onclick="submitAttendance('clock-out')">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Clock Out
                            </button>

                        </div>

                        <small class="text-muted d-block mt-3">
                            GPS is required for attendance verification
                        </small>

                    </div>

                </div>

            </form>

        </div>
    </div>

</div>

{{-- GPS --}}
<script>
navigator.geolocation.getCurrentPosition(function(pos) {
    document.getElementById('latitude').value = pos.coords.latitude.toFixed(6);
    document.getElementById('longitude').value = pos.coords.longitude.toFixed(6);
    document.getElementById('gpsStatus').innerHTML =
        '<span class="text-success"><i class="fas fa-check-circle me-1"></i> Location captured</span>';
});
</script>

{{-- FORM --}}
<script>
function submitAttendance(type) {

    const staff = document.getElementById('staff_id').value;
    const lat = document.getElementById('latitude').value;
    const lng = document.getElementById('longitude').value;

    if (!staff) return alert('Select staff');
    if (!lat || !lng) return alert('GPS not detected');

    const form = document.getElementById('attendanceForm');

    form.action = (type === 'clock-in')
        ? "{{ route('staffattendance.clock-in') }}"
        : "{{ route('staffattendance.clock-out') }}";

    form.submit();
}
</script>

@endsection