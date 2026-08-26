@extends('layouts.app')

@section('title', 'Approved Lesson Notes')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>Approved Lesson Notes</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('lesson-notes.approvals.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Approval List
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('lesson-notes.approvals.approved') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="staff_id" class="form-label">Staff</label>
                    <select name="staff_id" id="staff_id" class="form-select">
                        <option value="">All Staff</option>
                        @foreach($staffs as $staff)
                            <option value="{{ $staff->id }}" {{ request('staff_id') == $staff->id ? 'selected' : '' }}>
                                {{ $staff->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="student_class_id" class="form-label">Class</label>
                    <select name="student_class_id" id="student_class_id" class="form-select">
                        <option value="">All Classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('student_class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="subject_id" class="form-label">Subject</label>
                    <select name="subject_id" id="subject_id" class="form-select">
                        <option value="">All Subjects</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Approved Lesson Notes List -->
    <div class="card">
        <div class="card-body">
            @if($lessonNotes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Note Code</th>
                                <th>Topic</th>
                                <th>Staff</th>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Approved At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lessonNotes as $index => $note)
                                <tr>
                                    <td>{{ $lessonNotes->firstItem() + $index }}</td>
                                    <td><strong>{{ $note->note_code }}</strong></td>
                                    <td>
                                        <span title="{{ $note->sub_topic ?? '' }}">
                                            {{ Str::limit($note->topic, 30) }}
                                        </span>
                                    </td>
                                    <td>{{ $note->staff->full_name ?? 'N/A' }}</td>
                                    <td>{{ $note->studentClass->name ?? 'N/A' }}</td>
                                    <td>{{ $note->subject->name ?? 'N/A' }}</td>
                                    <td>{{ $note->approved_at ? $note->approved_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('lesson-notes.approvals.show', $note->id) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center">
                    {{ $lessonNotes->appends(request()->query())->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No approved lesson notes found.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection