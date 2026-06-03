@extends('layouts.master')

@section('title','Promote Students')

@section('content')

<div class="container-fluid">

    <div class="card border-0 shadow-sm">

        <div class="card-header">
            <h5 class="mb-0">
                Promote Students
            </h5>
        </div>

        <div class="card-body">

            <form method="POST"
                  action="{{ route('progressions.store') }}">

                @csrf

                <div class="row">

                    <div class="col-md-4">

                        <label>
                            From Class
                        </label>

                        <select name="from_class_id"
                                class="form-select"
                                required>

                            <option value="">
                                Select Class
                            </option>

                            @foreach($classes as $class)

                                <option value="{{ $class->id }}">
                                    {{ $class->name }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-4">

                        <label>
                            To Class
                        </label>

                        <select name="to_class_id"
                                class="form-select"
                                required>

                            <option value="">
                                Select Class
                            </option>

                            @foreach($classes as $class)

                                <option value="{{ $class->id }}">
                                    {{ $class->name }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-4">

                        <label>
                            Academic Year
                        </label>

                        <select name="academic_year_id"
                                class="form-select"
                                required>

                            @foreach($academicYears as $year)

                                <option value="{{ $year->id }}">
                                    {{ $year->name }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                </div>

                <hr>

                <button class="btn btn-success">

                    <i class="fas fa-check"></i>

                    Promote Students

                </button>

            </form>

        </div>

    </div>

</div>

@endsection