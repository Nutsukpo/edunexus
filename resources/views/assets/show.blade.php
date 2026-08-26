@extends('layouts.master')

@section('title', 'Asset Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <!-- Main Content -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-box text-primary"></i> Asset Details
                        </h3>
                        <div class="btn-group">
                            <a href="{{ route('assets.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="{{ route('assets.destroy', $asset->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" 
                                        onclick="return confirm('Are you sure you want to delete this asset?')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Asset Header -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                <div>
                                    <span class="text-muted">Asset Code:</span>
                                    <strong class="ml-2">{{ $asset->asset_code }}</strong>
                                </div>
                                <div>
                                    <span class="text-muted">Status:</span>
                                    <span class="ml-2 badge badge-{{ $asset->status_badge }}">
                                        {{ ucfirst($asset->status) }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-muted">Condition:</span>
                                    <span class="ml-2 badge badge-{{ $asset->condition_badge }}">
                                        {{ ucfirst($asset->condition) }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-muted">Warranty:</span>
                                    <span class="ml-2 badge badge-{{ $asset->is_under_warranty ? 'success' : 'secondary' }}">
                                        {{ $asset->is_under_warranty ? 'Active' : 'Expired' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Asset Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0"><i class="fas fa-info-circle"></i> Basic Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td><strong>Name</strong></td>
                                            <td>{{ $asset->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Category</strong></td>
                                            <td>{{ $asset->category->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Description</strong></td>
                                            <td>{{ $asset->description ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Serial Number</strong></td>
                                            <td>{{ $asset->serial_number ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Model</strong></td>
                                            <td>{{ $asset->model ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Brand</strong></td>
                                            <td>{{ $asset->brand ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Quantity</strong></td>
                                            <td>{{ $asset->quantity }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Location</strong></td>
                                            <td>{{ $asset->location ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0"><i class="fas fa-money-bill"></i> Financial Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td><strong>Purchase Price</strong></td>
                                            <td>${{ $asset->formatted_price }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Current Value</strong></td>
                                            <td>${{ number_format($asset->current_value ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Depreciation</strong></td>
                                            <td>{{ $asset->depreciation }}%</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Purchase Date</strong></td>
                                            <td>{{ $asset->purchase_date ? $asset->purchase_date->format('d/m/Y') : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Warranty Expiry</strong></td>
                                            <td>{{ $asset->warranty_expiry ? $asset->warranty_expiry->format('d/m/Y') : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Warranty Status</strong></td>
                                            <td>
                                                <span class="badge badge-{{ $asset->is_under_warranty ? 'success' : 'danger' }}">
                                                    {{ $asset->is_under_warranty ? 'Under Warranty' : 'Expired' }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assigned To -->
                    @if($asset->currentAssignment)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0"><i class="fas fa-user-check"></i> Currently Assigned To</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>User:</strong> {{ $asset->currentAssignment->assignee->name ?? 'N/A' }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Assigned Date:</strong> {{ $asset->currentAssignment->assigned_date->format('d/m/Y') }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Expected Return:</strong> 
                                            @if($asset->currentAssignment->expected_return_date)
                                                {{ $asset->currentAssignment->expected_return_date->format('d/m/Y') }}
                                                @if($asset->currentAssignment->is_overdue)
                                                    <span class="badge badge-danger ml-2">Overdue</span>
                                                @endif
                                            @else
                                                N/A
                                            @endif
                                        </div>
                                    </div>
                                    @if($asset->currentAssignment->assignment_notes)
                                        <div class="mt-2">
                                            <strong>Notes:</strong> {{ $asset->currentAssignment->assignment_notes }}
                                        </div>
                                    @endif
                                    <div class="mt-3">
                                        <form action="{{ route('assets.return', $asset->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-undo"></i> Return Asset
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Notes -->
                    @if($asset->notes)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0"><i class="fas fa-sticky-note"></i> Notes</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{{ $asset->notes }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Assignment History -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0"><i class="fas fa-history"></i> Assignment History</h6>
                                </div>
                                <div class="card-body">
                                    @if($assignments->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Assigned To</th>
                                                        <th>Assigned Date</th>
                                                        <th>Expected Return</th>
                                                        <th>Actual Return</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($assignments as $assignment)
                                                        <tr>
                                                            <td>{{ $assignment->assignee->name ?? 'N/A' }}</td>
                                                            <td>{{ $assignment->assigned_date->format('d/m/Y') }}</td>
                                                            <td>{{ $assignment->expected_return_date ? $assignment->expected_return_date->format('d/m/Y') : 'N/A' }}</td>
                                                            <td>{{ $assignment->actual_return_date ? $assignment->actual_return_date->format('d/m/Y') : 'Not Returned' }}</td>
                                                            <td>
                                                                <span class="badge badge-{{ $assignment->status == 'active' ? 'success' : ($assignment->status == 'overdue' ? 'danger' : 'secondary') }}">
                                                                    {{ ucfirst($assignment->status) }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        {{ $assignments->links() }}
                                    @else
                                        <p class="text-muted text-center mb-0">No assignment history available.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Maintenance History -->
                   
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Asset Image -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0"><i class="fas fa-image"></i> Asset Image</h6>
                </div>
                <div class="card-body text-center">
                    @if($asset->image_path && Storage::disk('public')->exists($asset->image_path))
                        <img src="{{ Storage::disk('public')->url($asset->image_path) }}" 
                             alt="{{ $asset->name }}" 
                             class="img-fluid rounded" style="max-height: 250px;">
                    @else
                        <div class="p-5 bg-light rounded">
                            <i class="fas fa-camera fa-4x text-muted"></i>
                            <p class="mt-2 text-muted">No image available</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0"><i class="fas fa-cog"></i> Quick Actions</h6>
                </div>
                <div class="card-body">
                    @if($asset->isAvailable())
                        <button type="button" class="btn btn-success btn-block mb-2" 
                                data-toggle="modal" data-target="#assignModal">
                            <i class="fas fa-user-plus"></i> Assign Asset
                        </button>
                    @endif
                    @if($asset->isAssigned())
                        <form action="{{ route('assets.return', $asset->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-block mb-2">
                                <i class="fas fa-undo"></i> Return Asset
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-warning btn-block mb-2">
                        <i class="fas fa-edit"></i> Edit Asset
                    </a>
                    @if($asset->document_path)
                        <a href="{{ route('assets.download.document', $asset->id) }}" class="btn btn-info btn-block mb-2" target="_blank">
                            <i class="fas fa-file-download"></i> Download Document
                        </a>
                    @endif
                    @if($asset->image_path)
                        <a href="{{ route('assets.download.image', $asset->id) }}" class="btn btn-secondary btn-block" target="_blank">
                            <i class="fas fa-image"></i> Download Image
                        </a>
                    @endif
                </div>
            </div>

            <!-- Metadata -->
            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0"><i class="fas fa-clock"></i> Metadata</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><strong>Created</strong></td>
                            <td>{{ $asset->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Created By</strong></td>
                            <td>{{ $asset->creator->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Last Updated</strong></td>
                            <td>{{ $asset->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Updated By</strong></td>
                            <td>{{ $asset->updater->name ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Asset: {{ $asset->name }}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('assets.assign', $asset->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Assign To <span class="text-danger">*</span></label>
                        <select name="assigned_to" class="form-control" required>
                            <option value="">Select User</option>
                            @foreach($users ?? [] as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Expected Return Date</label>
                        <input type="date" name="expected_return_date" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="assignment_notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Any additional JS here
});
</script>
@endpush
@endsection