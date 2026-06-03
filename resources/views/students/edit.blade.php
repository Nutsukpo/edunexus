@extends('layouts.master')

@section('title', 'Edit Student')

@section('content')
<div class="container-fluid">

    <h4 class="mb-3">Edit Student</h4>

    <div class="card">
        <div class="card-body">

            <form action="{{ route('students.update', $student->id) }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf
                @method('PUT')

                @include('students.form')

                <button class="btn btn-light text-dark">
                    Update Student
                </button>

                <a href="{{ route('students.index') }}" class="btn btn-secondary">
                    Back
                </a>

            </form>

        </div>
    </div>

</div>
@endsection