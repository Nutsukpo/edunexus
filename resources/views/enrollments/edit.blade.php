@extends('layouts.master')

@section('title', 'Edit Enrollment')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">

        <div class="card-header">
            <h4>Edit Enrollment</h4>
        </div>

        <div class="card-body">

            <form action="{{ route('enrollments.update', $enrollment->id) }}"
                  method="POST">

                @csrf
                @method('PUT')

                @include('enrollments.form')

                <button type="submit" class="btn btn-primary">
                    Update Enrollment
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