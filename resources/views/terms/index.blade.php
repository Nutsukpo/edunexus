@extends('layouts.master')

@section('title', 'Terms')

@section('content')

<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center
                flex-wrap gap-3 mb-4">

        <div>

            <h4 class="fw-bold mb-1">
                <i class="fas fa-calendar-days text-primary me-2"></i>
                Terms Management
            </h4>

            <p class="text-muted mb-0">
                Manage academic terms for each academic year.
            </p>

        </div>

        <a href="{{ route('terms.create') }}"
           class="btn btn-primary">

            <i class="fas fa-plus me-1"></i>
            Add Term

        </a>

    </div>


    {{-- Success --}}
    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show">

            <i class="fas fa-check-circle me-2"></i>

            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif


    {{-- Error --}}
    @if(session('error'))

        <div class="alert alert-danger alert-dismissible fade show">

            <i class="fas fa-exclamation-circle me-2"></i>

            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif


    {{-- Validation --}}
    @if($errors->any())

        <div class="alert alert-danger">

            <strong>Please correct the following:</strong>

            <ul class="mb-0 mt-2">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- Terms --}}
    <div class="card border-0 shadow-sm rounded-4">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th class="ps-4">#</th>
                            <th>Academic Year</th>
                            <th>Term</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($terms as $term)

                            <tr>

                                <td class="ps-4 text-muted">
                                    {{ $loop->iteration }}
                                </td>


                                <td>

                                    <span class="fw-semibold">

                                        {{ $term->academicYear->name ?? 'N/A' }}

                                    </span>

                                </td>


                                <td>

                                    @php
                                        $termClass = match(
                                            strtolower(trim($term->name))
                                        ) {
                                            'first term' => 'bg-primary',
                                            'second term' => 'bg-info text-dark',
                                            'third term' => 'bg-warning text-dark',
                                            default => 'bg-secondary',
                                        };
                                    @endphp

                                    <span class="badge {{ $termClass }}">

                                        {{ $term->name }}

                                    </span>

                                </td>


                                <td>

                                    {{ $term->start_date
                                        ? $term->start_date->format('d M Y')
                                        : '—'
                                    }}

                                </td>


                                <td>

                                    {{ $term->end_date
                                        ? $term->end_date->format('d M Y')
                                        : '—'
                                    }}

                                </td>


                                <td>

                                    @if($term->is_active)

                                        <span class="badge bg-success">

                                            <i class="fas fa-check me-1"></i>
                                            Active

                                        </span>

                                    @else

                                        <span class="badge bg-secondary">

                                            Inactive

                                        </span>

                                    @endif

                                </td>


                                <td class="text-end pe-4">

                                    <a href="{{ route('terms.show', $term->id) }}"
                                       class="btn btn-sm btn-outline-primary">

                                        <i class="fas fa-eye"></i>

                                    </a>


                                    <a href="{{ route('terms.edit', $term->id) }}"
                                       class="btn btn-sm btn-outline-secondary">

                                        <i class="fas fa-edit"></i>

                                    </a>


                                    <form action="{{ route('terms.destroy', $term->id) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this term?');">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger">

                                            <i class="fas fa-trash"></i>

                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="7"
                                    class="text-center py-5">

                                    <i class="fas fa-calendar-xmark text-muted fs-1 mb-3"></i>

                                    <h6 class="fw-bold">
                                        No Terms Found
                                    </h6>

                                    <p class="text-muted mb-3">
                                        No academic terms have been configured yet.
                                    </p>

                                    <a href="{{ route('terms.create') }}"
                                       class="btn btn-primary">

                                        <i class="fas fa-plus me-1"></i>
                                        Create First Term

                                    </a>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection