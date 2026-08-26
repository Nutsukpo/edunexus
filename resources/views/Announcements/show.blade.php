@extends('layouts.master')

@section('title', $announcement->title)

@section('content')
<div class="container-fluid py-4">
    <div class="card border-0 shadow-lg">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="fw-bold mb-0">{{ $announcement->title }}</h4>
                <div>
                    <a href="{{ route('announcements.edit', $announcement) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit me-2"></i>
                        Edit
                    </a>
                    <a href="{{ route('announcements.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            <!-- Metadata -->
            <div class="d-flex flex-wrap gap-2 mb-4">
                <span class="badge bg-{{ $announcement->type_badge }}">{{ ucfirst($announcement->type) }}</span>
                <span class="badge bg-{{ $announcement->audience_badge }}">{{ ucfirst($announcement->audience) }}</span>
                <span class="badge bg-{{ $announcement->priority_color }}">{{ ucfirst($announcement->priority) }}</span>
                @if($announcement->is_published)
                    <span class="badge bg-success">Published</span>
                @else
                    <span class="badge bg-secondary">Draft</span>
                @endif
                @if($announcement->is_featured)
                    <span class="badge bg-warning text-dark">
                        <i class="fas fa-star me-1"></i>
                        Featured
                    </span>
                @endif
            </div>

            <!-- Image -->
            @if($announcement->image)
                <div class="mb-4">
                    <img src="{{ asset('storage/' . $announcement->image) }}" 
                         alt="{{ $announcement->title }}" 
                         class="img-fluid rounded" style="max-height: 400px;">
                </div>
            @endif

            <!-- Content -->
            <div class="content mb-4">
                {!! nl2br(e($announcement->content)) !!}
            </div>

            <!-- Links -->
            @if($announcement->link)
                <div class="mb-3">
                    <a href="{{ $announcement->link }}" target="_blank" class="btn btn-outline-primary">
                        <i class="fas fa-external-link-alt me-2"></i>
                        Learn More
                    </a>
                </div>
            @endif

            <!-- Footer -->
            <div class="border-top pt-3 text-muted">
                <small>
                    <i class="fas fa-user me-1"></i>
                    Created by: {{ $announcement->creator->name ?? 'Unknown' }}
                </small>
                <br>
                <small>
                    <i class="fas fa-calendar me-1"></i>
                    Posted: {{ $announcement->formatted_date }}
                </small>
                @if($announcement->publish_date)
                    <br>
                    <small>
                        <i class="fas fa-calendar-check me-1"></i>
                        Publish Date: {{ $announcement->publish_date->format('M d, Y') }}
                    </small>
                @endif
                @if($announcement->expiry_date)
                    <br>
                    <small>
                        <i class="fas fa-calendar-times me-1"></i>
                        Expiry Date: {{ $announcement->expiry_date->format('M d, Y') }}
                    </small>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection