@extends('layouts.master')

@section('title', 'Lesson Notes')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Lesson Notes</h3>
                        <div>
                            <a href="{{ route('lesson-notes.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add New Lesson Note
                            </a>
                        </div>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <!-- Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form action="{{ route('lesson-notes.index') }}" method="GET" class="form-inline">
                                <div class="row w-100">
                                    <div class="col-md-3 mb-2">
                                        <select name="staff_id" class="form-control form-control-sm w-100">
                                            <option value="">All Staff</option>
                                            @foreach($staffs as $staff)
                                                <option value="{{ $staff->id }}" {{ request('staff_id') == $staff->id ? 'selected' : '' }}>
                                                    {{ $staff->full_name ?? $staff->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <select name="student_class_id" class="form-control form-control-sm w-100">
                                            <option value="">All Classes</option>
                                            @foreach($classes as $class)
                                                <option value="{{ $class->id }}" {{ request('student_class_id') == $class->id ? 'selected' : '' }}>
                                                    {{ $class->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <select name="subject_id" class="form-control form-control-sm w-100">
                                            <option value="">All Subjects</option>
                                            @foreach($subjects as $subject)
                                                <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                                    {{ $subject->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <select name="type" class="form-control form-control-sm w-100">
                                            <option value="">All Types</option>
                                            <option value="daily" {{ request('type') == 'daily' ? 'selected' : '' }}>Daily</option>
                                            <option value="weekly" {{ request('type') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                            <option value="monthly" {{ request('type') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                            <option value="termly" {{ request('type') == 'termly' ? 'selected' : '' }}>Termly</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('search') }}">
                                    </div>
                                    <div class="col-md-1 mb-2">
                                        <button type="submit" class="btn btn-sm btn-primary w-100">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Note Code</th>
                                    <th>Teacher</th>
                                    <th>Class</th>
                                    <th>Subject</th>
                                    <th>Topic</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Comments</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lessonNotes as $key => $note)
                                <tr>
                                    <td>{{ $lessonNotes->firstItem() + $key }}</td>
                                    <td>{{ $note->note_code }}</td>
                                    <td>{{ $note->staff->full_name ?? $note->staff->name ?? 'N/A' }}</td>
                                    <td>{{ $note->studentClass->name ?? 'N/A' }}</td>
                                    <td>{{ $note->subject->name ?? 'N/A' }}</td>
                                    <td>
                                        <strong>{{ $note->topic }}</strong>
                                        @if($note->sub_topic)
                                            <br><small class="text-muted">{{ $note->sub_topic }}</small>
                                        @endif
                                    </td>
                                    <td>{{ ucfirst($note->type) }}</td>
                                    <td>{{ $note->lesson_date ? $note->lesson_date->format('d/m/Y') : 'N/A' }}</td>
                                    <td>
                                        @if($note->status)
                                            {{ ucfirst($note->status) }}
                                        @else
                                            Pending
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($note->comment))
                                            {{ $note->comment }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('lesson-notes.show', $note->id) }}" class="btn btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('lesson-notes.edit', $note->id) }}" class="btn btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger" onclick="confirmDelete({{ $note->id }})" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button type="button" class="btn btn-success" onclick="cloneNote({{ $note->id }})" title="Clone">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center">
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-info-circle"></i> No lesson notes found.
                                            <a href="{{ route('lesson-notes.create') }}" class="alert-link">Create your first lesson note</a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($lessonNotes->hasPages())
                        <div class="row">
                            <div class="col-12">
                                {{ $lessonNotes->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @endif
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="delete-form" action="" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Clone Form -->
<form id="clone-form" action="" method="POST" style="display: none;">
    @csrf
</form>
@endsection

@push('scripts')
<script>
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this lesson note?')) {
            document.getElementById('delete-form').action = '{{ url("lesson-notes") }}/' + id;
            document.getElementById('delete-form').submit();
        }
    }

    function cloneNote(id) {
        if (confirm('Clone this lesson note?')) {
            document.getElementById('clone-form').action = '{{ url("lesson-notes") }}/' + id + '/clone';
            document.getElementById('clone-form').submit();
        }
    }
</script>
@endpush