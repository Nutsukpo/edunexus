@extends('layouts.master')

@section('title', 'Create Term')

@section('content')

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h4 class="fw-bold mb-1">
                Create Term
            </h4>

            <p class="text-muted mb-0">
                Add an academic term to an academic year.
            </p>
        </div>

        <a href="{{ route('terms.index') }}"
           class="btn btn-outline-secondary">

            <i class="fas fa-arrow-left me-1"></i>
            Back to Terms

        </a>

    </div>

    <form action="{{ route('terms.store') }}"
          method="POST">

        @csrf

        @include('terms.form')

    </form>

</div>

@endsection