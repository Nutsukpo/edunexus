<div class="row">

    {{-- STUDENT --}}
    <div class="col-md-6 mb-3">

        <label>Student</label>

        <select name="student_id"
                class="form-control @error('student_id') is-invalid @enderror">

            <option value="">Select Student</option>

            @foreach($students as $student)

                <option value="{{ $student->id }}"
                    {{ old('student_id', $enrollment->student_id ?? '') == $student->id ? 'selected' : '' }}>

                    {{ $student->student_id }} -
                    {{ $student->full_name }}

                </option>

            @endforeach

        </select>

        @error('student_id')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>

    {{-- CLASS --}}
    <div class="col-md-6 mb-3">

        <label>Class</label>

        <select name="student_class_id"
                class="form-control @error('student_class_id') is-invalid @enderror">

            <option value="">Select Class</option>

            @foreach($classes as $class)

                <option value="{{ $class->id }}"
                    {{ old('student_class_id', $enrollment->student_class_id ?? '') == $class->id ? 'selected' : '' }}>

                    {{ $class->name }}

                </option>

            @endforeach

        </select>

        @error('student_class_id')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>

    {{-- ACADEMIC YEAR --}}
    <div class="col-md-6 mb-3">

        <label>Academic Year</label>

        <select name="academic_year_id"
                class="form-control @error('academic_year_id') is-invalid @enderror">

            <option value="">Select Academic Year</option>

            @foreach($academicYears as $year)

                <option value="{{ $year->id }}"
                    {{ old('academic_year_id', $enrollment->academic_year_id ?? '') == $year->id ? 'selected' : '' }}>

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

    {{-- ENROLLMENT DATE --}}
    <div class="col-md-6 mb-3">

        <label>Enrollment Date</label>

        <input type="date"
               name="enrollment_date"
               class="form-control @error('enrollment_date') is-invalid @enderror"
               value="{{ old('enrollment_date', $enrollment->enrollment_date ?? '') }}">

        @error('enrollment_date')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>

    {{-- STATUS --}}
    <div class="col-md-6 mb-3">

        <label>Status</label>

        <select name="is_active"
                class="form-control @error('is_active') is-invalid @enderror">

            <option value="1"
                {{ old('is_active', $enrollment->is_active ?? 1) == 1 ? 'selected' : '' }}>
                Active
            </option>

            <option value="0"
                {{ old('is_active', $enrollment->is_active ?? 1) == 0 ? 'selected' : '' }}>
                Inactive
            </option>

        </select>

        @error('is_active')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>

    {{-- REMARKS --}}
    <div class="col-md-12 mb-3">

        <label>Remarks</label>

        <textarea name="remarks"
                  rows="3"
                  class="form-control @error('remarks') is-invalid @enderror">{{ old('remarks', $enrollment->remarks ?? '') }}</textarea>

        @error('remarks')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror

    </div>

</div>