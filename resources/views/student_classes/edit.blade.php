@extends('layouts.master')

@section('title', 'Edit Class')

@section('content')

<div class="container-fluid mt-3">

    <h5 class="mb-4 mt-3">Edit Class</h5>

    <form method="POST" action="{{ route('student-classes.update', $studentClass->id) }}">
        @csrf
        @method('PUT')

        @include('student_classes.form')

        <button class="btn btn-danger">Update</button>
    </form>

</div>

@endsection