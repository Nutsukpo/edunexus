@extends('students.layouts.app')

@section('title', 'Announcements - EduNexus')

@section('content')

<style>
    .announcement-page {
        padding: 24px 0 40px;
    }

    .announcement-hero {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        padding: 32px;
        margin-bottom: 24px;
        background:
            linear-gradient(135deg, #0f172a 0%, #1e3a8a 55%, #2563eb 100%);
        color: #fff;
        box-shadow: 0 14px 35px rgba(15, 23, 42, .16);
    }

    .announcement-hero::after {
        content: "";
        position: absolute;
        width: 280px;
        height: 280px;
        border-radius: 50%;
        right: -90px;
        top: -120px;
        background: rgba(255,255,255,.08);
    }

    .announcement-hero-content {
        position: relative;
        z-index: 2;
    }

    .announcement-icon {
        width: 56px;
        height: 56px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        background: rgba(255,255,255,.14);
        font-size: 22px;
        margin-bottom: 18px;
    }

    .announcement-hero h1 {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .announcement-hero p {
        margin: 0;
        color: rgba(255,255,255,.78);
    }

    .stat-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 20px;
        height: 100%;
        box-shadow: 0 5px 18px rgba(15,23,42,.05);
    }

    .stat-label {
        color: #64748b;
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .stat-value {
        font-size: 1.7rem;
        font-weight: 800;
        margin-top: 4px;
        color: #0f172a;
    }

    .filter-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 24px;
        box-shadow: 0 5px 18px rgba(15,23,42,.05);
    }

    .filter-label {
        font-size: .78rem;
        font-weight: 700;
        color: #475569;
        margin-bottom: 7px;
    }

    .filter-card .form-control,
    .filter-card .form-select {
        border-radius: 11px;
        min-height: 44px;
        border-color: #dbe2ea;
    }

    .announcement-card {
        height: 100%;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        overflow: hidden;
        transition: .2s ease;
        box-shadow: 0 5px 18px rgba(15,23,42,.05);
    }

    .announcement-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px rgba(15,23,42,.10);
    }

    .announcement-card-top {
        padding: 20px 20px 0;
    }

    .announcement-card-body {
        padding: 16px 20px 20px;
    }

    .announcement-title {
        color: #0f172a;
        font-weight: 800;
        font-size: 1.05rem;
        line-height: 1.35;
        margin: 12px 0 8px;
    }

    .announcement-description {
        color: #64748b;
        font-size: .88rem;
        line-height: 1.65;
    }

    .announcement-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #94a3b8;
        font-size: .76rem;
        margin-top: 16px;
    }

    .announcement-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: .68rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .badge-featured {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-urgent {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-high {
        background: #ffedd5;
        color: #9a3412;
    }

    .badge-normal {
        background: #e0f2fe;
        color: #075985;
    }

    .read-button {
        border-radius: 10px;
        font-size: .8rem;
        font-weight: 700;
    }

    .empty-state {
        text-align: center;
        padding: 70px 20px;
        background: #fff;
        border: 1px dashed #cbd5e1;
        border-radius: 20px;
    }

    .empty-state-icon {
        width: 70px;
        height: 70px;
        margin: 0 auto 18px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #eff6ff;
        color: #2563eb;
        font-size: 28px;
    }

    @media(max-width: 767px) {
        .announcement-hero {
            padding: 24px;
        }

        .announcement-hero h1 {
            font-size: 1.6rem;
        }
    }
</style>

<div class="container-fluid announcement-page">

    {{-- HERO --}}
    <div class="announcement-hero">
        <div class="announcement-hero-content">

            <div class="announcement-icon">
                <i class="fas fa-bullhorn"></i>
            </div>

            <h1>School Announcements</h1>

            <p>
                Stay informed about important news, updates and activities
                from your school.
            </p>

        </div>
    </div>


    {{-- SUMMARY --}}
    <div class="row g-3 mb-4">

        <div class="col-6 col-lg-4">
            <div class="stat-card">
                <div class="stat-label">Available</div>
                <div class="stat-value">{{ $total }}</div>
            </div>
        </div>

        <div class="col-6 col-lg-4">
            <div class="stat-card">
                <div class="stat-label">Featured</div>
                <div class="stat-value">{{ $featured }}</div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="stat-card">
                <div class="stat-label">Important</div>
                <div class="stat-value">{{ $urgent }}</div>
            </div>
        </div>

    </div>


    {{-- FILTERS --}}
    <div class="filter-card">

        <form method="GET"
              action="{{ route('students.announcements.index') }}">

            <div class="row g-3 align-items-end">

                <div class="col-lg-4">
                    <label class="filter-label">
                        Search
                    </label>

                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        class="form-control"
                        placeholder="Search announcements..."
                    >
                </div>


                <div class="col-md-6 col-lg-2">
                    <label class="filter-label">
                        Type
                    </label>

                    <select name="type" class="form-select">

                        <option value="">
                            All Types
                        </option>

                        @foreach($types as $type)

                            <option
                                value="{{ $type }}"
                                {{ request('type') === $type ? 'selected' : '' }}
                            >
                                {{ ucfirst($type) }}
                            </option>

                        @endforeach

                    </select>
                </div>


                <div class="col-md-6 col-lg-2">
                    <label class="filter-label">
                        Priority
                    </label>

                    <select name="priority" class="form-select">

                        <option value="">
                            All Priorities
                        </option>

                        <option value="urgent"
                            {{ request('priority') === 'urgent' ? 'selected' : '' }}>
                            Urgent
                        </option>

                        <option value="high"
                            {{ request('priority') === 'high' ? 'selected' : '' }}>
                            High
                        </option>

                        <option value="normal"
                            {{ request('priority') === 'normal' ? 'selected' : '' }}>
                            Normal
                        </option>

                    </select>
                </div>


                <div class="col-md-6 col-lg-2">

                    <label class="filter-label">
                        From
                    </label>

                    <input
                        type="date"
                        name="date_from"
                        value="{{ request('date_from') }}"
                        class="form-control"
                    >

                </div>


                <div class="col-md-6 col-lg-2">

                    <label class="filter-label">
                        To
                    </label>

                    <input
                        type="date"
                        name="date_to"
                        value="{{ request('date_to') }}"
                        class="form-control"
                    >

                </div>


                <div class="col-12 d-flex gap-2">

                    <button type="submit"
                            class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i>
                        Apply Filters
                    </button>

                    <a href="{{ route('students.announcements.index') }}"
                       class="btn btn-light border">
                        <i class="fas fa-rotate-left me-1"></i>
                        Reset
                    </a>

                </div>

            </div>

        </form>

    </div>


    {{-- RESULTS --}}
    @if($announcements->count())

        <div class="row g-4">

            @foreach($announcements as $announcement)

                @php
                    $priority = strtolower($announcement->priority ?? 'normal');
                @endphp

                <div class="col-md-6 col-xl-4">

                    <div class="announcement-card">

                        <div class="announcement-card-top">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    @if($announcement->is_featured)

                                        <span class="announcement-badge badge-featured">
                                            <i class="fas fa-star"></i>
                                            Featured
                                        </span>

                                    @endif

                                </div>

                                <span class="announcement-badge
                                    @if($priority === 'urgent')
                                        badge-urgent
                                    @elseif($priority === 'high')
                                        badge-high
                                    @else
                                        badge-normal
                                    @endif">

                                    {{ ucfirst($priority) }}

                                </span>

                            </div>


                            <div class="announcement-title">

                                {{ $announcement->title }}

                            </div>

                        </div>


                        <div class="announcement-card-body">

                            <div class="announcement-description">

                                {{ \Illuminate\Support\Str::limit(
                                    strip_tags(
                                        $announcement->description
                                        ?? $announcement->content
                                        ?? ''
                                    ),
                                    150
                                ) }}

                            </div>


                            <div class="announcement-meta">

                                <span>
                                    <i class="far fa-calendar me-1"></i>

                                    {{ $announcement->created_at
                                        ? $announcement->created_at->format('d M Y')
                                        : 'N/A' }}

                                </span>

                                @if($announcement->type)

                                    <span>
                                        <i class="far fa-folder me-1"></i>
                                        {{ ucfirst($announcement->type) }}
                                    </span>

                                @endif

                            </div>


                            <div class="mt-3">

                                <a
                                    href="{{ route(
                                        'students.announcements.show',
                                        $announcement->id
                                    ) }}"
                                    class="btn btn-primary btn-sm read-button w-100"
                                >
                                    Read Announcement
                                    <i class="fas fa-arrow-right ms-1"></i>
                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

    @else

        <div class="empty-state">

            <div class="empty-state-icon">
                <i class="fas fa-bullhorn"></i>
            </div>

            <h5 class="fw-bold">
                No announcements found
            </h5>

            <p class="text-muted mb-0">
                There are no announcements matching your current filters.
            </p>

        </div>

    @endif

</div>

@endsection