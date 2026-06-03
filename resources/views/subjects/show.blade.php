@extends('layouts.master')

@section('title', 'Subject Details')

@section('content')

<div class="container-fluid">

    <div class="card">

        <div class="card-header d-flex justify-content-between">

            <h4>Subject Details</h4>

            <a href="{{ route('subjects.index') }}"
               class="btn btn-secondary">

                Back

            </a>

        </div>

        <div class="card-body">

            <table class="table table-bordered">

                <tr>
                    <th width="250">Subject Name</th>
                    <td>{{ $subject->name }}</td>
                </tr>

                <tr>
                    <th>Code</th>
                    <td>{{ $subject->code }}</td>
                </tr>

                <tr>
                    <th>Education Level</th>
                    <td>{{ $subject->education_level }}</td>
                </tr>

                <tr>
                    <th>Category</th>
                    <td>{{ $subject->category }}</td>
                </tr>

                <tr>
                    <th>Description</th>
                    <td>{{ $subject->description }}</td>
                </tr>

                <tr>
                    <th>Teacher Assigned</th>

                    <td>
                        {{ ($subject->staff->first_name ?? '') . ' ' . ($subject->staff->last_name ?? '') }}
                    </td>
                </tr>

                <tr>
                    <th>Status</th>

                    <td>
                        @if($subject->is_active)
                            <span class="badge bg-success">
                                Active
                            </span>
                        @else
                            <span class="badge bg-danger">
                                Inactive
                            </span>
                        @endif
                    </td>
                </tr>

                <tr>
                    <th>Created At</th>

                    <td>
                        {{ $subject->created_at->format('M d, Y') }}
                    </td>
                </tr>

            </table>

        </div>

    </div>

</div>

@endsection