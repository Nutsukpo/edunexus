@extends('layouts.master')

@section('title', 'System Settings')

@section('content')
<div class="container-fluid py-4">

    <div class="mb-4">
        <div class="d-flex align-items-center mb-1">
            <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                <i class="fas fa-cogs fa-lg"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-1">System Settings</h3>
                <p class="text-muted mb-0">
                    Configure EDUNEXUS viewing defaults and the school's operational academic period.
                </p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm">
            <strong>Please correct the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                            <i class="fas fa-eye fa-lg"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Viewing / Default Period</small>
                            <h5 class="fw-bold mb-1 mt-1">
                                {{ $viewingAcademicYear?->name ?? 'Not configured' }}
                            </h5>
                            <div class="text-muted">{{ $viewingTerm?->name ?? 'Not configured' }}</div>
                            <span class="badge bg-primary-subtle text-primary mt-2">Safe to change for viewing</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="bg-success bg-opacity-10 text-success rounded-3 p-3 me-3">
                            <i class="fas fa-school fa-lg"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Operational School Period</small>
                            <h5 class="fw-bold mb-1 mt-1">
                                {{ $operationalAcademicYear?->name ?? 'Not configured' }}
                            </h5>
                            <div class="text-muted">{{ $operationalTerm?->name ?? 'Not configured' }}</div>
                            <span class="badge bg-success-subtle text-success mt-2">Actual school operating period</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-1">
                <i class="fas fa-eye text-primary me-2"></i>
                Viewing / Default Period
            </h5>
            <small class="text-muted">
                Used by modules that rely on EDUNEXUS's central viewing/default period.
            </small>
        </div>

        <div class="card-body">
            <div class="alert alert-primary border mb-4">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Safe viewing control.</strong>
                Changing this period does not activate or complete student assignments and does not change Bill Sheets or payments.
            </div>

            <form method="POST" action="{{ route('settings.update') }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="current_academic_year_id" class="form-label fw-semibold">
                            Viewing Academic Year
                        </label>
                        <select name="current_academic_year_id" id="current_academic_year_id" class="form-select" required>
                            <option value="">Select Academic Year</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}"
                                    {{ (int)$year->id === (int)$settings->current_academic_year_id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                    @if($year->start_date && $year->end_date)
                                        — {{ \Carbon\Carbon::parse($year->start_date)->format('d M Y') }}
                                        to {{ \Carbon\Carbon::parse($year->end_date)->format('d M Y') }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="current_term_id" class="form-label fw-semibold">
                            Viewing Term
                        </label>
                        <select name="current_term_id" id="current_term_id" class="form-select" required>
                            <option value="">Select Term</option>
                            @foreach($viewingTerms as $term)
                                <option value="{{ $term->id }}"
                                    {{ (int)$term->id === (int)$settings->current_term_id ? 'selected' : '' }}>
                                    {{ $term->name }}
                                    @if($term->start_date && $term->end_date)
                                        — {{ \Carbon\Carbon::parse($term->start_date)->format('d M Y') }}
                                        to {{ \Carbon\Carbon::parse($term->end_date)->format('d M Y') }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr class="my-4">

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"
                                       name="auto_assign_current_period"
                                       id="auto_assign_current_period"
                                       value="1"
                                       {{ $settings->auto_assign_current_period ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="auto_assign_current_period">
                                    Automatically use current period
                                </label>
                            </div>
                            <small class="text-muted d-block mt-2">
                                Controls module defaults. It does not activate prepared assignments.
                            </small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"
                                       name="use_current_period_defaults"
                                       id="use_current_period_defaults"
                                       value="1"
                                       {{ $settings->use_current_period_defaults ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="use_current_period_defaults">
                                    Use viewing period as module default
                                </label>
                            </div>
                            <small class="text-muted d-block mt-2">
                                Modules may use the viewing/default period as their initial filter.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-1"></i>
                        Save Viewing Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1">
                        <i class="fas fa-school text-success me-2"></i>
                        Operational School Period
                    </h5>
                    <small class="text-muted">
                        The period in which the school is actually operating.
                    </small>
                </div>

                <a href="{{ route('academic-period.open') }}" class="btn btn-outline-success">
                    <i class="fas fa-calendar-check me-1"></i>
                    Open Academic Period
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="alert alert-warning border mb-4">
                <i class="fas fa-shield-alt me-2"></i>
                <strong>This is separate from viewing.</strong>
                Changing the operational period does not automatically activate students.
                After changing it, use <strong>Open Academic Period</strong> to deliberately activate prepared assignments.
                Bill Sheets and payments are not rewritten.
            </div>

            <form method="POST"
                  action="{{ route('settings.update-operational-period') }}"
                  id="operationalPeriodForm">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="operational_academic_year_id" class="form-label fw-semibold">
                            Operational Academic Year
                        </label>

                        <select name="operational_academic_year_id"
                                id="operational_academic_year_id"
                                class="form-select"
                                required>
                            <option value="">Select Academic Year</option>

                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}"
                                    {{ (int)$year->id === (int)$settings->operational_academic_year_id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                    @if($year->start_date && $year->end_date)
                                        — {{ \Carbon\Carbon::parse($year->start_date)->format('d M Y') }}
                                        to {{ \Carbon\Carbon::parse($year->end_date)->format('d M Y') }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="operational_term_id" class="form-label fw-semibold">
                            Operational Term
                        </label>

                        <select name="operational_term_id"
                                id="operational_term_id"
                                class="form-select"
                                required>
                            <option value="">Select Term</option>

                            @foreach($operationalTerms as $term)
                                <option value="{{ $term->id }}"
                                    {{ (int)$term->id === (int)$settings->operational_term_id ? 'selected' : '' }}>
                                    {{ $term->name }}
                                    @if($term->start_date && $term->end_date)
                                        — {{ \Carbon\Carbon::parse($term->start_date)->format('d M Y') }}
                                        to {{ \Carbon\Carbon::parse($term->end_date)->format('d M Y') }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="operationalChangeWarning"
                     class="alert alert-danger border mt-4 d-none">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    You are changing the school's operational period.
                    Student assignments will not be activated automatically.
                    Use <strong>Open Academic Period</strong> afterward.
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4">
                    <a href="{{ route('academic-period.open') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list-check me-1"></i>
                        Review Prepared Assignments
                    </a>

                    <button type="submit" class="btn btn-success px-4">
                        <i class="fas fa-save me-1"></i>
                        Change Operational Period
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">
                <i class="fas fa-route text-primary me-2"></i>
                EDUNEXUS Academic Period Workflow
            </h5>
        </div>

        <div class="card-body">
            <div class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <div class="border rounded-3 p-3 h-100">
                        <span class="badge bg-primary mb-2">1</span>
                        <h6 class="fw-bold">Prepare</h6>
                        <p class="small text-muted mb-0">
                            Prepare future-term assignments and Bill Sheets without changing the operational period.
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="border rounded-3 p-3 h-100">
                        <span class="badge bg-info mb-2">2</span>
                        <h6 class="fw-bold">Set Operational Period</h6>
                        <p class="small text-muted mb-0">
                            Change the operational period only when the school officially moves into the new period.
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="border rounded-3 p-3 h-100">
                        <span class="badge bg-warning text-dark mb-2">3</span>
                        <h6 class="fw-bold">Review</h6>
                        <p class="small text-muted mb-0">
                            Review pending assignments before activation.
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="border rounded-3 p-3 h-100">
                        <span class="badge bg-success mb-2">4</span>
                        <h6 class="fw-bold">Open Period</h6>
                        <p class="small text-muted mb-0">
                            Explicitly activate prepared assignments while preserving financial history.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const viewingYear = document.getElementById('current_academic_year_id');
    const viewingTerm = document.getElementById('current_term_id');

    const operationalYear = document.getElementById('operational_academic_year_id');
    const operationalTerm = document.getElementById('operational_term_id');

    const operationalForm = document.getElementById('operationalPeriodForm');
    const operationalWarning = document.getElementById('operationalChangeWarning');

    function termsUrl(yearId) {
        return "{{ url('/settings/academic-years') }}/" + yearId + "/terms";
    }

    async function loadTerms(yearId, termSelect, selectedTermId = null) {

        if (!yearId || !termSelect) return;

        termSelect.innerHTML = '<option value="">Loading terms...</option>';
        termSelect.disabled = true;

        try {
            const response = await fetch(termsUrl(yearId), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('Unable to load terms.');
            }

            const terms = await response.json();

            termSelect.innerHTML = '<option value="">Select Term</option>';

            terms.forEach(function (term) {
                const option = document.createElement('option');
                option.value = term.id;

                let label = term.name;

                if (term.start_date && term.end_date) {
                    const start = new Date(term.start_date).toLocaleDateString('en-GB', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    });

                    const end = new Date(term.end_date).toLocaleDateString('en-GB', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    });

                    label += ' — ' + start + ' to ' + end;
                }

                option.textContent = label;

                if (selectedTermId && Number(selectedTermId) === Number(term.id)) {
                    option.selected = true;
                }

                termSelect.appendChild(option);
            });

        } catch (error) {
            termSelect.innerHTML = '<option value="">Unable to load terms</option>';
            console.error(error);
        } finally {
            termSelect.disabled = false;
        }
    }

    if (viewingYear) {
        viewingYear.addEventListener('change', function () {
            loadTerms(this.value, viewingTerm);
        });
    }

    if (operationalYear) {
        operationalYear.addEventListener('change', function () {
            loadTerms(this.value, operationalTerm);
            operationalWarning?.classList.remove('d-none');
        });
    }

    if (operationalTerm) {
        operationalTerm.addEventListener('change', function () {
            operationalWarning?.classList.remove('d-none');
        });
    }

    if (operationalForm) {
        operationalForm.addEventListener('submit', function (event) {
            const confirmed = window.confirm(
                'Change the operational school period?\n\n' +
                'This will NOT automatically activate student assignments. ' +
                'Use Open Academic Period afterward.\n\n' +
                'Bill Sheets and payments will not be changed.'
            );

            if (!confirmed) {
                event.preventDefault();
            }
        });
    }
});
</script>
@endpush
