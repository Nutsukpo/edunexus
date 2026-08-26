@extends('layouts.master')

@section('title', 'Create Class')

@section('content')

<div class="container-fluid">

    <h5 class="mb-4 mt-3">Create Class</h5>

    <form method="POST" action="{{ route('student-classes.store') }}">
        @csrf

        @include('student_classes.form')

        <button class="btn btn-danger text-white">Save</button>
    </form>

</div>

@endsection