@extends('students.layouts.app')

@section('title', 'Class History')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="fw-bold text-primary">{{ $summary['total_classes'] }}</h3>
                        <small class="text-muted">Total Classes</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="fw-bold text-success">{{ $summary['current_class'] }}</h3>
                        <small class="text-muted">Current Class</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="fw-bold text-info">{{ $summary['completed_classes'] }}</h3>
                        <small class="text-muted">Completed Classes</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="fw-bold text-warning">{{ $summary['total_years'] }}</h3>
                        <small class="text-muted">Academic Years</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Class History Table -->
        <div class="card border-0 shadow-lg">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">
                    <i class="fas fa-history me-2 text-primary"></i>
                    Class History
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Class Name</th>
                                <th>Academic Year</th>
                                <th>Status</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Subjects</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($classHistory as $index => $history)
                            <tr>
                                <td>{{ $classHistory->firstItem() + $index }}</td>
                                <td>
                                    <strong>{{ $history['class_name'] }}</strong>
                                    <br>
                                    <small class="text-muted">Code: {{ $history['class_code'] }}</small>
                                </td>
                                <td>{{ $history['academic_year'] }}</td>
                                <td>
                                    @if($history['status'] == 'Current')
                                        <span class="badge bg-success">Current</span>
                                    @elseif($history['is_completed'])
                                        <span class="badge bg-secondary">Completed</span>
                                    @else
                                        <span class="badge bg-warning">In Progress</span>
                                    @endif
                                </td>
                                <td>{{ $history['start_date'] }}</td>
                                <td>{{ $history['end_date'] }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $history['subjects_count'] }}</span>
                                </td>
                                <td>
                                    @if(isset($classPerformance[$index]))
                                        <span class="badge bg-{{ $classPerformance[$index]['grade'] == 'A' ? 'success' : ($classPerformance[$index]['grade'] == 'B' ? 'info' : ($classPerformance[$index]['grade'] == 'C' ? 'warning' : 'danger')) }}">
                                            {{ $classPerformance[$index]['grade'] ?? 'N/A' }}
                                        </span>
                                        <br>
                                        <small>{{ number_format($classPerformance[$index]['average_score'] ?? 0, 1) }}%</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No class history found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $classHistory->links() }}
            </div>
        </div>
    </div>
</div>
@endsection