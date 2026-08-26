@extends('layouts.master')

@section('title', 'Academic Years')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <h5 class="fw-bold">Academic Years</h5>

        <a href="{{ route('academic-years.create') }}"
           class="btn btn-primary text-white">
           <i class="fas fa-plus-circle me-1"></i> Academic Year
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <table class="table table-bordered table-hover">

                <thead class="table-light text-dark">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($academicYears as $year)

                    <tr>
                        <td>{{ $loop->iteration }}</td>

                        <td>{{ $year->name }}</td>

                        <td>
                            {{ \Carbon\Carbon::parse($year->start_date)->format('d M Y') }}
                        </td>

                        <td>
                            {{ \Carbon\Carbon::parse($year->end_date)->format('d M Y') }}
                        </td>

                        <td>
                            @if($year->is_active)
                                <span class="badge bg-success">
                                    Active
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    Inactive
                                </span>
                            @endif
                        </td>

                        <td>

                            <a href="{{ route('academic-years.show', $year->id) }}"
                               class="btn btn-light btn-sm">
                                View
                            </a>

                            <a href="{{ route('academic-years.edit', $year->id) }}"
                               class="btn btn-light btn-sm">
                                Edit
                            </a>

                            <form action="{{ route('academic-years.destroy', $year->id) }}"
                                  method="POST"
                                  class="d-inline">

                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="btn btn-light btn-sm"
                                        onclick="return confirm('Delete this academic year?')">
                                    Delete
                                </button>

                            </form>

                        </td>
                    </tr>

                    @empty

                    <tr>
                        <td colspan="6" class="text-center">
                            No Academic Years Found
                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>
    </div>

</div>

@endsection