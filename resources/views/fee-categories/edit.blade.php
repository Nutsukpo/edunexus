@extends('layouts.master')

@section('title', 'Edit Fee Category')

@section('content')

<div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Edit Fee Category</h3>
            <small class="text-muted">
                Update fee category information
            </small>
        </div>

        <a href="{{ route('fee-categories.index') }}"
           class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Back
        </a>
    </div>

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white">
            <h5 class="mb-0 fw-bold">
                Edit: {{ $feeCategory->name }}
            </h5>
        </div>

        <div class="card-body">

            <form action="{{ route('fee-categories.update', $feeCategory->id) }}"
                  method="POST">

                @csrf
                @method('PUT')

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">
                            Category Name <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $feeCategory->name) }}"
                               required>

                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">
                            Status
                        </label>

                        <div class="form-check form-switch mt-2">

                            <input class="form-check-input"
                                   type="checkbox"
                                   name="is_active"
                                   id="is_active"
                                   {{ $feeCategory->is_active ? 'checked' : '' }}>

                            <label class="form-check-label"
                                   for="is_active">

                                Active Category

                            </label>

                        </div>

                    </div>

                    <div class="col-md-12 mb-3">

                        <label class="form-label fw-semibold">
                            Description
                        </label>

                        <textarea name="description"
                                  rows="4"
                                  class="form-control">{{ old('description', $feeCategory->description) }}</textarea>

                    </div>

                </div>

                <hr>

                <div class="d-flex justify-content-end">

                    <a href="{{ route('fee-categories.index') }}"
                       class="btn btn-light me-2">

                        Cancel

                    </a>

                    <button type="submit"
                            class="btn btn-primary">

                        <i class="fas fa-save me-1"></i>

                        Update Category

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection