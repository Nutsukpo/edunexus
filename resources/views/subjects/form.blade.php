<div class="row">

    {{-- SUBJECT NAME --}}
    <div class="col-md-6 mb-3">

        <label class="form-label fw-semibold">
            Subject Name
        </label>

        <input type="text"
               name="name"
               class="form-control"
               value="{{ old('name', $subject->name ?? '') }}"
               required>

    </div>

    {{-- SUBJECT CODE --}}
    <div class="col-md-6 mb-3">

        <label class="form-label fw-semibold">
            Subject Code
        </label>

        <input type="text"
               name="code"
               class="form-control"
               placeholder="e.g. ENG101"
               value="{{ old('code', $subject->code ?? '') }}">

    </div>

    {{-- EDUCATION LEVEL --}}
    <div class="col-md-6 mb-3">

        <label class="form-label fw-semibold">
            Education Level
        </label>

        <select name="education_level"
                class="form-select"
                required>

            <option value="">Select Level</option>

            @foreach([
                'Early Childhood',
                'Primary',
                'JHS',
                'SHS'
            ] as $level)

                <option value="{{ $level }}"
                    {{ old('education_level', $subject->education_level ?? '') == $level ? 'selected' : '' }}>

                    {{ $level }}

                </option>

            @endforeach

        </select>

    </div>

    {{-- CATEGORY --}}
    <div class="col-md-6 mb-3">

        <label class="form-label fw-semibold">
            Category
        </label>

        <select name="category"
                class="form-select"
                required>

            <option value="">Select Category</option>

            @foreach([
                'Core',
                'Elective',
                'Vocational',
                'Technical'
            ] as $category)

                <option value="{{ $category }}"
                    {{ old('category', $subject->category ?? '') == $category ? 'selected' : '' }}>

                    {{ $category }}

                </option>

            @endforeach

        </select>

    </div>

    {{-- ASSIGN STAFF --}}
    {{-- ASSIGN STAFF --}}
<div class="col-md-6 mb-3">

<label class="form-label fw-semibold">
    Assign Teacher / Staff
</label>

<select name="staff_id"
        class="form-select">

    <option value="">Select Staff</option>

    @foreach($staffs ?? [] as $staff)

        <option value="{{ $staff->id }}"
            {{ old('staff_id', $subject->staff_id ?? '') == $staff->id ? 'selected' : '' }}>

            {{ $staff->first_name ?? '' }}
            {{ $staff->last_name ?? '' }}

            @if($staff->staff_id)
                ({{ $staff->staff_id }})
            @endif

        </option>

    @endforeach

</select>

</div>

    {{-- DESCRIPTION --}}
    <div class="col-md-12 mb-3">

        <label class="form-label fw-semibold">
            Description
        </label>

        <textarea name="description"
                  rows="4"
                  class="form-control"
                  placeholder="Optional subject description...">{{ old('description', $subject->description ?? '') }}</textarea>

    </div>

    {{-- STATUS --}}
    <div class="col-md-12 mb-3">

        <div class="form-check">

            <input type="checkbox"
                   name="is_active"
                   value="1"
                   class="form-check-input"
                   {{ old('is_active', $subject->is_active ?? true) ? 'checked' : '' }}>

            <label class="form-check-label fw-semibold">
                Active Subject
            </label>

        </div>

    </div>

</div>