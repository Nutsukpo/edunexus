@extends('layouts.master')

@section('title', 'Create Class')

@section('content')

<div class="container-fluid">

    <h4>Create Class</h4>

    <form method="POST" action="{{ route('student-classes.store') }}">
        @csrf

        @include('student_classes.form')

        <button class="btn btn-light text-dark">Save</button>
    </form>

</div>

@endsection