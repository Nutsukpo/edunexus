@extends('layouts.master')

@section('title', 'Staff Attendance')

@section('content')

<div class="container-fluid py-4">
{{-- Header --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap">

        <div>
            <h3 class="fw-bold mb-1">
                <i class="fas fa-clock text-danger me-2"></i>
                Staff Attendance
            </h3>

            <small class="text-muted">
                Record clock-in and clock-out with GPS verification
            </small>
        </div>

        <div>
            <span class="badge bg-light text-dark p-2 fs-6">
                <i class="fas fa-calendar-alt me-1"></i>
                {{ now()->format('l, d M Y') }}
            </span>
        </div>

    </div>
</div>


{{-- Flash Messages --}}
@foreach (['success'=>'success','error'=>'danger','warning'=>'warning','info'=>'info'] as $msg => $type)
    @if(session($msg))
        <div class="alert alert-{{ $type }} alert-dismissible fade show shadow-sm">
            {{ session($msg) }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
@endforeach


{{-- Attendance Form --}}
<div class="card border-0 shadow-sm">

    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fas fa-fingerprint text-danger me-2"></i>
            Attendance Form
        </h5>
    </div>

    <div class="card-body p-4">

        <form id="attendanceForm" method="POST">
            @csrf

            <div class="row g-4">

                {{-- Staff --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Staff Member
                    </label>

                    <select name="staff_id"
                            id="staff_id"
                            class="form-select"
                            required>

                        <option value="">
                            -- Select Staff --
                        </option>

                        @foreach($staff as $member)
                            <option value="{{ $member->id }}">
                                {{ $member->first_name }}
                                {{ $member->last_name }}
                            </option>
                        @endforeach

                    </select>
                </div>


                {{-- Date --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Date
                    </label>

                    <input type="date"
                           name="date"
                           class="form-control"
                           value="{{ date('Y-m-d') }}"
                           required>
                </div>


                {{-- Latitude --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Latitude
                    </label>

                    <input type="text"
                           id="latitude"
                           name="latitude"
                           class="form-control"
                           readonly
                           required>
                </div>


                {{-- Longitude --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Longitude
                    </label>

                    <input type="text"
                           id="longitude"
                           name="longitude"
                           class="form-control"
                           readonly
                           required>
                </div>


                {{-- GPS Status --}}
                <div class="col-12">

                    <div class="alert alert-light border mb-0">

                        <span id="gpsStatus">
                            <i class="fas fa-spinner fa-spin me-2"></i>
                            Detecting GPS location...
                        </span>

                    </div>

                </div>


                {{-- Buttons --}}
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
```

</div>

{{-- GPS Detection --}}

<script>

if (navigator.geolocation) {

    navigator.geolocation.getCurrentPosition(

        function(position) {

            document.getElementById('latitude').value =
                position.coords.latitude.toFixed(6);

            document.getElementById('longitude').value =
                position.coords.longitude.toFixed(6);

            document.getElementById('gpsStatus').innerHTML =
                '<span class="text-success">' +
                '<i class="fas fa-check-circle me-2"></i>' +
                'GPS location captured successfully.' +
                '</span>';

        },

        function(error) {

            document.getElementById('gpsStatus').innerHTML =
                '<span class="text-danger">' +
                '<i class="fas fa-times-circle me-2"></i>' +
                'Unable to detect location. Please enable GPS.' +
                '</span>';

        }

    );

}
else {

    document.getElementById('gpsStatus').innerHTML =
        '<span class="text-danger">' +
        'Geolocation is not supported by this browser.' +
        '</span>';

}

</script>

{{-- Submit Form --}}

<script>

function submitAttendance(url)
{
    let staff = document.getElementById('staff_id').value;
    let lat = document.getElementById('latitude').value;
    let lng = document.getElementById('longitude').value;

    if (!staff)
    {
        alert('Please select a staff member.');
        return;
    }

    if (!lat || !lng)
    {
        alert('GPS location has not been captured.');
        return;
    }

    let form = document.getElementById('attendanceForm');

    form.action = url;

    form.submit();
}

</script>

@endsection
