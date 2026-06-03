@extends('layouts.master')

@section('title', 'Edit Class')

@section('content')

<div class="container-fluid">

    <h4>Edit Class</h4>

    <form method="POST" action="{{ route('student-classes.update', $studentClass->id) }}">
        @csrf
        @method('PUT')

        @include('student_classes.form')

        <button class="btn btn-light">Update</button>
    </form>

</div>

@endsection