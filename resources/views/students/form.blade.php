<div class="row">

    {{-- ================= PHOTO ================= --}}
    <div class="col-md-6 mb-3">
        <label>Student Photo</label>

        <input type="file"
               name="photo"
               class="form-control @error('photo') is-invalid @enderror"
               accept="image/*">

        @error('photo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        @if(!empty($student->photo))
            <img src="{{ asset('storage/' . $student->photo) }}"
                 width="90"
                 class="mt-2 rounded border">
        @endif
    </div>

    {{-- ================= FIRST NAME ================= --}}
    <div class="col-md-6 mb-3">
        <label>First Name <span class="text-danger">*</span></label>

        <input type="text"
               name="first_name"
               class="form-control @error('first_name') is-invalid @enderror"
               value="{{ old('first_name', $student->first_name ?? '') }}"
               required>

        @error('first_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- ================= MIDDLE NAME ================= --}}
    <div class="col-md-6 mb-3">
        <label>Middle Name</label>

        <input type="text"
               name="middle_name"
               class="form-control @error('middle_name') is-invalid @enderror"
               value="{{ old('middle_name', $student->middle_name ?? '') }}">

        @error('middle_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- ================= LAST NAME ================= --}}
    <div class="col-md-6 mb-3">
        <label>Last Name <span class="text-danger">*</span></label>

        <input type="text"
               name="last_name"
               class="form-control @error('last_name') is-invalid @enderror"
               value="{{ old('last_name', $student->last_name ?? '') }}"
               required>

        @error('last_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- ================= GENDER ================= --}}
    <div class="col-md-6 mb-3">
        <label>Gender <span class="text-danger">*</span></label>

        <select name="gender"
                class="form-control @error('gender') is-invalid @enderror"
                required>

            <option value="">Select</option>
            <option value="Male" {{ old('gender', $student->gender ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
            <option value="Female" {{ old('gender', $student->gender ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
        </select>

        @error('gender')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- ================= DOB ================= --}}
    <div class="col-md-6 mb-3">
        <label>Date of Birth</label>

        <input type="date"
               name="date_of_birth"
               class="form-control @error('date_of_birth') is-invalid @enderror"
               value="{{ old('date_of_birth', isset($student->date_of_birth) ? \Carbon\Carbon::parse($student->date_of_birth)->format('Y-m-d') : '') }}">

        @error('date_of_birth')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- ================= NATIONALITY ================= --}}
    <div class="col-md-6 mb-3">
        <label>Nationality</label>

        <input type="text"
               name="nationality"
               class="form-control @error('nationality') is-invalid @enderror"
               value="{{ old('nationality', $student->nationality ?? '') }}">

        @error('nationality')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- ================= RELIGION ================= --}}
    <div class="col-md-6 mb-3">
        <label>Religion</label>

        <input type="text"
               name="religion"
               class="form-control @error('religion') is-invalid @enderror"
               value="{{ old('religion', $student->religion ?? '') }}">

        @error('religion')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- ================= ADDRESS ================= --}}
    <div class="col-md-12 mb-3">
        <label>Residential Address</label>

        <textarea name="address"
                  class="form-control @error('address') is-invalid @enderror"
                  rows="3">{{ old('address', $student->address ?? '') }}</textarea>

        @error('address')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    {{-- ================= DISABILITY ================= --}}
    <div class="col-md-6 mb-3">
        <label>Has Disability?</label>

        <select name="has_disability"
                class="form-control @error('has_disability') is-invalid @enderror">

            <option value="0" {{ old('has_disability', $student->has_disability ?? 0) == 0 ? 'selected' : '' }}>No</option>
            <option value="1" {{ old('has_disability', $student->has_disability ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
        </select>

        @error('has_disability')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label>Disability Type</label>

        <select name="disability_type"
                class="form-control @error('disability_type') is-invalid @enderror">

            <option value="">None</option>
            <option value="Visual Impairment" {{ old('disability_type', $student->disability_type ?? '') == 'Visual Impairment' ? 'selected' : '' }}>Visual Impairment</option>
            <option value="Hearing Impairment" {{ old('disability_type', $student->disability_type ?? '') == 'Hearing Impairment' ? 'selected' : '' }}>Hearing Impairment</option>
            <option value="Physical Disability" {{ old('disability_type', $student->disability_type ?? '') == 'Physical Disability' ? 'selected' : '' }}>Physical Disability</option>
            <option value="Intellectual Disability" {{ old('disability_type', $student->disability_type ?? '') == 'Intellectual Disability' ? 'selected' : '' }}>Intellectual Disability</option>
            <option value="Other" {{ old('disability_type', $student->disability_type ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
        </select>

        @error('disability_type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- ================= FATHER ================= --}}
    <div class="col-md-6 mb-3">
        <label>Father Name</label>

        <input type="text"
               name="father_name"
               class="form-control @error('father_name') is-invalid @enderror"
               value="{{ old('father_name', $student->father_name ?? '') }}">

        @error('father_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label>Father Phone</label>

        <input type="text"
               name="father_phone"
               class="form-control @error('father_phone') is-invalid @enderror"
               value="{{ old('father_phone', $student->father_phone ?? '') }}">

        @error('father_phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label>Father Email</label>

        <input type="email"
               name="father_email"
               class="form-control @error('father_email') is-invalid @enderror"
               value="{{ old('father_email', $student->father_email ?? '') }}">

        @error('father_email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label>Father Occupation</label>

        <input type="text"
               name="father_occupation"
               class="form-control @error('father_occupation') is-invalid @enderror"
               value="{{ old('father_occupation', $student->father_occupation ?? '') }}">

        @error('father_occupation')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- ================= MOTHER ================= --}}
    <div class="col-md-6 mb-3">
        <label>Mother Name</label>

        <input type="text"
               name="mother_name"
               class="form-control @error('mother_name') is-invalid @enderror"
               value="{{ old('mother_name', $student->mother_name ?? '') }}">

        @error('mother_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label>Mother Phone</label>

        <input type="text"
               name="mother_phone"
               class="form-control @error('mother_phone') is-invalid @enderror"
               value="{{ old('mother_phone', $student->mother_phone ?? '') }}">

        @error('mother_phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label>Mother Email</label>

        <input type="email"
               name="mother_email"
               class="form-control @error('mother_email') is-invalid @enderror"
               value="{{ old('mother_email', $student->mother_email ?? '') }}">

        @error('mother_email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label>Mother Occupation</label>

        <input type="text"
               name="mother_occupation"
               class="form-control @error('mother_occupation') is-invalid @enderror"
               value="{{ old('mother_occupation', $student->mother_occupation ?? '') }}">

        @error('mother_occupation')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- ================= GUARDIAN ================= --}}
    <div class="col-md-6 mb-3">
        <label>Guardian Name</label>

        <input type="text"
               name="guardian_name"
               class="form-control @error('guardian_name') is-invalid @enderror"
               value="{{ old('guardian_name', $student->guardian_name ?? '') }}">

        @error('guardian_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label>Guardian Phone</label>

        <input type="text"
               name="guardian_phone"
               class="form-control @error('guardian_phone') is-invalid @enderror"
               value="{{ old('guardian_phone', $student->guardian_phone ?? '') }}">

        @error('guardian_phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label>Guardian Email</label>

        <input type="email"
               name="guardian_email"
               class="form-control @error('guardian_email') is-invalid @enderror"
               value="{{ old('guardian_email', $student->guardian_email ?? '') }}">

        @error('guardian_email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- ================= ADMISSION DATE ================= --}}
    <div class="col-md-6 mb-3">
        <label>Admission Date <span class="text-danger">*</span></label>

        <input type="date"
               name="admission_date"
               class="form-control @error('admission_date') is-invalid @enderror"
               value="{{ old('admission_date', isset($student->admission_date) ? \Carbon\Carbon::parse($student->admission_date)->format('Y-m-d') : now()->format('Y-m-d')) }}"
               required>

        @error('admission_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

</div>