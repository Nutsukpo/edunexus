@extends('layouts.master')

@section('title', 'Academic Year Details')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm border-0">

        <div class="card-header bg-light text-dark">
            <h4 class="mb-0">Academic Year Details</h4>
        </div>

        <div class="card-body">

            <table class="table table-bordered">

                <tr>
                    <th width="250">Academic Year</th>
                    <td>{{ $academicYear->name }}</td>
                </tr>

                <tr>
                    <th>Start Date</th>
                    <td>
                        {{ \Carbon\Carbon::parse($academicYear->start_date)->format('d M Y') }}
                    </td>
                </tr>

                <tr>
                    <th>End Date</th>
                    <td>
                        {{ \Carbon\Carbon::parse($academicYear->end_date)->format('d M Y') }}
                    </td>
                </tr>

                <tr>
                    <th>Status</th>
                    <td>

                        @if($academicYear->is_active)

                            <span class="badge bg-success">
                                Active
                            </span>

                        @else

                            <span class="badge bg-secondary">
                                Inactive
                            </span>

                        @endif

                    </td>
                </tr>

            </table>

        </div>

    </div>

</div>

@endsection