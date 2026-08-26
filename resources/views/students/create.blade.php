@extends('layouts.master')

@section('title', 'Add Student')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">

        <div class="card-header mt-3">
            <h5>Add New Student</h5>
        </div>

        <div class="card-body">

            <form action="{{ route('students.store') }}"
                  method="POST">

                @csrf

                @include('students.form')

                <hr>

                <button class="btn btn-primary">
                    Save Student
                </button>

                <a href="{{ route('students.index') }}"
                   class="btn btn-secondary">
                    Cancel
                </a>

            </form>

        </div>

    </div>

</div>

@endsection