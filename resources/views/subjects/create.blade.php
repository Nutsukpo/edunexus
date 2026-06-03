@extends('layouts.master')

@section('title', 'Create Subject')

@section('content')

<div class="container-fluid">

    <div class="card">

        <div class="card-header">
            <h4>Create Subject</h4>
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

            <form action="{{ route('subjects.store') }}"
                  method="POST">

                @csrf

                @include('subjects.form')

                <button type="submit"
                        class="btn btn-light text-dark">

                    Save Subject

                </button>

            </form>

        </div>

    </div>

</div>

@endsection