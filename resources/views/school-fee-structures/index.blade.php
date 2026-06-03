@extends('layouts.master')

@section('title', 'School Fee Structures')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold mb-0">School Fee Structures</h3>
            <small class="text-muted">Manage all fee structures by class, term, and academic year</small>
        </div>

        <a href="{{ route('school-fee-structures.create') }}"
           class="btn btn-primary">
            <i class="fas fa-plus-circle me-1"></i>
            Add Structure
        </a>
    </div>

    {{-- TABLE CARD --}}
    <div class="card border-0 shadow-sm">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Academic Year</th>
                            <th>Term</th>
                            <th>Class</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th width="120">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($schoolFeeStructures as $key => $structure)

                            <tr>

                                <td class="fw-semibold">
                                    {{ $key + 1 }}
                                </td>

                                <td>
                                    {{ $structure->academicYear->name ?? 'N/A' }}
                                </td>

                                <td>
                                    {{ $structure->term->name ?? 'N/A' }}
                                </td>

                                <td>
                                    {{ $structure->studentClass->name ?? 'All Classes' }}
                                </td>

                                <td>
                                    {{ $structure->feeCategory->name ?? 'N/A' }}
                                </td>

                                <td>
                                    <strong>
                                        {{ number_format($structure->amount, 2) }}
                                    </strong>
                                </td>

                                <td>
                                    @if($structure->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>

                                <td>

                                    <div class="btn-group btn-group-sm">

                                        <a href="{{ route('school-fee-structures.show', $structure->id) }}"
                                           class="btn btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <a href="{{ route('school-fee-structures.edit', $structure->id) }}"
                                           class="btn btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="{{ route('school-fee-structures.destroy', $structure->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Delete this fee structure?')">

                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No fee structures found
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