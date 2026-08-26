@extends('layouts.master')

@section('title', 'Fee Categories')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold mb-0">Fee Categories</h3>
            <small class="text-muted">
                Manage School Fee Categories
            </small>
        </div>

        <a href="{{ route('fee-categories.create') }}"
           class="btn btn-danger">

            <i class="fas fa-plus-circle me-1"></i>
            Add Category
        </a>
    </div>

    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <table id="feeTable"
                   class="table table-hover align-middle">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($categories as $category)

                    <tr>

                        <td>{{ $loop->iteration }}</td>

                        <td>
                            <strong>
                                {{ $category->name }}
                            </strong>
                        </td>

                        <td>
                            {{ $category->description }}
                        </td>

                        <td>
                            @if($category->is_active)

                                <span class="badge bg-success">
                                    Active
                                </span>

                            @else

                                <span class="badge bg-danger">
                                    Inactive
                                </span>

                            @endif
                        </td>

                        <td>

                            <a href="{{ route('fee-categories.edit',$category->id) }}"
                               class="btn btn-white text-dark btn-sm">

                                <i class="fas fa-edit"></i>
                            </a>

                            <form action="{{ route('fee-categories.destroy',$category->id) }}"
                                  method="POST"
                                  class="d-inline">

                                @csrf
                                @method('DELETE')

                                <button class="btn btn-white text-dark btn-sm"
                                        onclick="return confirm('Delete Category?')">

                                    <i class="fas fa-trash"></i>

                                </button>

                            </form>

                        </td>

                    </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection