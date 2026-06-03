@extends('layouts.master')

@section('title', 'Term Details')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white text-dark">
            <h4 class="mb-0">Term Details</h4>
        </div>

        <div class="card-body">

            <table class="table table-bordered">

                <tr>
                    <th width="250">Academic Year</th>
                    <td>{{ $term->academicYear->name ?? 'N/A' }}</td>
                </tr>

                <tr>
                    <th>Term Name</th>
                    <td>{{ $term->name }}</td>
                </tr>

                <tr>
                    <th>Start Date</th>
                    <td>
                        {{ $term->start_date ? $term->start_date->format('d M Y') : '-' }}
                    </td>
                </tr>

                <tr>
                    <th>End Date</th>
                    <td>
                        {{ $term->end_date ? $term->end_date->format('d M Y') : '-' }}
                    </td>
                </tr>

                <tr>
                    <th>Status</th>
                    <td>

                        @if($term->is_active)

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