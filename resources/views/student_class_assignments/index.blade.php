@extends('layouts.master')

@section('title', 'Student Class Assignments')

@section('content')

<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-users-class me-2 text-primary"></i>
                Student Class Assignments
            </h4>

            <p class="text-muted mb-0">
                Manage student class assignments and academic history.
            </p>
        </div>

        <a href="{{ route('student-class-assignments.create') }}"
           class="btn btn-primary">
            <i class="fas fa-user-plus me-1"></i>
            Assign Student
        </a>
    </div>


    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif


    {{-- Current Academic Period --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">

            <div class="row align-items-center">

                <div class="col-lg-6 mb-3 mb-lg-0">

                    <div class="d-flex align-items-center">

                        <div class="period-icon me-3">
                            <i class="fas fa-calendar-alt"></i>
                        </div>

                        <div>
                            <small class="text-muted d-block">
                                Current Academic Period
                            </small>

                            <div class="fw-bold">
                                @if($settings->currentAcademicYear)
                                    {{ $settings->currentAcademicYear->name }}
                                @else
                                    <span class="text-danger">
                                        Not configured
                                    </span>
                                @endif

                                @if($settings->currentTerm)
                                    <span class="text-muted mx-1">•</span>
                                    {{ $settings->currentTerm->name }}
                                @endif
                            </div>
                        </div>

                    </div>

                </div>

                <div class="col-lg-6 text-lg-end">

                    <a href="{{ route('settings.index') }}"
                       class="btn btn-sm btn-outline-primary">

                        <i class="fas fa-cog me-1"></i>
                        System Settings

                    </a>

                </div>

            </div>

        </div>
    </div>


    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white border-bottom py-3">

            <div class="d-flex align-items-center">

                <i class="fas fa-filter text-primary me-2"></i>

                <h6 class="fw-bold mb-0">
                    Filter Assignments
                </h6>

            </div>

        </div>

        <div class="card-body">

            <form method="GET"
                  action="{{ route('student-class-assignments.index') }}">

                <div class="row g-3">

                    {{-- Search --}}
                    <div class="col-lg-3 col-md-6">

                        <label class="form-label small fw-semibold">
                            Search Student
                        </label>

                        <div class="input-group">

                            <span class="input-group-text bg-white">
                                <i class="fas fa-search text-muted"></i>
                            </span>

                            <input type="text"
                                   name="search"
                                   class="form-control"
                                   placeholder="Name or student ID"
                                   value="{{ request('search') }}">

                        </div>

                    </div>


                    {{-- Academic Year --}}
                    <div class="col-lg-2 col-md-6">

                        <label class="form-label small fw-semibold">
                            Academic Year
                        </label>

                        <select name="academic_year_id"
                                id="filterAcademicYear"
                                class="form-select">

                            <option value="">
                                All Years
                            </option>

                            @foreach($academicYears as $year)

                                <option value="{{ $year->id }}"
                                    {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>

                                    {{ $year->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- Term --}}
                    <div class="col-lg-2 col-md-6">

                        <label class="form-label small fw-semibold">
                            Term
                        </label>

                        <select name="term_id"
                                id="filterTerm"
                                class="form-select">

                            <option value="">
                                All Terms
                            </option>

                            @foreach($terms as $term)

                                <option value="{{ $term->id }}"
                                    {{ request('term_id') == $term->id ? 'selected' : '' }}>

                                    {{ $term->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- Status --}}
                    <div class="col-lg-2 col-md-6">

                        <label class="form-label small fw-semibold">
                            Status
                        </label>

                        <select name="status"
                                class="form-select">

                            <option value="">
                                All Statuses
                            </option>

                            <option value="active"
                                {{ request('status') === 'active' ? 'selected' : '' }}>
                                Active
                            </option>

                            <option value="repeated"
                                {{ request('status') === 'repeated' ? 'selected' : '' }}>
                                Repeated
                            </option>

                            <option value="inactive"
                                {{ request('status') === 'inactive' ? 'selected' : '' }}>
                                Inactive
                            </option>

                            <option value="promoted"
                                {{ request('status') === 'promoted' ? 'selected' : '' }}>
                                Promoted
                            </option>

                            <option value="graduated"
                                {{ request('status') === 'graduated' ? 'selected' : '' }}>
                                Graduated
                            </option>

                        </select>

                    </div>


                    {{-- Current --}}
                    <div class="col-lg-1 col-md-6">

                        <label class="form-label small fw-semibold">
                            Current
                        </label>

                        <select name="is_current"
                                class="form-select">

                            <option value="">
                                All
                            </option>

                            <option value="1"
                                {{ request('is_current') === '1' ? 'selected' : '' }}>
                                Yes
                            </option>

                            <option value="0"
                                {{ request('is_current') === '0' ? 'selected' : '' }}>
                                No
                            </option>

                        </select>

                    </div>


                    {{-- Buttons --}}
                    <div class="col-lg-2 col-md-6 d-flex align-items-end gap-2">

                        <button type="submit"
                                class="btn btn-primary flex-grow-1">

                            <i class="fas fa-filter me-1"></i>
                            Apply

                        </button>

                        <a href="{{ route('student-class-assignments.index') }}"
                           class="btn btn-light border"
                           title="Clear filters">

                            <i class="fas fa-times"></i>

                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- Assignment Table --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white border-bottom py-3">

            <div class="d-flex flex-wrap justify-content-between align-items-center">

                <h6 class="fw-bold mb-0">

                    <i class="fas fa-list me-2 text-primary"></i>

                    Assignments

                    <span class="badge bg-primary bg-opacity-10 text-primary ms-2">
                        {{ $assignments->total() }}
                    </span>

                </h6>

                @if(request()->hasAny([
                    'search',
                    'academic_year_id',
                    'term_id',
                    'status',
                    'is_current'
                ]))

                    <span class="small text-muted">
                        Filtered results
                    </span>

                @endif

            </div>

        </div>


        <div class="card-body p-0">

            @if($assignments->count())

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th class="ps-4">
                                    Student
                                </th>

                                <th>
                                    Class
                                </th>

                                <th>
                                    Academic Year
                                </th>

                                <th>
                                    Term
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Current
                                </th>

                                <th>
                                    Assigned
                                </th>

                                <th class="text-end pe-4">
                                    Action
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach($assignments as $assignment)

                                <tr>

                                    {{-- Student --}}
                                    <td class="ps-4">

                                        <div class="d-flex align-items-center">

                                            <div class="student-avatar me-3">

                                                {{ strtoupper(
                                                    substr(
                                                        $assignment->student->first_name ?? 'S',
                                                        0,
                                                        1
                                                    )
                                                ) }}

                                            </div>

                                            <div>

                                                <div class="fw-semibold">

                                                    {{ $assignment->student->first_name ?? '' }}
                                                    {{ $assignment->student->middle_name ?? '' }}
                                                    {{ $assignment->student->last_name ?? '' }}

                                                </div>

                                                <small class="text-muted">

                                                    {{ $assignment->student->student_id ?? 'N/A' }}

                                                </small>

                                            </div>

                                        </div>

                                    </td>


                                    {{-- Class --}}
                                    <td>

                                        <span class="fw-semibold">

                                            {{ $assignment->studentClass->name ?? 'N/A' }}

                                        </span>

                                        @if(!empty($assignment->studentClass->stream))

                                            <small class="text-muted d-block">
                                                {{ $assignment->studentClass->stream }}
                                            </small>

                                        @endif

                                    </td>


                                    {{-- Academic Year --}}
                                    <td>

                                        {{ $assignment->academicYear->name ?? 'N/A' }}

                                        @if(
                                            $settings->current_academic_year_id &&
                                            $assignment->academic_year_id == $settings->current_academic_year_id
                                        )

                                            <span class="badge bg-primary-subtle text-primary ms-1">
                                                Current
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Term --}}
                                    <td>

                                        @if($assignment->term)

                                            {{ $assignment->term->name }}

                                            @if(
                                                $settings->current_term_id &&
                                                $assignment->term_id == $settings->current_term_id
                                            )

                                                <span class="badge bg-success-subtle text-success ms-1">
                                                    Current
                                                </span>

                                            @endif

                                        @else

                                            <span class="text-muted">
                                                Not recorded
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Status --}}
                                    <td>

                                        @switch($assignment->status)

                                            @case('active')

                                                <span class="badge bg-success">
                                                    Active
                                                </span>

                                                @break

                                            @case('repeated')

                                                <span class="badge bg-warning text-dark">
                                                    Repeated
                                                </span>

                                                @break

                                            @case('inactive')

                                                <span class="badge bg-secondary">
                                                    Inactive
                                                </span>

                                                @break

                                            @case('promoted')

                                                <span class="badge bg-info text-dark">
                                                    Promoted
                                                </span>

                                                @break

                                            @case('graduated')

                                                <span class="badge bg-dark">
                                                    Graduated
                                                </span>

                                                @break

                                            @default

                                                <span class="badge bg-secondary">
                                                    {{ ucfirst($assignment->status ?? 'Unknown') }}
                                                </span>

                                        @endswitch

                                    </td>


                                    {{-- Current --}}
                                    <td>

                                        @if($assignment->is_current)

                                            <span class="badge bg-success-subtle text-success">

                                                <i class="fas fa-check-circle me-1"></i>
                                                Yes

                                            </span>

                                        @else

                                            <span class="badge bg-light text-muted border">

                                                No

                                            </span>

                                        @endif

                                    </td>


                                    {{-- Assigned Date --}}
                                    <td>

                                        @if($assignment->assigned_date)

                                            <span title="{{ $assignment->assigned_date->format('Y-m-d H:i') }}">

                                                {{ $assignment->assigned_date->format('d M Y') }}

                                            </span>

                                        @else

                                            <span class="text-muted">
                                                —
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Action --}}
                                    <td class="text-end pe-4">

                                        <div class="dropdown">

                                            <button class="btn btn-sm btn-light border"
                                                    type="button"
                                                    data-bs-toggle="dropdown">

                                                <i class="fas fa-ellipsis-v"></i>

                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-end">

                                                <li>

                                                    <a class="dropdown-item"
                                                       href="{{ route(
                                                           'student-class-assignments.show',
                                                           $assignment->id
                                                       ) }}">

                                                        <i class="fas fa-eye me-2 text-primary"></i>
                                                        View

                                                    </a>

                                                </li>

                                                @if($assignment->status !== 'graduated')

                                                    <li>

                                                        <a class="dropdown-item"
                                                           href="{{ route(
                                                               'student-class-assignments.edit',
                                                               $assignment->id
                                                           ) }}">

                                                            <i class="fas fa-edit me-2 text-warning"></i>
                                                            Edit

                                                        </a>

                                                    </li>

                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>

                                                    @if($assignment->is_current)

                                                        <li>

                                                            <form method="POST"
                                                                  action="{{ route(
                                                                      'student-class-assignments.deactivate',
                                                                      $assignment->id
                                                                  ) }}">

                                                                @csrf
                                                                @method('PATCH')

                                                                <button type="submit"
                                                                        class="dropdown-item text-warning"
                                                                        onclick="return confirm('Deactivate this assignment?')">

                                                                    <i class="fas fa-pause-circle me-2"></i>
                                                                    Deactivate

                                                                </button>

                                                            </form>

                                                        </li>

                                                    @else

                                                        <li>

                                                            <form method="POST"
                                                                  action="{{ route(
                                                                      'student-class-assignments.reactivate',
                                                                      $assignment->id
                                                                  ) }}">

                                                                @csrf
                                                                @method('PATCH')

                                                                <button type="submit"
                                                                        class="dropdown-item text-success"
                                                                        onclick="return confirm('Reactivate this assignment?')">

                                                                    <i class="fas fa-play-circle me-2"></i>
                                                                    Reactivate

                                                                </button>

                                                            </form>

                                                        </li>

                                                    @endif

                                                    <li>

                                                        <form method="POST"
                                                              action="{{ route(
                                                                  'student-class-assignments.destroy',
                                                                  $assignment->id
                                                              ) }}">

                                                            @csrf
                                                            @method('DELETE')

                                                            <button type="submit"
                                                                    class="dropdown-item text-danger"
                                                                    onclick="return confirm('Delete this assignment? This action cannot be undone.')">

                                                                <i class="fas fa-trash-alt me-2"></i>
                                                                Delete

                                                            </button>

                                                        </form>

                                                    </li>

                                                @endif

                                            </ul>

                                        </div>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="text-center py-5">

                    <div class="empty-icon mb-3">

                        <i class="fas fa-user-graduate"></i>

                    </div>

                    <h6 class="fw-bold">
                        No assignments found
                    </h6>

                    <p class="text-muted mb-3">
                        No student class assignments match your current filters.
                    </p>

                    <a href="{{ route('student-class-assignments.create') }}"
                       class="btn btn-primary">

                        <i class="fas fa-user-plus me-1"></i>
                        Assign Student

                    </a>

                </div>

            @endif

        </div>


        {{-- Pagination --}}
        @if($assignments->hasPages())

            <div class="card-footer bg-white border-top py-3">

                {{ $assignments->links() }}

            </div>

        @endif

    </div>

</div>

@endsection


@push('styles')

<style>

    .card {
        border-radius: 12px;
    }

    .period-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(13, 110, 253, .10);
        color: #0d6efd;
    }

    .student-avatar {
        width: 40px;
        height: 40px;
        min-width: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(13, 110, 253, .10);
        color: #0d6efd;
        font-weight: 700;
    }

    .empty-icon {
        width: 64px;
        height: 64px;
        margin: auto;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(13, 110, 253, .08);
        color: #0d6efd;
        font-size: 26px;
    }

    .table > :not(caption) > * > * {
        padding-top: 14px;
        padding-bottom: 14px;
    }

    .badge {
        font-weight: 500;
    }

</style>

@endpush


@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const yearSelect = document.getElementById('filterAcademicYear');
    const termSelect = document.getElementById('filterTerm');

    if (!yearSelect || !termSelect) {
        return;
    }

    yearSelect.addEventListener('change', function () {

        const yearId = this.value;

        const selectedTerm =
            "{{ request('term_id') }}";

        termSelect.innerHTML =
            '<option value="">Loading terms...</option>';

        termSelect.disabled = true;

        if (!yearId) {

            termSelect.innerHTML =
                '<option value="">All Terms</option>';

            termSelect.disabled = false;

            return;
        }

        const url =
            "{{ route(
                'settings.academic-years.terms',
                ['academicYear' => '__YEAR__']
            ) }}"
            .replace('__YEAR__', yearId);

        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })

        .then(response => {

            if (!response.ok) {
                throw new Error('Unable to load terms.');
            }

            return response.json();

        })

        .then(terms => {

            termSelect.innerHTML =
                '<option value="">All Terms</option>';

            terms.forEach(function (item) {

                const option =
                    document.createElement('option');

                option.value = item.id;
                option.textContent = item.name;

                if (String(item.id) === String(selectedTerm)) {
                    option.selected = true;
                }

                termSelect.appendChild(option);

            });

            termSelect.disabled = false;

        })

        .catch(error => {

            console.error(error);

            termSelect.innerHTML =
                '<option value="">Unable to load terms</option>';

            termSelect.disabled = false;

        });

    });

});

</script>

@endpush