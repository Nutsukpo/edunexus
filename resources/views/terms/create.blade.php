@extends('layouts.master')

@section('title', 'Create Term')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white text-dark">
            <h4 class="mb-0">Create Term</h4>
        </div>

        <div class="card-body">

            <form action="{{ route('terms.store') }}"
                  method="POST">

                @csrf

                @include('terms.form')

            </form>

        </div>

    </div>

</div>

@endsection