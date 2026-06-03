<div class="row">

    {{-- CLASS NAME --}}
    <div class="col-md-6 mb-3">

        <label class="form-label">Class Name</label>

        <input type="text"
               name="name"
               class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $studentClass->name ?? '') }}"
               placeholder="e.g. JHS 1"
               required>

        @error('name')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>

    {{-- EDUCATION TYPE --}}
    <div class="col-md-6 mb-3">

        <label class="form-label">Education Type</label>

        <select name="education_type"
                class="form-control @error('education_type') is-invalid @enderror"
                required>

            <option value="">Select Education Type</option>

            <option value="Early Childhood Education"
                {{ old('education_type', $studentClass->education_type ?? '') == 'Early Childhood Education' ? 'selected' : '' }}>
                Early Childhood Education
            </option>

            <option value="Basic Education"
                {{ old('education_type', $studentClass->education_type ?? '') == 'Basic Education' ? 'selected' : '' }}>
                Basic Education
            </option>

            <option value="Junior High School (JHS)"
                {{ old('education_type', $studentClass->education_type ?? '') == 'Junior High School (JHS)' ? 'selected' : '' }}>
                Junior High School (JHS)
            </option>

        </select>

        @error('education_type')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>

    {{-- CLASS TYPE --}}
    <div class="col-md-6 mb-3">

        <label class="form-label">Class Type</label>

        <select name="class_type"
                class="form-control @error('class_type') is-invalid @enderror"
                required>

            <option value="">Select Class Type</option>

            <option value="Kindergarten (KG)">Kindergarten (KG)</option>

            <option value="Lower Primary">Lower Primary</option>

            <option value="Higher Primary">Higher Primary</option>

            <option value="JHS">JHS</option>

        </select>

        @error('class_type')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>

    {{-- STREAM --}}
    <div class="col-md-6 mb-3">

        <label class="form-label">Stream</label>

        <select name="stream"
                class="form-control @error('stream') is-invalid @enderror">

            <option value="">Select Stream</option>

            <option value="A"
                {{ old('stream', $studentClass->stream ?? '') == 'A' ? 'selected' : '' }}>
                A
            </option>

            <option value="B"
                {{ old('stream', $studentClass->stream ?? '') == 'B' ? 'selected' : '' }}>
                B
            </option>

            <option value="C"
                {{ old('stream', $studentClass->stream ?? '') == 'C' ? 'selected' : '' }}>
                C
            </option>

        </select>

    </div>

    {{-- ACADEMIC YEAR --}}
    <div class="col-md-6 mb-3">

        <label class="form-label">Academic Year</label>

        <select name="academic_year_id"
                class="form-control @error('academic_year_id') is-invalid @enderror"
                required>

            <option value="">Select Academic Year</option>

            @foreach($academicYears as $year)

                <option value="{{ $year->id }}"
                    {{ old('academic_year_id', $studentClass->academic_year_id ?? '') == $year->id ? 'selected' : '' }}>

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

    {{-- CLASS TEACHER --}}
    <div class="col-md-6 mb-3">

        <label class="form-label">Class Teacher</label>

        <select name="staff_id"
        class="form-control @error('staff_id') is-invalid @enderror"  >

            <option value="">Select Teacher</option>

            @foreach($staff as $teacher)

                <option value="{{ $teacher->id }}"
                    {{ old('staff_id', $studentClass->staff_id ?? '') == $teacher->id ? 'selected' : '' }}>

                    {{ $teacher->first_name }} {{ $teacher->last_name }}

                </option>

            @endforeach

        </select>

        @error('staff_id')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>

    {{-- CAPACITY --}}
    <div class="col-md-6 mb-3">

        <label class="form-label">Class Capacity</label>

        <input type="number"
               name="capacity"
               class="form-control @error('capacity') is-invalid @enderror"
               min="1"
               value="{{ old('capacity', $studentClass->capacity ?? '') }}"
               placeholder="e.g. 40">

        @error('capacity')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>

</div>