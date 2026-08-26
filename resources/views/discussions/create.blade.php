@extends('layouts.master')

@section('title', 'Create Discussion Group')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-plus-circle text-primary mr-2"></i>Create New Discussion Group
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('discussions.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <form action="{{ route('discussions.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <h5><i class="fas fa-exclamation-circle"></i> Please fix the following errors:</h5>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="name">Group Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" placeholder="Enter group name" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      rows="3" placeholder="What is this group about?">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="type">Group Type <span class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                                <option value="public" {{ old('type') == 'public' ? 'selected' : '' }}>Public - Anyone can join</option>
                                <option value="private" {{ old('type') == 'private' ? 'selected' : '' }}>Private - Invitation only</option>
                                <option value="department" {{ old('type') == 'department' ? 'selected' : '' }}>Department - Specific department</option>
                            </select>
                            @error('type')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="participants">Add Participants</label>
                            <select name="participants[]" id="participants" 
                                    class="form-control @error('participants') is-invalid @enderror" multiple>
                                @foreach($staffMembers as $member)
                                    @if($member->id != $staff->id)
                                        <option value="{{ $member->id }}" 
                                                {{ (is_array(old('participants')) && in_array($member->id, old('participants'))) ? 'selected' : '' }}>
                                            {{ $member->first_name ?? '' }} {{ $member->last_name ?? '' }} ({{ $member->email ?? '' }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple participants</small>
                            @error('participants')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> You will be automatically added as an admin of this group.
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Group
                        </button>
                        <a href="{{ route('discussions.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#participants').select2({
            placeholder: 'Select participants...',
            allowClear: true,
            theme: 'bootstrap4'
        });
    });
</script>
@endpush

@push('styles')
<style>
    .select2-container--bootstrap4 .select2-selection--multiple {
        min-height: 45px;
    }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 4px 10px;
        border-radius: 4px;
    }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove {
        color: white;
        margin-right: 5px;
    }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #ffcccc;
    }
</style>
@endpush
@endsection