<div class="row">

    {{-- Academic Year --}}
    <div class="col-md-6 mb-3">

        <label class="form-label">
            Academic Year
        </label>

        <select name="academic_year_id"
                class="form-select @error('academic_year_id') is-invalid @enderror">

            <option value="">
                Select Academic Year
            </option>

            @foreach($academicYears as $year)

                <option value="{{ $year->id }}"
                    {{ old('academic_year_id', $term->academic_year_id ?? '') == $year->id ? 'selected' : '' }}>

                    {{ $year->name }}

                </option>

            @endforeach

        </select>

        @error('academic_year_id')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>

    {{-- Term Name --}}
    <div class="col-md-6 mb-3">

        <label class="form-label">
            Term Name
        </label>

        <input type="text"
               name="name"
               class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $term->name ?? '') }}"
               placeholder="Example: First Term">

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
               value="{{ old('start_date', isset($term) && $term->start_date ? $term->start_date->format('Y-m-d') : '') }}">

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
               value="{{ old('end_date', isset($term) && $term->end_date ? $term->end_date->format('Y-m-d') : '') }}">

        @error('end_date')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>

    {{-- Status --}}
    <div class="col-md-6 mb-3">

        <label class="form-label d-block">
            Status
        </label>

        <div class="form-check form-switch">

            <input type="checkbox"
                   name="is_active"
                   value="1"
                   class="form-check-input"
                   id="is_active"
                   {{ old('is_active', $term->is_active ?? false) ? 'checked' : '' }}>

            <label class="form-check-label" for="is_active">
                Active Term
            </label>

        </div>

    </div>

</div>

<div class="mt-4">

    <button type="submit"
            class="btn btn-primary">
        Save Term
    </button>

    <a href="{{ route('terms.index') }}"
       class="btn btn-secondary">
        Cancel
    </a>

</div>