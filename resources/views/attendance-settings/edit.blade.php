@extends('layouts.master')

@section('title', 'Edit Attendance Settings')

@section('content')

<div class="container-fluid">

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white">

            <h4 class="mb-0 fw-bold">

                Edit Attendance Settings

            </h4>

        </div>

        <form action="{{ route('attendance-settings.update', $setting->id) }}"
              method="POST">

            @csrf
            @method('PUT')

            <div class="card-body">

                @include('attendance-settings.form')

            </div>

            <div class="card-footer bg-white">

                <button type="submit"
                        class="btn btn-primary">

                    <i class="fas fa-save me-1"></i>
                    Update Settings

                </button>

            </div>

        </form>

    </div>

</div>

@endsection