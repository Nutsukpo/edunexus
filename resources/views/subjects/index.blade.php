@extends('layouts.master')

@section('title', 'Subjects')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <h5 class="fw-bold mt-3">Subjects</h5>
        <a href="{{ route('subjects.create') }}"
           class="btn btn-light text-dark">

            <i class="fas fa-plus"></i> Add Subject
        </a>
    </div>

    <div class="card border-0 shadow-sm">

        <div class="card-body table-responsive">

            <table class="table table-bordered align-middle">

                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Level</th>
                        <th>Teacher Assign</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($subjects as $subject)

                        <tr>

                            <td>{{ $loop->iteration }}</td>

                            <td>{{ $subject->name }}</td>

                            <td>{{ $subject->code }}</td>

                            <td>{{ $subject->education_level }}</td>

                            <td>
                                {{ $subject->staff->first_name ?? '' }}
                                {{ $subject->staff->last_name ?? '' }}
                            </td>

                            <td>

                                <a href="{{ route('subjects.show', $subject->id) }}"
                                   class="btn btn-sm btn-light">

                                    <i class="fas fa-eye"></i>
                                </a>

                                <a href="{{ route('subjects.edit', $subject->id) }}"
                                   class="btn btn-sm btn-light">

                                    <i class="fas fa-edit"></i>
                                </a>

                                <form action="{{ route('subjects.destroy', $subject->id) }}"
                                      method="POST"
                                      class="d-inline">

                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-sm btn-light"
                                            onclick="return confirm('Delete subject?')">

                                        <i class="fas fa-trash"></i>

                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="text-center">
                                No subjects found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

            <div class="mt-3">
                {{ $subjects->links() }}
            </div>

        </div>

    </div>

</div>

@endsection