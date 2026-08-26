@extends('layouts.master')

@section('title', 'Add New Asset')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0 text-dark font-weight-bold">
                        <i class="fas fa-plus-circle text-primary mr-2"></i>Add New Asset
                    </h4>
                    <small class="text-muted">Register a new asset in the inventory system</small>
                </div>
                <div>
                    <a href="{{ route('assets.index') }}" class="btn btn-secondary shadow-sm">
                        <i class="fas fa-arrow-left mr-2"></i>Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Form Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 text-muted">
                <i class="fas fa-info-circle mr-2"></i>Asset Information
                <span class="badge badge-danger ml-2">Required fields marked with *</span>
            </h6>
        </div>

        <form action="{{ route('assets.store') }}" method="POST" enctype="multipart/form-data" id="assetForm">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h6><i class="fas fa-exclamation-circle mr-2"></i>Please fix the following errors:</h6>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <!-- Basic Information Section -->
                <div class="row">
                    <div class="col-12">
                        <h6 class="border-bottom pb-2 mb-3 text-primary">
                            <i class="fas fa-info-circle mr-2"></i>Basic Information
                        </h6>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="name">Asset Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                </div>
                                <input type="text" name="name" id="name" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name') }}" placeholder="Enter asset name" required>
                            </div>
                            @error('name')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="category_id">Category <span class="text-danger">*</span></label>
                            <select name="category_id" id="category_id" 
                                    class="form-control select2 @error('category_id') is-invalid @enderror" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Asset Details Section -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h6 class="border-bottom pb-2 mb-3 text-primary">
                            <i class="fas fa-cog mr-2"></i>Asset Details
                        </h6>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="asset_code">Asset Code</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                </div>
                                <input type="text" name="asset_code" id="asset_code" 
                                       class="form-control @error('asset_code') is-invalid @enderror" 
                                       value="{{ old('asset_code') }}" placeholder="Auto-generated">
                            </div>
                            <small class="form-text text-muted">Leave empty for auto-generation</small>
                            @error('asset_code')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="serial_number">Serial Number</label>
                            <input type="text" name="serial_number" id="serial_number" 
                                   class="form-control @error('serial_number') is-invalid @enderror" 
                                   value="{{ old('serial_number') }}" placeholder="Enter serial number">
                            @error('serial_number')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="model">Model</label>
                            <input type="text" name="model" id="model" 
                                   class="form-control @error('model') is-invalid @enderror" 
                                   value="{{ old('model') }}" placeholder="Enter model">
                            @error('model')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="brand">Brand / Manufacturer</label>
                            <input type="text" name="brand" id="brand" 
                                   class="form-control @error('brand') is-invalid @enderror" 
                                   value="{{ old('brand') }}" placeholder="Enter brand">
                            @error('brand')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Quantity & Status Section -->
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="quantity">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" id="quantity" 
                                   class="form-control @error('quantity') is-invalid @enderror" 
                                   value="{{ old('quantity', 1) }}" min="1" required>
                            @error('quantity')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" 
                                    class="form-control @error('status') is-invalid @enderror" required>
                                <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}>Available</option>
                                <option value="assigned" {{ old('status') == 'assigned' ? 'selected' : '' }}>Assigned</option>
                                <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="damaged" {{ old('status') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                                <option value="disposed" {{ old('status') == 'disposed' ? 'selected' : '' }}>Disposed</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="condition">Condition <span class="text-danger">*</span></label>
                            <select name="condition" id="condition" 
                                    class="form-control @error('condition') is-invalid @enderror" required>
                                <option value="new" {{ old('condition') == 'new' ? 'selected' : '' }}>New</option>
                                <option value="good" {{ old('condition') == 'good' ? 'selected' : '' }}>Good</option>
                                <option value="fair" {{ old('condition') == 'fair' ? 'selected' : '' }}>Fair</option>
                                <option value="poor" {{ old('condition') == 'poor' ? 'selected' : '' }}>Poor</option>
                                <option value="damaged" {{ old('condition') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                            </select>
                            @error('condition')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="location">Location <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                </div>
                                <input type="text" name="location" id="location" 
                                       class="form-control @error('location') is-invalid @enderror" 
                                       value="{{ old('location') }}" placeholder="e.g., Building A - Room 201" required>
                            </div>
                            @error('location')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- User Assignment Section -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h6 class="border-bottom pb-2 mb-3 text-primary">
                            <i class="fas fa-user-check mr-2"></i>User Assignment
                            <span class="badge badge-info ml-2">Optional</span>
                        </h6>
                        <p class="text-muted small mb-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            Assign this asset to a user immediately after creation
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="assign_to">Assign To</label>
                            <select name="assign_to" id="assign_to" 
                                    class="form-control select2 @error('assign_to') is-invalid @enderror">
                                <option value="">-- Select User (Optional) --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('assign_to') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} 
                                        @if(isset($user->email)) ({{ $user->email }}) @endif
                                        @if(isset($user->department)) - {{ $user->department }} @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('assign_to')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                If assigned, the asset status will be automatically set to "Assigned"
                            </small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group" id="expectedReturnGroup" style="display: {{ old('assign_to') ? 'block' : 'none' }}">
                            <label for="expected_return_date">Expected Return Date</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                </div>
                                <input type="date" name="expected_return_date" id="expected_return_date" 
                                       class="form-control @error('expected_return_date') is-invalid @enderror" 
                                       value="{{ old('expected_return_date') }}" min="{{ date('Y-m-d') }}">
                            </div>
                            @error('expected_return_date')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">When is this asset expected to be returned?</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group" id="assignmentNotesGroup" style="display: {{ old('assign_to') ? 'block' : 'none' }}">
                            <label for="assignment_notes">Assignment Notes</label>
                            <textarea name="assignment_notes" id="assignment_notes" 
                                      class="form-control @error('assignment_notes') is-invalid @enderror" 
                                      rows="1" placeholder="Add notes about this assignment">{{ old('assignment_notes') }}</textarea>
                            @error('assignment_notes')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Financial Information Section -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h6 class="border-bottom pb-2 mb-3 text-primary">
                            <i class="fas fa-money-bill-wave mr-2"></i>Financial Information
                        </h6>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="purchase_price">Purchase Price</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" name="purchase_price" id="purchase_price" 
                                       class="form-control @error('purchase_price') is-invalid @enderror" 
                                       value="{{ old('purchase_price') }}" step="0.01" min="0" placeholder="0.00">
                            </div>
                            @error('purchase_price')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="current_value">Current Value</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" name="current_value" id="current_value" 
                                       class="form-control @error('current_value') is-invalid @enderror" 
                                       value="{{ old('current_value') }}" step="0.01" min="0" placeholder="0.00">
                            </div>
                            @error('current_value')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="purchase_date">Purchase Date</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                </div>
                                <input type="date" name="purchase_date" id="purchase_date" 
                                       class="form-control @error('purchase_date') is-invalid @enderror" 
                                       value="{{ old('purchase_date') }}">
                            </div>
                            @error('purchase_date')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="warranty_expiry">Warranty Expiry</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                </div>
                                <input type="date" name="warranty_expiry" id="warranty_expiry" 
                                       class="form-control @error('warranty_expiry') is-invalid @enderror" 
                                       value="{{ old('warranty_expiry') }}">
                            </div>
                            @error('warranty_expiry')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Supplier Information -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="supplier">Supplier / Vendor</label>
                            <input type="text" name="supplier" id="supplier" 
                                   class="form-control @error('supplier') is-invalid @enderror" 
                                   value="{{ old('supplier') }}" placeholder="Enter supplier name">
                            @error('supplier')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="invoice_number">Invoice Number</label>
                            <input type="text" name="invoice_number" id="invoice_number" 
                                   class="form-control @error('invoice_number') is-invalid @enderror" 
                                   value="{{ old('invoice_number') }}" placeholder="Enter invoice number">
                            @error('invoice_number')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Files Section -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h6 class="border-bottom pb-2 mb-3 text-primary">
                            <i class="fas fa-paperclip mr-2"></i>Files & Attachments
                        </h6>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="image">Asset Image</label>
                            <div class="custom-file">
                                <input type="file" name="image" id="image" 
                                       class="custom-file-input @error('image') is-invalid @enderror" 
                                       accept="image/jpeg,image/png,image/jpg,image/gif">
                                <label class="custom-file-label" for="image">
                                    <i class="fas fa-cloud-upload-alt mr-1"></i>Choose image
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                JPEG, PNG, JPG, GIF (Max 5MB)
                            </small>
                            @error('image')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="document">Supporting Document</label>
                            <div class="custom-file">
                                <input type="file" name="document" id="document" 
                                       class="custom-file-input @error('document') is-invalid @enderror" 
                                       accept=".pdf,.doc,.docx">
                                <label class="custom-file-label" for="document">
                                    <i class="fas fa-cloud-upload-alt mr-1"></i>Choose document
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                PDF, DOC, DOCX (Max 10MB)
                            </small>
                            @error('document')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h6 class="border-bottom pb-2 mb-3 text-primary">
                            <i class="fas fa-sticky-note mr-2"></i>Additional Notes
                        </h6>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea name="notes" id="notes" 
                                      class="form-control @error('notes') is-invalid @enderror" 
                                      rows="2" placeholder="Any additional information about this asset">{{ old('notes') }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Footer -->
            <div class="card-footer bg-white">
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm" id="submitBtn">
                            <i class="fas fa-save mr-2"></i>Save Asset
                        </button>
                        <button type="reset" class="btn btn-outline-secondary btn-lg ml-2">
                            <i class="fas fa-undo mr-2"></i>Reset
                        </button>
                        <a href="{{ route('assets.index') }}" class="btn btn-outline-danger btn-lg ml-2">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    /* Card and Form Styling */
    .card {
        border-radius: 12px;
        border: none;
    }
    .card-header {
        border-bottom: 1px solid #e9ecef;
        border-radius: 12px 12px 0 0 !important;
    }
    .card-footer {
        border-top: 1px solid #e9ecef;
        border-radius: 0 0 12px 12px !important;
    }

    /* Form Labels */
    .form-group label {
        font-weight: 600;
        font-size: 13px;
        color: #495057;
        margin-bottom: 5px;
    }
    .form-group label .text-danger {
        font-weight: 700;
    }

    /* Input Groups */
    .input-group-text {
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        color: #6c757d;
    }
    .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    /* Custom File Input */
    .custom-file-label {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .custom-file-label::after {
        content: "Browse";
        background-color: #007bff;
        color: white;
        border-left: 1px solid #007bff;
    }
    .custom-file-input:focus ~ .custom-file-label {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    /* Section Headers */
    .border-bottom {
        border-bottom: 2px solid #e9ecef !important;
    }
    .text-primary {
        color: #007bff !important;
    }

    /* Alert Styling */
    .alert {
        border-radius: 8px;
        border-left: 4px solid #dc3545;
    }

    /* Form Actions */
    .btn-lg {
        padding: 0.5rem 1.5rem;
        font-size: 1rem;
        border-radius: 8px;
    }
    .btn-primary {
        background: linear-gradient(135deg, #0062cc, #007bff);
        border: none;
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.4);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .btn-lg {
            padding: 0.4rem 1rem;
            font-size: 0.9rem;
        }
        .d-flex {
            flex-direction: column;
            align-items: flex-start !important;
        }
        .d-flex .btn {
            margin-top: 10px;
        }
    }

    /* Select2 Customization */
    .select2-container--bootstrap4 .select2-selection {
        min-height: 38px;
        border-radius: 4px;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-left: 12px;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
        height: 38px;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 for better dropdowns
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: 'Select an option',
        allowClear: true
    });

    // File input display with icons
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        var label = $(this).siblings('.custom-file-label');
        if (fileName) {
            label.addClass('selected').html('<i class="fas fa-file mr-1"></i>' + fileName);
        } else {
            label.removeClass('selected').html('<i class="fas fa-cloud-upload-alt mr-1"></i>Choose file');
        }
    });

    // Auto-calculate current value if purchase price is entered
    $('#purchase_price').on('change keyup', function() {
        var purchasePrice = $(this).val();
        if ($('#current_value').val() === '' && purchasePrice !== '') {
            $('#current_value').val(purchasePrice);
        }
    });

    // Show/hide assignment fields based on selection
    $('#assign_to').on('change', function() {
        var selected = $(this).val();
        if (selected) {
            $('#expectedReturnGroup').slideDown(300);
            $('#assignmentNotesGroup').slideDown(300);
            // Auto-set status to assigned if not already changed
            if ($('#status').val() !== 'assigned') {
                $('#status').val('assigned').trigger('change');
            }
        } else {
            $('#expectedReturnGroup').slideUp(300);
            $('#assignmentNotesGroup').slideUp(300);
            // Reset status if it was auto-set
            if ($('#status').val() === 'assigned') {
                $('#status').val('available').trigger('change');
            }
        }
    });

    // Form validation before submit
    $('#assetForm').on('submit', function(e) {
        var assignTo = $('#assign_to').val();
        var status = $('#status').val();
        
        // If assigned to user but status is not 'assigned', show warning
        if (assignTo && status !== 'assigned') {
            if (!confirm('You are assigning this asset but the status is not set to "Assigned". Click OK to continue or Cancel to change status.')) {
                e.preventDefault();
                $('#status').focus();
            }
        }
        
        // If status is 'assigned' but no user selected, show warning
        if (status === 'assigned' && !assignTo) {
            if (!confirm('Status is set to "Assigned" but no user is selected. Click OK to continue or Cancel to assign a user.')) {
                e.preventDefault();
                $('#assign_to').focus();
            }
        }
    });

    // Disable submit button after click to prevent double submission
    $('#submitBtn').on('click', function() {
        $(this).html('<i class="fas fa-spinner fa-spin mr-2"></i>Saving...');
        $(this).prop('disabled', true);
        $('#assetForm').submit();
    });

    // Reset form with confirmation
    $('button[type="reset"]').on('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to reset the form? All entered data will be cleared.')) {
            $('#assetForm')[0].reset();
            $('.select2').val(null).trigger('change');
            $('.custom-file-label').html('<i class="fas fa-cloud-upload-alt mr-1"></i>Choose file');
            $('.custom-file-label').removeClass('selected');
            $('#expectedReturnGroup').hide();
            $('#assignmentNotesGroup').hide();
            $(this).blur();
        }
    });
});
</script>
@endpush
@endsection