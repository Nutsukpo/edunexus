<div class="row g-3">

    <div class="col-md-6">

        <label class="form-label fw-semibold">
            Latitude
        </label>

        <input type="text"
               name="latitude"
               class="form-control"
               value="{{ old('latitude', $setting->latitude ?? '') }}"
               required>

    </div>

    <div class="col-md-6">

        <label class="form-label fw-semibold">
            Longitude
        </label>

        <input type="text"
               name="longitude"
               class="form-control"
               value="{{ old('longitude', $setting->longitude ?? '') }}"
               required>

    </div>

    <div class="col-md-6">

        <label class="form-label fw-semibold">
            Allowed Radius (Meters)
        </label>

        <input type="number"
               name="radius"
               class="form-control"
               value="{{ old('radius', $setting->radius ?? 100) }}"
               required>

    </div>

    <div class="col-md-6 d-flex align-items-center">

        <div class="form-check mt-4">

            <input type="checkbox"
                   class="form-check-input"
                   name="gps_enabled"
                   value="1"
                   {{ old('gps_enabled', $setting->gps_enabled ?? true) ? 'checked' : '' }}>

            <label class="form-check-label">
                Enable GPS Attendance
            </label>

        </div>

    </div>

    <div class="col-md-3">

        <label class="form-label">
            Clock In Start
        </label>

        <input type="time"
               name="clock_in_start"
               class="form-control"
               value="{{ old('clock_in_start', $setting->clock_in_start ?? '') }}">

    </div>

    <div class="col-md-3">

        <label class="form-label">
            Clock In End
        </label>

        <input type="time"
               name="clock_in_end"
               class="form-control"
               value="{{ old('clock_in_end', $setting->clock_in_end ?? '') }}">

    </div>

    <div class="col-md-3">

        <label class="form-label">
            Clock Out Start
        </label>

        <input type="time"
               name="clock_out_start"
               class="form-control"
               value="{{ old('clock_out_start', $setting->clock_out_start ?? '') }}">

    </div>

    <div class="col-md-3">

        <label class="form-label">
            Clock Out End
        </label>

        <input type="time"
               name="clock_out_end"
               class="form-control"
               value="{{ old('clock_out_end', $setting->clock_out_end ?? '') }}">

    </div>

</div>