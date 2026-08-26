@extends('layouts.master')

@section('title', 'Discussions')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-comments text-primary mr-2"></i> Discussions
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('discussions.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> New Group
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($groups->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No discussions yet</h5>
                            <p class="text-muted">Create a new group or join an existing one.</p>
                            <a href="{{ route('discussions.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Group
                            </a>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($groups as $group)
                                <a href="{{ route('discussions.show', $group->slug) }}" 
                                   class="list-group-item list-group-item-action d-flex align-items-center">
                                    <div class="mr-3">
                                        <img src="{{ $group->avatar_url }}" alt="{{ $group->name }}" 
                                             class="rounded-circle" width="50" height="50">
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">{{ $group->name }}</h6>
                                            <small class="text-muted">
                                                @if($group->latestMessage)
                                                    {{ $group->latestMessage->created_at->diffForHumans() }}
                                                @endif
                                            </small>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                @if($group->latestMessage)
                                                    {{ $group->latestMessage->sender->first_name ?? '' }}: 
                                                    {{ Str::limit(strip_tags($group->latestMessage->message), 30) }}
                                                @else
                                                    No messages yet
                                                @endif
                                            </small>
                                            @if($group->unread_count > 0)
                                                <span class="badge badge-primary badge-pill">{{ $group->unread_count }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection