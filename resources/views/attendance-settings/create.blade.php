@extends('layouts.master')

@section('title', 'Create Attendance Settings')

@section('content')

<div class="container-fluid">

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white">

            <h4 class="mb-0 fw-bold">

                Create Attendance Settings

            </h4>

        </div>

        <form action="{{ route('attendance-settings.store') }}"
              method="POST">

            @csrf

            <div class="card-body">

                @include('attendance-settings.form')

            </div>

            <div class="card-footer bg-white">

                <button type="submit"
                        class="btn btn-primary">

                    <i class="fas fa-save me-1"></i>
                    Save Settings

                </button>

            </div>

        </form>

    </div>

</div>

@endsection