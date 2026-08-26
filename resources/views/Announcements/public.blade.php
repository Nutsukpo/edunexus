{{-- resources/views/announcements/public.blade.php --}}
@extends('layouts.master')

@section('title', 'Announcements')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-bottom: 2px solid #90caf9;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0" style="color: #1565c0;">
                            <i class="fas fa-bullhorn mr-2"></i> Announcements
                            <span class="badge ml-2" style="background: #1565c0; color: white;">
                                {{ isset($recent) ? $recent->total() : 0 }}
                            </span>
                        </h5>
                        <div class="d-flex gap-2">
                            @auth
                                @if(auth()->user()->is_admin ?? false)
                                    <a href="{{ route('announcements.create') }}" class="btn btn-sm" style="background: #28a745; color: white;">
                                        <i class="fas fa-plus-circle mr-1"></i> Create Announcement
                                    </a>
                                @endif
                            @endauth
                            <a href="{{ route('announcements.public') }}" class="btn btn-sm" style="background: #e0e0e0; color: #333;">
                                <i class="fas fa-sync-alt mr-1"></i> Refresh
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body" style="background: #f8fbff;">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" style="border-left: 4px solid #28a745;">
                            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" style="border-left: 4px solid #dc3545;">
                            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    {{-- Search and Filter --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <form action="{{ route('announcements.public') }}" method="GET" class="d-flex">
                                <div class="input-group">
                                    <span class="input-group-text" style="background: #e3f2fd; border: 1px solid #90caf9;">
                                        <i class="fas fa-search" style="color: #1565c0;"></i>
                                    </span>
                                    <input type="text" name="search" class="form-control" style="border: 1px solid #90caf9;" placeholder="Search announcements..." value="{{ request('search') }}">
                                    <button type="submit" class="btn" style="background: #1565c0; color: white;">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(request('search') || request('type'))
                                        <a href="{{ route('announcements.public') }}" class="btn" style="background: #6c757d; color: white;">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end flex-wrap">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('announcements.public') }}" class="btn btn-sm" style="background: {{ empty(request('type')) ? '#1565c0' : '#e3f2fd' }}; color: {{ empty(request('type')) ? 'white' : '#1565c0' }}; border: 1px solid #90caf9;">
                                        <i class="fas fa-list mr-1"></i> All
                                    </a>
                                    @if(isset($types) && $types->isNotEmpty())
                                        @foreach($types as $typeOption)
                                            <a href="{{ route('announcements.public', ['type' => $typeOption]) }}" class="btn btn-sm" style="background: {{ request('type') == $typeOption ? '#1565c0' : '#e3f2fd' }}; color: {{ request('type') == $typeOption ? 'white' : '#1565c0' }}; border: 1px solid #90caf9;">
                                                {{ ucfirst($typeOption) }}
                                            </a>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Statistics Row --}}
                    @if(isset($recent) && $recent->isNotEmpty())
                        <div class="row mb-4">
                            <div class="col-md-3 col-6">
                                <div class="small-box" style="background: #e3f2fd; border-radius: 8px; padding: 10px 15px; border-left: 4px solid #1565c0;">
                                    <div class="inner">
                                        <h5 style="color: #0d47a1; margin: 0;">{{ $recent->total() }}</h5>
                                        <p style="color: #1565c0; margin: 0; font-size: 12px;">Total Announcements</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="small-box" style="background: #fff3e0; border-radius: 8px; padding: 10px 15px; border-left: 4px solid #e65100;">
                                    <div class="inner">
                                        <h5 style="color: #bf360c; margin: 0;">
                                            {{ isset($featured) ? $featured->count() : 0 }}
                                        </h5>
                                        <p style="color: #e65100; margin: 0; font-size: 12px;">Featured</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="small-box" style="background: #e8f5e9; border-radius: 8px; padding: 10px 15px; border-left: 4px solid #2e7d32;">
                                    <div class="inner">
                                        <h5 style="color: #1b5e20; margin: 0;">
                                            {{ isset($recent) ? $recent->where('type', 'urgent')->count() : 0 }}
                                        </h5>
                                        <p style="color: #2e7d32; margin: 0; font-size: 12px;">Urgent</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="small-box" style="background: #ffebee; border-radius: 8px; padding: 10px 15px; border-left: 4px solid #c62828;">
                                    <div class="inner">
                                        <h5 style="color: #b71c1c; margin: 0;">
                                            {{ isset($recent) ? $recent->where('expiry_date', '<', now())->count() : 0 }}
                                        </h5>
                                        <p style="color: #c62828; margin: 0; font-size: 12px;">Expired</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Featured Announcements --}}
                    @if(isset($featured) && $featured->isNotEmpty())
                        <div class="mb-4">
                            <h6 style="color: #1565c0;">
                                <i class="fas fa-star mr-1" style="color: #ffc107;"></i> Featured Announcements
                            </h6>
                            <div class="row">
                                @foreach($featured as $announcement)
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100" style="border: 1px solid #bbdefb; border-radius: 8px; transition: all 0.3s ease;">
                                            <div class="card-body" style="background: #f8fbff;">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <h6 style="color: #0d47a1; font-weight: 600;">
                                                        {{ $announcement->title }}
                                                    </h6>
                                                    <span class="badge" style="background: #ffc107; color: #333;">
                                                        <i class="fas fa-star mr-1"></i> Featured
                                                    </span>
                                                </div>
                                                <p style="color: #555; font-size: 14px; margin-top: 10px;">
                                                    {{ Str::limit($announcement->summary ?? $announcement->content, 100) }}
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center mt-2">
                                                    <small style="color: #6c757d;">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        {{ $announcement->created_at->diffForHumans() }}
                                                    </small>
                                                    <a href="{{ route('announcements.show', $announcement->id) }}" class="btn btn-sm" style="background: #1565c0; color: white;">
                                                        <i class="fas fa-eye mr-1"></i> View
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Announcements List --}}
                    @if(isset($recent) && $recent->isNotEmpty())
                        <div class="list-group">
                            @foreach($recent as $announcement)
                                <div class="list-group-item" style="border: 1px solid #bbdefb; border-radius: 8px; margin-bottom: 10px; background: #f8fbff; transition: all 0.3s ease;">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center flex-wrap">
                                                <h5 style="color: #0d47a1; margin-bottom: 5px;">
                                                    {{ $announcement->title }}
                                                </h5>
                                                @if($announcement->type)
                                                    <span class="badge ml-2" style="background: #e3f2fd; color: #1565c0; padding: 3px 10px;">
                                                        {{ ucfirst($announcement->type) }}
                                                    </span>
                                                @endif
                                                @if($announcement->is_featured)
                                                    <span class="badge ml-2" style="background: #ffc107; color: #333; padding: 3px 10px;">
                                                        <i class="fas fa-star mr-1"></i> Featured
                                                    </span>
                                                @endif
                                                @if($announcement->priority)
                                                    <span class="badge ml-2" style="background: 
                                                        @if($announcement->priority == 'urgent') #dc3545 
                                                        @elseif($announcement->priority == 'high') #fd7e14 
                                                        @elseif($announcement->priority == 'normal') #28a745 
                                                        @else #6c757d @endif; 
                                                        color: white; padding: 3px 10px;">
                                                        {{ ucfirst($announcement->priority) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <p style="color: #555; margin-top: 8px;">
                                                {{ Str::limit($announcement->summary ?? $announcement->content, 200) }}
                                            </p>
                                            <div class="d-flex flex-wrap align-items-center" style="gap: 15px;">
                                                <small style="color: #6c757d;">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    {{ $announcement->created_at->format('M d, Y') }}
                                                </small>
                                                @if($announcement->publish_date)
                                                    <small style="color: #6c757d;">
                                                        <i class="fas fa-calendar-day mr-1"></i>
                                                        Published: {{ $announcement->publish_date->format('M d, Y') }}
                                                    </small>
                                                @endif
                                                @if($announcement->expiry_date)
                                                    <small style="color: #6c757d;">
                                                        <i class="fas fa-calendar-times mr-1"></i>
                                                        Expires: {{ $announcement->expiry_date->format('M d, Y') }}
                                                    </small>
                                                @endif
                                                @if($announcement->views)
                                                    <small style="color: #6c757d;">
                                                        <i class="fas fa-eye mr-1"></i>
                                                        {{ $announcement->views }} views
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <a href="{{ route('announcements.show', $announcement->id) }}" class="btn btn-sm" style="background: #1565c0; color: white;">
                                                <i class="fas fa-eye mr-1"></i> View
                                            </a>
                                            @auth
                                                @if(auth()->user()->is_admin ?? false)
                                                    <a href="{{ route('announcements.edit', $announcement->id) }}" class="btn btn-sm" style="background: #ff9800; color: white;">
                                                        <i class="fas fa-edit mr-1"></i>
                                                    </a>
                                                @endif
                                            @endauth
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div style="color: #6c757d; font-size: 14px;">
                                Showing {{ $recent->firstItem() ?? 0 }} to {{ $recent->lastItem() ?? 0 }} 
                                of {{ $recent->total() }} announcements
                            </div>
                            <div>
                                {{ $recent->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-bullhorn" style="font-size: 48px; color: #bbdefb; margin-bottom: 15px;"></i>
                            <h5 style="color: #1565c0;">No Announcements Found</h5>
                            <p style="color: #6c757d;">There are no announcements available at this time.</p>
                            @auth
                                @if(auth()->user()->is_admin ?? false)
                                    <a href="{{ route('announcements.create') }}" class="btn" style="background: #28a745; color: white;">
                                        <i class="fas fa-plus-circle mr-1"></i> Create Announcement
                                    </a>
                                @endif
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    border: none;
    border-radius: 12px;
}
.card-header {
    border-radius: 12px 12px 0 0 !important;
}
.list-group-item {
    transition: all 0.3s ease;
}
.list-group-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(21, 101, 192, 0.1);
}
.btn:hover {
    opacity: 0.85;
    transform: translateY(-1px);
    transition: all 0.2s;
}
.badge {
    font-weight: 500;
}
.small-box {
    transition: all 0.3s ease;
}
.small-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
</style>
@endpush