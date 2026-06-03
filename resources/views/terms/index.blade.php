@extends('layouts.master')

@section('title', 'Terms')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <h5 class="fw-bold bg-light text-dark">Terms Management</h5>

        <a href="{{ route('terms.create') }}"
           class="btn btn-white text-dark">
            <i class="fa fa-plus"></i> Add Term
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

                <thead class="table-white text-dark">
                    <tr>
                        <th>#</th>
                        <th>Academic Year</th>
                        <th>Term Name</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($terms as $term)

                    <tr>

                        <td>{{ $loop->iteration }}</td>

                        <td>
                            {{ $term->academicYear->name ?? 'N/A' }}
                        </td>

                        <td>{{ $term->name }}</td>

                        <td>
                            {{ $term->start_date ? $term->start_date->format('d M Y') : '-' }}
                        </td>

                        <td>
                            {{ $term->end_date ? $term->end_date->format('d M Y') : '-' }}
                        </td>

                        <td>

                            @if($term->is_active)

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

                            <a href="{{ route('terms.show', $term->id) }}"
                               class="btn btn-light btn-sm">
                                View
                            </a>

                            <a href="{{ route('terms.edit', $term->id) }}"
                               class="btn btn-light btn-sm">
                                Edit
                            </a>

                            <form action="{{ route('terms.destroy', $term->id) }}"
                                  method="POST"
                                  class="d-inline">

                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="btn btn-light btn-sm"
                                        onclick="return confirm('Delete this term?')">
                                    Delete
                                </button>

                            </form>

                        </td>

                    </tr>

                    @empty

                    <tr>
                        <td colspan="7" class="text-center">
                            No Terms Found
                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection