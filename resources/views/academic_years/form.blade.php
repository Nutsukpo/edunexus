<div class="row">

    {{-- Academic Year Name --}}
    <div class="col-md-6 mb-3">

        <label class="form-label">
            Academic Year Name
        </label>

        <input type="text"
               name="name"
               class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $academicYear->name ?? '') }}"
               placeholder="Example: 2025/2026">

        @error('name')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>

    {{-- Start Date --}}
    <div class="col-md-6 mb-3">

        <label class="form-label">
            Start Date
        </label>

        <input type="date"
               name="start_date"
               class="form-control @error('start_date') is-invalid @enderror"
               value="{{ old('start_date', $academicYear->start_date ?? '') }}">

        @error('start_date')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>

    {{-- End Date --}}
    <div class="col-md-6 mb-3">

        <label class="form-label">
            End Date
        </label>

        <input type="date"
               name="end_date"
               class="form-control @error('end_date') is-invalid @enderror"
               value="{{ old('end_date', $academicYear->end_date ?? '') }}">

        @error('end_date')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>

    {{-- Active Status --}}
    <div class="col-md-6 mb-3">

        <label class="form-label d-block">
            Status
        </label>

        <div class="form-check form-switch">

            <input class="form-check-input"
                   type="checkbox"
                   name="is_active"
                   value="1"
                   id="is_active"
                   {{ old('is_active', $academicYear->is_active ?? false) ? 'checked' : '' }}>

            <label class="form-check-label" for="is_active">
                Active Academic Year
            </label>

        </div>

    </div>

</div>

<div class="mt-4">

    <button type="submit"
            class="btn btn-primary text-white">
        Save Academic Year
    </button>

    <a href="{{ route('academic-years.index') }}"
       class="btn btn-light">
        Cancel
    </a>

</div>