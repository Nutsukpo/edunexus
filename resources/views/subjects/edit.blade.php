@extends('layouts.master')

@section('title', 'Edit Subject')

@section('content')

<div class="container-fluid">

    <div class="card">

        <div class="card-header">
            <h4>Edit Subject</h4>
        </div>

        <div class="card-body">

            {{-- ERRORS --}}
            @if($errors->any())

                <div class="alert alert-danger">

                    <ul class="mb-0">

                        @foreach($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif

            <form action="{{ route('subjects.update', $subject) }}"
                  method="POST">

                @csrf
                @method('PUT')

                @include('subjects.form')

                <button type="submit"
                        class="btn btn-primary">

                    Update Subject

                </button>

            </form>

        </div>

    </div>

</div>

@endsection