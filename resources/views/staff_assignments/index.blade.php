@extends('layouts.master')

@section('title', 'Staff Assignments')

@section('content')

<div class="container-fluid py-4">

    {{-- ================================================================
        PAGE HEADER
    ================================================================= --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="fas fa-user-tie me-2"></i>
                Staff Assignments
            </h4>

            <p class="text-muted mb-0">
                Manage Form Masters and Subject Teachers for the
                operational academic period.
            </p>
        </div>

        <a href="{{ url()->previous() }}"
           class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Back
        </a>
    </div>


    {{-- ================================================================
        FLASH MESSAGES
    ================================================================= --}}
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

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>
                <i class="fas fa-exclamation-triangle me-1"></i>
                Please correct the following:
            </strong>

            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- ================================================================
        OPERATIONAL PERIOD
    ================================================================= --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="row align-items-center">

                <div class="col-md-8">

                    <div class="d-flex align-items-center">

                        <div class="bg-primary bg-opacity-10
                                    text-primary rounded-circle
                                    p-3 me-3">

                            <i class="fas fa-calendar-alt fa-lg"></i>

                        </div>

                        <div>

                            <div class="text-muted small">
                                CURRENT OPERATIONAL PERIOD
                            </div>

                            @if($academicYear && $term)

                                <h5 class="mb-0">
                                    {{ $academicYear->name }}
                                    —
                                    {{ $term->name }}
                                </h5>

                                <small class="text-muted">
                                    Staff assignments made here apply
                                    specifically to this period.
                                </small>

                            @else

                                <h5 class="text-danger mb-1">
                                    No Operational Period
                                </h5>

                                <small class="text-muted">
                                    Configure an operational academic
                                    year and term before assigning staff.
                                </small>

                            @endif

                        </div>

                    </div>

                </div>

                <div class="col-md-4 text-md-end mt-3 mt-md-0">

                    @if($academicYear && $term)

                        <span class="badge bg-success px-3 py-2">
                            <i class="fas fa-circle me-1"
                               style="font-size:7px;"></i>
                            Operational
                        </span>

                    @else

                        <span class="badge bg-danger px-3 py-2">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            Not Configured
                        </span>

                    @endif

                </div>

            </div>

        </div>

    </div>


    @if($academicYear && $term)

        {{-- ============================================================
            ASSIGNMENT TABS
        ============================================================= --}}

        <ul class="nav nav-tabs mb-4"
            id="assignmentTabs"
            role="tablist">

            <li class="nav-item" role="presentation">

                <button class="nav-link active text-success"
                        id="form-master-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#form-master"
                        type="button"
                        role="tab">

                    <i class="fas fa-chalkboard-teacher me-1"></i>
                    Form Masters

                </button>

            </li>

            <li class="nav-item" role="presentation">

                <button class="nav-link"
                        id="subject-teacher-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#subject-teacher"
                        type="button"
                        role="tab">

                    <i class="fas fa-book-reader me-1"></i>
                    Subject Teachers

                </button>

            </li>

        </ul>


        <div class="tab-content" id="assignmentTabsContent">


            {{-- ========================================================
                FORM MASTER TAB
            ========================================================= --}}

            <div class="tab-pane fade show active"
                 id="form-master"
                 role="tabpanel">

                <div class="row">

                    {{-- Assignment form --}}
                    <div class="col-lg-5 mb-4">

                        <div class="card border-0 shadow-sm h-100">

                            <div class="card-header bg-white py-3">

                                <h6 class="mb-0">
                                    <i class="fas fa-user-tie text-primary me-2"></i>
                                    Assign Form Master
                                </h6>

                            </div>

                            <div class="card-body">

                                <p class="text-muted small mb-4">
                                    A Form Master has full access to
                                    the assigned class, subject to
                                    their system permissions.
                                </p>

                                <form method="POST"
                                      action="{{ route('staff-assignments.form-master.store') }}">

                                    @csrf

                                    {{-- Staff --}}
                                    <div class="mb-3">

                                        <label class="form-label fw-semibold">
                                            Staff
                                        </label>

                                        <select name="staff_id"
                                                class="form-select"
                                                required>

                                            <option value="">
                                                Select Form Master
                                            </option>

                                            @foreach($staff as $member)

                                                <option value="{{ $member->id }}"
                                                    {{ old('staff_id') == $member->id ? 'selected' : '' }}>

                                                    {{ $member->full_name }}

                                                    @if($member->staff_id)
                                                        — {{ $member->staff_id }}
                                                    @endif

                                                </option>

                                            @endforeach

                                        </select>

                                    </div>


                                    {{-- Class --}}
                                    <div class="mb-4">

                                        <label class="form-label fw-semibold">
                                            Class
                                        </label>

                                        <select name="student_class_id"
                                                class="form-select"
                                                required>

                                            <option value="">
                                                Select Class
                                            </option>

                                            @foreach($classes as $class)

                                                <option value="{{ $class->id }}"
                                                    {{ old('student_class_id') == $class->id ? 'selected' : '' }}>

                                                    {{ $class->full_name }}

                                                </option>

                                            @endforeach

                                        </select>

                                    </div>


                                    <button type="submit"
                                            class="btn btn-primary w-100">

                                        <i class="fas fa-user-plus me-1"></i>
                                        Assign Form Master

                                    </button>

                                </form>

                            </div>

                        </div>

                    </div>


                    {{-- Existing assignments --}}
                    <div class="col-lg-7 mb-4">

                        <div class="card border-0 shadow-sm">

                            <div class="card-header bg-white py-3">

                                <div class="d-flex justify-content-between">

                                    <h6 class="mb-0">
                                        <i class="fas fa-list me-2"></i>
                                        Current Form Master Assignments
                                    </h6>

                                    <span class="badge bg-primary">
                                        {{ $formMasterAssignments->count() }}
                                    </span>

                                </div>

                            </div>

                            <div class="card-body p-0">

                                @if($formMasterAssignments->count())

                                    <div class="table-responsive">

                                        <table class="table table-hover
                                                      align-middle mb-0">

                                            <thead class="table-light">

                                                <tr>
                                                    <th>Class</th>
                                                    <th>Form Master</th>
                                                    <th class="text-end">
                                                        Action
                                                    </th>
                                                </tr>

                                            </thead>

                                            <tbody>

                                                @foreach($formMasterAssignments as $assignment)

                                                    <tr>

                                                        <td>
                                                            <strong>
                                                                {{ $assignment->studentClass?->full_name ?? 'N/A' }}
                                                            </strong>

                                                            <div class="small text-muted">
                                                                {{ $assignment->studentClass?->student_class_code }}
                                                            </div>
                                                        </td>

                                                        <td>

                                                            {{ $assignment->staff?->full_name ?? 'N/A' }}

                                                            @if($assignment->staff?->staff_id)

                                                                <div class="small text-muted">
                                                                    {{ $assignment->staff->staff_id }}
                                                                </div>

                                                            @endif

                                                        </td>

                                                        <td class="text-end">

                                                            <form method="POST"
                                                                  action="{{ route('staff-assignments.form-master.destroy', $assignment) }}"
                                                                  onsubmit="return confirm('Remove this Form Master assignment?');">

                                                                @csrf
                                                                @method('DELETE')

                                                                <button type="submit"
                                                                        class="btn btn-sm btn-outline-danger">

                                                                    <i class="fas fa-trash"></i>

                                                                </button>

                                                            </form>

                                                        </td>

                                                    </tr>

                                                @endforeach

                                            </tbody>

                                        </table>

                                    </div>

                                @else

                                    <div class="text-center py-5">

                                        <i class="fas fa-user-tie
                                                  fa-2x text-muted mb-3"></i>

                                        <p class="text-muted mb-0">
                                            No Form Masters have been
                                            assigned for this period.
                                        </p>

                                    </div>

                                @endif

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ========================================================
                SUBJECT TEACHER TAB
            ========================================================= --}}

            <div class="tab-pane fade"
                 id="subject-teacher"
                 role="tabpanel">

                <div class="row">

                    {{-- Assignment form --}}
                    <div class="col-lg-5 mb-4">

                        <div class="card border-0 shadow-sm h-100">

                            <div class="card-header bg-white py-3">

                                <h6 class="mb-0">
                                    <i class="fas fa-book text-success me-2"></i>
                                    Assign Subject Teacher
                                </h6>

                            </div>

                            <div class="card-body">

                                <p class="text-muted small mb-4">
                                    A Subject Teacher has read access
                                    to the assigned class and write
                                    access only to the assigned subject.
                                </p>

                                <form method="POST"
                                      action="{{ route('staff-assignments.subject-teacher.store') }}">

                                    @csrf

                                    {{-- Staff --}}
                                    <div class="mb-3">

                                        <label class="form-label fw-semibold">
                                            Staff
                                        </label>

                                        <select name="staff_id"
                                                class="form-select"
                                                required>

                                            <option value="">
                                                Select Subject Teacher
                                            </option>

                                            @foreach($staff as $member)

                                                <option value="{{ $member->id }}">

                                                    {{ $member->full_name }}

                                                    @if($member->staff_id)
                                                        — {{ $member->staff_id }}
                                                    @endif

                                                </option>

                                            @endforeach

                                        </select>

                                    </div>


                                    {{-- Class --}}
                                    <div class="mb-3">

                                        <label class="form-label fw-semibold">
                                            Class
                                        </label>

                                        <select name="student_class_id"
                                                class="form-select"
                                                required>

                                            <option value="">
                                                Select Class
                                            </option>

                                            @foreach($classes as $class)

                                                <option value="{{ $class->id }}">

                                                    {{ $class->full_name }}

                                                </option>

                                            @endforeach

                                        </select>

                                    </div>


                                    {{-- Subject --}}
                                    <div class="mb-4">

                                        <label class="form-label fw-semibold">
                                            Subject
                                        </label>

                                        <select name="subject_id"
                                                class="form-select"
                                                required>

                                            <option value="">
                                                Select Subject
                                            </option>

                                            @foreach($subjects as $subject)

                                                <option value="{{ $subject->id }}">

                                                    {{ $subject->name }}

                                                    @if($subject->code)
                                                        — {{ $subject->code }}
                                                    @endif

                                                </option>

                                            @endforeach

                                        </select>

                                    </div>


                                    <button type="submit"
                                            class="btn btn-success w-100">

                                        <i class="fas fa-user-plus me-1"></i>
                                        Assign Subject Teacher

                                    </button>

                                </form>

                            </div>

                        </div>

                    </div>


                    {{-- Existing assignments --}}
                    <div class="col-lg-7 mb-4">

                        <div class="card border-0 shadow-sm">

                            <div class="card-header bg-white py-3">

                                <div class="d-flex justify-content-between">

                                    <h6 class="mb-0">
                                        <i class="fas fa-list me-2"></i>
                                        Current Subject Teacher Assignments
                                    </h6>

                                    <span class="badge bg-success">
                                        {{ $subjectTeacherAssignments->count() }}
                                    </span>

                                </div>

                            </div>

                            <div class="card-body p-0">

                                @if($subjectTeacherAssignments->count())

                                    <div class="table-responsive">

                                        <table class="table table-hover
                                                      align-middle mb-0">

                                            <thead class="table-light">

                                                <tr>
                                                    <th>Class</th>
                                                    <th>Subject</th>
                                                    <th>Staff</th>
                                                    <th class="text-end">
                                                        Action
                                                    </th>
                                                </tr>

                                            </thead>

                                            <tbody>

                                                @foreach($subjectTeacherAssignments as $assignment)

                                                    <tr>

                                                        <td>

                                                            <strong>
                                                                {{ $assignment->studentClass?->full_name ?? 'N/A' }}
                                                            </strong>

                                                        </td>

                                                        <td>

                                                            {{ $assignment->subject?->name ?? 'N/A' }}

                                                            @if($assignment->subject?->code)

                                                                <div class="small text-muted">
                                                                    {{ $assignment->subject->code }}
                                                                </div>

                                                            @endif

                                                        </td>

                                                        <td>

                                                            {{ $assignment->staff?->full_name ?? 'N/A' }}

                                                        </td>

                                                        <td class="text-end">

                                                            <form method="POST"
                                                                  action="{{ route('staff-assignments.subject-teacher.destroy', $assignment) }}"
                                                                  onsubmit="return confirm('Remove this Subject Teacher assignment?');">

                                                                @csrf
                                                                @method('DELETE')

                                                                <button type="submit"
                                                                        class="btn btn-sm btn-outline-danger">

                                                                    <i class="fas fa-trash"></i>

                                                                </button>

                                                            </form>

                                                        </td>

                                                    </tr>

                                                @endforeach

                                            </tbody>

                                        </table>

                                    </div>

                                @else

                                    <div class="text-center py-5">

                                        <i class="fas fa-book-reader
                                                  fa-2x text-muted mb-3"></i>

                                        <p class="text-muted mb-0">
                                            No Subject Teachers have been
                                            assigned for this period.
                                        </p>

                                    </div>

                                @endif

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    @else

        {{-- ============================================================
            NO OPERATIONAL PERIOD
        ============================================================= --}}

        <div class="card border-0 shadow-sm">

            <div class="card-body text-center py-5">

                <div class="mb-3">

                    <i class="fas fa-calendar-times
                              fa-3x text-warning"></i>

                </div>

                <h5>
                    Operational Academic Period Required
                </h5>

                <p class="text-muted mb-4">
                    Please configure the operational academic year
                    and term in System Settings before assigning staff.
                </p>

                <a href="{{ route('system-settings.index') }}"
                   class="btn btn-primary">

                    <i class="fas fa-cog me-1"></i>
                    Open System Settings

                </a>

            </div>

        </div>

    @endif

</div>

@endsection