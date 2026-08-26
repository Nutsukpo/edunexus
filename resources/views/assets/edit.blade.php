@extends('layouts.master')

@section('title', 'Edit Asset')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Asset: {{ $asset->asset_code }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('assets.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <form action="{{ route('assets.update', $asset->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Asset Code Info -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Asset Code:</strong> {{ $asset->asset_code }}
                                    <span class="ml-3">
                                        <i class="fas fa-calendar-alt"></i>
                                        <strong>Created:</strong> {{ $asset->created_at->format('d/m/Y H:i') }}
                                    </span>
                                    <span class="ml-3">
                                        <i class="fas fa-user"></i>
                                        <strong>Created By:</strong> {{ $asset->creator->name ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Basic Information -->
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name">Asset Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $asset->name) }}" required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="category_id">Category <span class="text-danger">*</span></label>
                                    <select name="category_id" id="category_id" 
                                            class="form-control @error('category_id') is-invalid @enderror" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', $asset->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea name="description" id="description" 
                                              class="form-control @error('description') is-invalid @enderror" 
                                              rows="2">{{ old('description', $asset->description) }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Asset Details -->
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="serial_number">Serial Number</label>
                                    <input type="text" name="serial_number" id="serial_number" 
                                           class="form-control @error('serial_number') is-invalid @enderror" 
                                           value="{{ old('serial_number', $asset->serial_number) }}">
                                    @error('serial_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="model">Model</label>
                                    <input type="text" name="model" id="model" 
                                           class="form-control @error('model') is-invalid @enderror" 
                                           value="{{ old('model', $asset->model) }}">
                                    @error('model')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="brand">Brand</label>
                                    <input type="text" name="brand" id="brand" 
                                           class="form-control @error('brand') is-invalid @enderror" 
                                           value="{{ old('brand', $asset->brand) }}">
                                    @error('brand')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="quantity">Quantity</label>
                                    <input type="number" name="quantity" id="quantity" 
                                           class="form-control @error('quantity') is-invalid @enderror" 
                                           value="{{ old('quantity', $asset->quantity) }}" min="1">
                                    @error('quantity')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Financial Information -->
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
                                               value="{{ old('purchase_price', $asset->purchase_price) }}" step="0.01" min="0">
                                    </div>
                                    @error('purchase_price')
                                        <span class="invalid-feedback">{{ $message }}</span>
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
                                               value="{{ old('current_value', $asset->current_value) }}" step="0.01" min="0">
                                    </div>
                                    @error('current_value')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="purchase_date">Purchase Date</label>
                                    <input type="date" name="purchase_date" id="purchase_date" 
                                           class="form-control @error('purchase_date') is-invalid @enderror" 
                                           value="{{ old('purchase_date', $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : '') }}">
                                    @error('purchase_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="warranty_expiry">Warranty Expiry</label>
                                    <input type="date" name="warranty_expiry" id="warranty_expiry" 
                                           class="form-control @error('warranty_expiry') is-invalid @enderror" 
                                           value="{{ old('warranty_expiry', $asset->warranty_expiry ? $asset->warranty_expiry->format('Y-m-d') : '') }}">
                                    @error('warranty_expiry')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Location & Status -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="location">Location</label>
                                    <input type="text" name="location" id="location" 
                                           class="form-control @error('location') is-invalid @enderror" 
                                           value="{{ old('location', $asset->location) }}">
                                    @error('location')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" 
                                            class="form-control @error('status') is-invalid @enderror" required>
                                        <option value="available" {{ old('status', $asset->status) == 'available' ? 'selected' : '' }}>Available</option>
                                        <option value="assigned" {{ old('status', $asset->status) == 'assigned' ? 'selected' : '' }}>Assigned</option>
                                        <option value="maintenance" {{ old('status', $asset->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                        <option value="damaged" {{ old('status', $asset->status) == 'damaged' ? 'selected' : '' }}>Damaged</option>
                                        <option value="disposed" {{ old('status', $asset->status) == 'disposed' ? 'selected' : '' }}>Disposed</option>
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="condition">Condition <span class="text-danger">*</span></label>
                                    <select name="condition" id="condition" 
                                            class="form-control @error('condition') is-invalid @enderror" required>
                                        <option value="new" {{ old('condition', $asset->condition) == 'new' ? 'selected' : '' }}>New</option>
                                        <option value="good" {{ old('condition', $asset->condition) == 'good' ? 'selected' : '' }}>Good</option>
                                        <option value="fair" {{ old('condition', $asset->condition) == 'fair' ? 'selected' : '' }}>Fair</option>
                                        <option value="poor" {{ old('condition', $asset->condition) == 'poor' ? 'selected' : '' }}>Poor</option>
                                        <option value="damaged" {{ old('condition', $asset->condition) == 'damaged' ? 'selected' : '' }}>Damaged</option>
                                    </select>
                                    @error('condition')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Current Files -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="alert alert-secondary">
                                    <strong><i class="fas fa-paperclip"></i> Current Files:</strong>
                                    @if($asset->image_path)
                                        <span class="ml-2">
                                            <i class="fas fa-image text-success"></i> 
                                            <a href="{{ route('assets.download.image', $asset->id) }}" target="_blank">
                                                View Image
                                            </a>
                                            <span class="ml-2">
                                                <input type="checkbox" name="remove_image" id="remove_image" value="1">
                                                <label for="remove_image" class="text-danger">Remove</label>
                                            </span>
                                        </span>
                                    @endif
                                    @if($asset->document_path)
                                        <span class="ml-3">
                                            <i class="fas fa-file text-primary"></i>
                                            <a href="{{ route('assets.download.document', $asset->id) }}" target="_blank">
                                                {{ $asset->document_name ?? 'Document' }}
                                            </a>
                                            <span class="ml-2">
                                                <input type="checkbox" name="remove_document" id="remove_document" value="1">
                                                <label for="remove_document" class="text-danger">Remove</label>
                                            </span>
                                        </span>
                                    @endif
                                    @if(!$asset->image_path && !$asset->document_path)
                                        <span class="text-muted">No files attached</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Files -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="image">Replace Image</label>
                                    <div class="custom-file">
                                        <input type="file" name="image" id="image" 
                                               class="custom-file-input @error('image') is-invalid @enderror">
                                        <label class="custom-file-label" for="image">Choose image</label>
                                    </div>
                                    <small class="form-text text-muted">JPEG, PNG, JPG, GIF (Max 5MB)</small>
                                    @error('image')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="document">Replace Document</label>
                                    <div class="custom-file">
                                        <input type="file" name="document" id="document" 
                                               class="custom-file-input @error('document') is-invalid @enderror">
                                        <label class="custom-file-label" for="document">Choose document</label>
                                    </div>
                                    <small class="form-text text-muted">PDF, DOC, DOCX (Max 10MB)</small>
                                    @error('document')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea name="notes" id="notes" 
                                              class="form-control @error('notes') is-invalid @enderror" 
                                              rows="2">{{ old('notes', $asset->notes) }}</textarea>
                                    @error('notes')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Asset
                        </button>
                        <a href="{{ route('assets.show', $asset->id) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="{{ route('assets.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // File input display
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
    });

    // Auto-calculate current value if purchase price is entered
    $('#purchase_price').on('change', function() {
        if ($('#current_value').val() === '') {
            $('#current_value').val($(this).val());
        }
    });

    // Remove file confirmation
    $('#remove_image, #remove_document').change(function() {
        if ($(this).is(':checked')) {
            if (!confirm('Are you sure you want to remove this file?')) {
                $(this).prop('checked', false);
            }
        }
    });
});
</script>
@endpush

@push('styles')
<style>
    .custom-file-label::after {
        content: "Browse";
    }
</style>
@endpush
@endsection