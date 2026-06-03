@extends('layouts.master')

@section('title', 'Create Enrollment')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">

        <div class="card-header">
            <h4>Create Enrollment</h4>
        </div>

        <div class="card-body">

            <form action="{{ route('enrollments.store') }}"
                  method="POST">

                @csrf

                @include('enrollments.form')

                <button type="submit" class="btn btn-light text-dark">
                    Save Enrollment
                </button>

                <a href="{{ route('enrollments.index') }}"
                   class="btn btn-secondary">
                    Back
                </a>

            </form>

        </div>

    </div>

</div>

@endsection