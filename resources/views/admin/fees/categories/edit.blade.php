@extends('layouts.master')

@section('title', 'Edit Fee Category')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit mr-2"></i>Edit Fee Category
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('fee-categories.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('fee-categories.update', $category->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">Category Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="name" 
                                   id="name"
                                   class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $category->name) }}"
                                   required
                                   autofocus>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="code">Category Code</label>
                            <input type="text" 
                                   name="code" 
                                   id="code"
                                   class="form-control @error('code') is-invalid @enderror" 
                                   value="{{ old('code', $category->code) }}"
                                   maxlength="50">
                            @error('code')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Short code or abbreviation (must be unique).</small>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" 
                                      id="description"
                                      class="form-control @error('description') is-invalid @enderror" 
                                      rows="3">{{ old('description', $category->description) }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sort_order">Sort Order</label>
                                    <input type="number" 
                                           name="sort_order" 
                                           id="sort_order"
                                           class="form-control @error('sort_order') is-invalid @enderror" 
                                           value="{{ old('sort_order', $category->sort_order) }}"
                                           min="0">
                                    @error('sort_order')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">Lower numbers will appear first.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <div class="custom-control custom-switch mt-2">
                                        <input type="checkbox" 
                                               class="custom-control-input" 
                                               id="is_active" 
                                               name="is_active" 
                                               value="1"
                                               {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">
                                            <span class="badge badge-pill {{ old('is_active', $category->is_active) ? 'badge-success' : 'badge-danger' }}" 
                                                  id="statusLabel">
                                                {{ old('is_active', $category->is_active) ? 'Active' : 'Inactive' }}
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i>
                            @php
                                $itemCount = $category->feeItems()->count();
                            @endphp
                            This category is currently used in <strong>{{ $itemCount }}</strong> fee item(s).
                            @if($itemCount > 0)
                                <br><small>Deleting this category will not delete the fee items, but they will be disassociated.</small>
                            @endif
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Category
                            </button>
                            <a href="{{ route('fee-categories.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Update status label when toggle changes
    $('#is_active').on('change', function() {
        const label = $('#statusLabel');
        if ($(this).is(':checked')) {
            label.removeClass('badge-danger').addClass('badge-success').text('Active');
        } else {
            label.removeClass('badge-success').addClass('badge-danger').text('Inactive');
        }
    });
});
</script>
@endpush
@endsection