@extends('students.layouts.app')

@section('title', $announcement->title . ' - EduNexus')

@section('content')

<style>
    .announcement-detail {
        max-width: 1000px;
        margin: 0 auto;
        padding: 30px 0 50px;
    }

    .detail-header {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 24px;
        padding: 30px;
        box-shadow: 0 8px 25px rgba(15,23,42,.06);
    }

    .detail-title {
        font-size: 2rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.25;
        margin: 18px 0 14px;
    }

    .detail-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 18px;
        color: #64748b;
        font-size: .85rem;
    }

    .detail-content {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 24px;
        padding: 32px;
        margin-top: 20px;
        box-shadow: 0 8px 25px rgba(15,23,42,.06);
        color: #334155;
        line-height: 1.8;
    }

    .detail-content img {
        max-width: 100%;
        height: auto;
        border-radius: 15px;
    }

    .detail-badge {
        display: inline-flex;
        padding: 7px 12px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
    }
</style>

<div class="container-fluid">

    <div class="announcement-detail">

        <div class="mb-3">

            <a href="{{ route('students.announcements.index') }}"
               class="btn btn-light border btn-sm">

                <i class="fas fa-arrow-left me-1"></i>

                Back to Announcements

            </a>

        </div>


        <div class="detail-header">

            <span class="detail-badge">

                <i class="fas fa-bullhorn me-1"></i>

                {{ ucfirst($announcement->type ?? 'Announcement') }}

            </span>


            <h1 class="detail-title">

                {{ $announcement->title }}

            </h1>


            <div class="detail-meta">

                <span>
                    <i class="far fa-calendar me-1"></i>

                    {{ $announcement->created_at
                        ? $announcement->created_at->format('d F Y')
                        : 'N/A' }}
                </span>


                @if($announcement->priority)

                    <span>
                        <i class="fas fa-flag me-1"></i>

                        {{ ucfirst($announcement->priority) }} priority
                    </span>

                @endif


                @if($announcement->is_featured)

                    <span class="text-warning">

                        <i class="fas fa-star me-1"></i>

                        Featured

                    </span>

                @endif

            </div>

        </div>


        <div class="detail-content">

            @if(!empty($announcement->description))

                <p class="fw-semibold mb-4">

                    {{ $announcement->description }}

                </p>

            @endif


            @if(!empty($announcement->content))

                {!! $announcement->content !!}

            @elseif(!empty($announcement->description))

                <p>
                    {{ $announcement->description }}
                </p>

            @else

                <p class="text-muted">
                    No additional information is available.
                </p>

            @endif

        </div>


        <div class="mt-3">

            <a href="{{ route('students.announcements.index') }}"
               class="btn btn-primary">

                <i class="fas fa-arrow-left me-1"></i>

                Back to All Announcements

            </a>

        </div>

    </div>

</div>

@endsection