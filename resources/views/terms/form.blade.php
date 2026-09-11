<div class="card border-0 shadow-sm">

    <div class="card-body p-4">

        {{-- Header --}}
        <div class="d-flex align-items-center mb-4">

            <div class="bg-primary bg-opacity-10 text-primary rounded-3
                        d-flex align-items-center justify-content-center me-3"
                 style="width:48px;height:48px;">

                <i class="fas fa-calendar-alt"></i>

            </div>

            <div>

                <h5 class="fw-bold mb-1">
                    Term Information
                </h5>

                <p class="text-muted small mb-0">
                    Configure the academic year, term period, dates and status.
                </p>

            </div>

        </div>


        <div class="row g-4">

            {{-- Academic Year --}}
            <div class="col-lg-6">

                <label for="academic_year_id"
                       class="form-label fw-semibold">

                    Academic Year
                    <span class="text-danger">*</span>

                </label>

                <select name="academic_year_id"
                        id="academic_year_id"
                        class="form-select @error('academic_year_id') is-invalid @enderror"
                        required>

                    <option value="">
                        Select Academic Year
                    </option>

                    @foreach($academicYears as $year)

                        <option value="{{ $year->id }}"
                            {{ old(
                                'academic_year_id',
                                $term->academic_year_id ?? ''
                            ) == $year->id ? 'selected' : '' }}>

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


            {{-- Term --}}
            <div class="col-lg-6">

                <label for="name"
                       class="form-label fw-semibold">

                    Term
                    <span class="text-danger">*</span>

                </label>

                <select name="name"
                        id="name"
                        class="form-select @error('name') is-invalid @enderror"
                        required>

                    <option value="">
                        Select Term
                    </option>

                    <option value="First Term"
                        {{ old(
                            'name',
                            $term->name ?? ''
                        ) === 'First Term' ? 'selected' : '' }}>

                        First Term

                    </option>

                    <option value="Second Term"
                        {{ old(
                            'name',
                            $term->name ?? ''
                        ) === 'Second Term' ? 'selected' : '' }}>

                        Second Term

                    </option>

                    <option value="Third Term"
                        {{ old(
                            'name',
                            $term->name ?? ''
                        ) === 'Third Term' ? 'selected' : '' }}>

                        Third Term

                    </option>

                </select>

                @error('name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

            </div>


            {{-- Start Date --}}
            <div class="col-lg-6">

                <label for="start_date"
                       class="form-label fw-semibold">

                    Start Date

                </label>

                <input type="date"
                       name="start_date"
                       id="start_date"
                       class="form-control @error('start_date') is-invalid @enderror"
                       value="{{ old(
                           'start_date',
                           isset($term) && $term->start_date
                               ? $term->start_date->format('Y-m-d')
                               : ''
                       ) }}">

                @error('start_date')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

            </div>


            {{-- End Date --}}
            <div class="col-lg-6">

                <label for="end_date"
                       class="form-label fw-semibold">

                    End Date

                </label>

                <input type="date"
                       name="end_date"
                       id="end_date"
                       class="form-control @error('end_date') is-invalid @enderror"
                       value="{{ old(
                           'end_date',
                           isset($term) && $term->end_date
                               ? $term->end_date->format('Y-m-d')
                               : ''
                       ) }}">

                @error('end_date')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

            </div>


            {{-- Status --}}
            <div class="col-12">

                <div class="border rounded-3 p-3 bg-light">

                    <div class="d-flex justify-content-between
                                align-items-center flex-wrap gap-3">

                        <div>

                            <div class="fw-semibold">
                                Term Status
                            </div>

                            <small class="text-muted">
                                Active terms can be selected in System Settings
                                and used by school modules.
                            </small>

                        </div>


                        <div class="form-check form-switch">

                            <input type="checkbox"
                                   name="is_active"
                                   value="1"
                                   id="is_active"
                                   class="form-check-input"
                                   style="width:2.5rem;height:1.25rem;"
                                   {{ old(
                                       'is_active',
                                       $term->is_active ?? false
                                   ) ? 'checked' : '' }}>

                            <label for="is_active"
                                   class="form-check-label fw-semibold ms-2">

                                Active

                            </label>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <hr class="my-4">


        {{-- Actions --}}
        <div class="d-flex justify-content-between
                    align-items-center flex-wrap gap-2">

            <div class="small text-muted">

                <i class="fas fa-info-circle me-1"></i>

                The term names must remain consistent because
                academic workflows use them.

            </div>


            <div class="d-flex gap-2">

                <a href="{{ route('terms.index') }}"
                   class="btn btn-light border">

                    <i class="fas fa-times me-1"></i>
                    Cancel

                </a>

                <button type="submit"
                        class="btn btn-primary px-4">

                    <i class="fas fa-save me-1"></i>
                    Save Term

                </button>

            </div>

        </div>

    </div>

</div>