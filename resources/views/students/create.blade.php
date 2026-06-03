@extends('layouts.master')

@section('title', 'Add Student')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">

        <div class="card-header">
            <h4>Add New Student</h4>
        </div>

        <div class="card-body">

            <form action="{{ route('students.store') }}"
                  method="POST">

                @csrf

                @include('students.form')

                <hr>

                <button class="btn btn-light text-dark">
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