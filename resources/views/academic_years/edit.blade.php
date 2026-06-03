@extends('layouts.master')

@section('title', 'Edit Academic Year')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm border-0">

        <div class="card-header bg-light text-dark">
            <h4 class="mb-0">Edit Academic Year</h4>
        </div>

        <div class="card-body">

            <form action="{{ route('academic-years.update', $academicYear->id) }}"
                  method="POST">

                @csrf
                @method('PUT')

                @include('academic_years.form')

            </form>

        </div>

    </div>

</div>

@endsection