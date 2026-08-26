@extends('layouts.master')

@section('title', 'Add Staff')

@section('content')

<div class="container-fluid py-4">

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-light border shadow-sm text-secondary rounded-3">

            <div class="fw-semibold mb-2 text-dark">
                <i class="fas fa-exclamation-circle me-1 text-dark"></i>
                Please fix the following errors:
            </div>

            <ul class="mb-0 ps-3 text-secondary small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>
    @endif

    {{-- Card --}}
    <div class="card border shadow-sm rounded-4 overflow-hidden">

        {{-- Header --}}
        <div class="card-header bg-white border-bottom py-3">

            <h5 class="mb-0 fw-semibold text-dark">
                <i class="fas fa-id-card me-2 text-dark"></i>
                Staff Information
            </h5>
        </div>

        {{-- Body --}}
        <div class="card-body p-4">

            <form action="{{ route('staff.store') }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                {{-- FORM --}}
                @include('staff.form')

                {{-- ACTIONS --}}
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">

                    <a href="{{ route('staff.index') }}"
                       class="btn btn-outline-dark rounded-pill px-4">

                        Cancel
                    </a>

                    <button type="reset"
                            class="btn btn-light border text-secondary rounded-pill px-4">

                        Reset
                    </button>

                    <button type="submit"
                            class="btn btn-primary border text-primary rounded-pill px-4">

                        <i class="fas fa-save me-1"></i>
                        Save Staff

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection