@extends('layouts.master')

@section('title', 'Edit Term')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white text-dark">
            <h4 class="mb-0">Edit Term</h4>
        </div>

        <div class="card-body">

            <form action="{{ route('terms.update', $term->id) }}"
                  method="POST">

                @csrf
                @method('PUT')

                @include('terms.form')

            </form>

        </div>

    </div>

</div>

@endsection