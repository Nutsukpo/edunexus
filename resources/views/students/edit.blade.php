@extends('layouts.master')

@section('title', 'Edit Student')

@section('content')
<div class="container-fluid">

    <h5 class="mb-3 mt-3 bg-white">Edit Student</h5>

    <div class="card">
        <div class="card-body">

            <form action="{{ route('students.update', $student->id) }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf
                @method('PUT')

                @include('students.form')

                <button class="btn btn-danger text-white">
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