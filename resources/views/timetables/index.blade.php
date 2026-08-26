@extends('layouts.master')

@section('title', 'Timetables')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3 mt-3">

        <h4>
            <i class="fas fa-calendar-alt text-primary"></i>
            Timetable Management
        </h4>

        <a href="{{ route('timetables.create') }}"
           class="btn btn-primary text-white">
           <i class="fas fa-plus-circle me-1"></i>
            Upload Timetable
        </a>

    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-striped">

                    <thead>

                        <tr>

                            <th>#</th>
                            <th>Title</th>
                            <th>Academic Year</th>
                            <th>Class</th>
                            <th>File Type</th>
                            <th>Uploaded</th>
                            <th width="250">Actions</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($timetables as $timetable)

                            <tr>

                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    {{ $timetable->title }}
                                </td>

                                <td>
                                    {{ $timetable->academicYear->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $timetable->studentClass->name ?? '-' }}
                                </td>

                                <td>
                                    <span class="badge bg-info">
                                        {{ strtoupper($timetable->file_type) }}
                                    </span>
                                </td>

                                <td>
                                    {{ $timetable->created_at->format('d M Y') }}
                                </td>

                                <td>

                                    <a href="{{ route('timetables.show',$timetable->id) }}"
                                       class="btn btn-sm btn-white text-dark">
                                        View
                                    </a>

                                    <a href="{{ route('timetables.download',$timetable->id) }}"
                                       class="btn btn-sm btn-white text-dark">
                                        Download
                                    </a>

                                    <form action="{{ route('timetables.destroy',$timetable->id) }}"
                                          method="POST"
                                          class="d-inline">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            onclick="return confirm('Delete timetable?')"
                                            class="btn btn-sm btn-white text-dark">

                                            Delete

                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="8" class="text-center">
                                    No timetables uploaded.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="mt-3">
                {{ $timetables->links() }}
            </div>

        </div>

    </div>

</div>

@endsection