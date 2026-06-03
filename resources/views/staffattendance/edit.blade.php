@extends('layouts.master')

@section('title', 'Update Attendance')

@section('content')
<div class="container-fluid">

    <h3 class="mb-4">Edit Attendance</h3>

    {{-- CONTEXT INFO --}}
    <div class="card mb-3">
        <div class="card-body">

            <p><strong>Staff:</strong>
                {{ $attendance->staff->first_name ?? '' }}
                {{ $attendance->staff->last_name ?? '' }}
            </p>

            <p><strong>Date:</strong> {{ $attendance->date }}</p>

            <p><strong>Clock In:</strong> {{ $attendance->clock_in_time ?? '-' }}</p>

            <p><strong>Clock Out:</strong> {{ $attendance->clock_out_time ?? '-' }}</p>

        </div>
    </div>

    {{-- FORM --}}
    <div class="card">
        <div class="card-body">

            <form method="POST" action="{{ route('staffattendance.update', $attendance->id) }}">
                @csrf
                @method('PUT')

                {{-- STATUS --}}
                <div class="mb-3">
                    <label>Status</label>
                    <select name="status" class="form-control" required>

                        <option value="present"
                            {{ $attendance->status == 'present' ? 'selected' : '' }}>
                            Present
                        </option>

                        <option value="late"
                            {{ $attendance->status == 'late' ? 'selected' : '' }}>
                            Late
                        </option>

                        <option value="absent"
                            {{ $attendance->status == 'absent' ? 'selected' : '' }}>
                            Absent
                        </option>

                    </select>
                </div>

                {{-- GPS (VISIBLE, NOT HIDDEN) --}}
                <div class="mb-3">
                    <label>Latitude</label>
                    <input type="text" name="latitude" id="latitude"
                           class="form-control" readonly required>
                </div>

                <div class="mb-3">
                    <label>Longitude</label>
                    <input type="text" name="longitude" id="longitude"
                           class="form-control" readonly required>
                </div>

                <button class="btn btn-success">
                    Update Attendance
                </button>

            </form>

        </div>
    </div>

</div>

{{-- GPS SCRIPT --}}
<script>
navigator.geolocation.getCurrentPosition(function(pos){
    document.getElementById('latitude').value = pos.coords.latitude;
    document.getElementById('longitude').value = pos.coords.longitude;
});
</script>

@endsection