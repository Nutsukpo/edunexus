@extends('students.layouts.app')

@section('title', 'Announcements - EduNexus')

@section('content')

@php
    $filtersActive = collect(['search', 'type', 'priority', 'date_from', 'date_to'])
        ->contains(fn ($key) => filled(request($key)));
    $isPaginated = $announcements instanceof \Illuminate\Contracts\Pagination\Paginator;
@endphp

<style>
    .an {
        --navy: #0b2447; --navy-2: #123a73; --blue: #1d6fd6; --sky: #4da3ff;
        --green: #16a36a; --amber: #e29a0b; --red: #d64545; --violet: #6d5bd0;
        --ink: #14213d; --muted: #667388; --faint: #98a3b5; --line: #e7ebf1; --bg: #f4f6fa;
        --radius: 16px; --shadow: 0 1px 2px rgba(16,36,72,.04), 0 6px 20px rgba(16,36,72,.05);
        background: var(--bg); color: var(--ink); min-height: 100vh;
        padding: 28px clamp(16px, 3vw, 36px) 56px;
        font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
    }
    .an *, .an *::before, .an *::after { box-sizing: border-box; }
    .an h1, .an h2, .an h3, .an p { margin: 0; }

    /* ---------- Hero ---------- */
    .an-hero {
        position: relative; overflow: hidden; color: #fff; border-radius: 20px; padding: 30px 34px; margin-bottom: 24px;
        background: linear-gradient(120deg, var(--navy) 0%, var(--navy-2) 60%, #1b5aa8 100%);
        box-shadow: 0 12px 32px rgba(11,36,71,.22);
    }
    .an-hero::after {
        content: ""; position: absolute; right: -80px; top: -120px; width: 320px; height: 320px; border-radius: 50%;
        background: radial-gradient(circle, rgba(77,163,255,.35), transparent 70%); pointer-events: none;
    }
    .an-crumbs { position: relative; z-index: 1; display: flex; align-items: center; gap: 8px; font-size: 12px; color: rgba(255,255,255,.65); margin-bottom: 16px; }
    .an-crumbs i { font-size: 9px; }
    .an-hero-row { position: relative; z-index: 1; display: flex; align-items: center; gap: 20px; }
    .an-hero-icon { width: 56px; height: 56px; flex-shrink: 0; border-radius: 16px; display: grid; place-items: center; font-size: 22px; background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.22); }
    .an-hero h1 { font-size: clamp(22px, 2.6vw, 30px); font-weight: 700; letter-spacing: -.01em; margin-bottom: 6px; }
    .an-hero p { font-size: 14px; color: rgba(255,255,255,.75); max-width: 560px; }

    /* ---------- KPIs ---------- */
    .an-kpis { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-bottom: 22px; }
    .an-kpi {
        --tone: var(--blue); --tint: #e8f1fd;
        display: flex; align-items: center; gap: 16px; padding: 20px 22px;
        background: #fff; border: 1px solid var(--line); border-radius: var(--radius); box-shadow: var(--shadow);
    }
    .an-kpi.is-amber { --tone: var(--amber); --tint: #fdf1d9; }
    .an-kpi.is-red   { --tone: var(--red);   --tint: #fdecec; }
    .an-kpi-icon { width: 46px; height: 46px; flex-shrink: 0; border-radius: 13px; display: grid; place-items: center; font-size: 18px; background: var(--tint); color: var(--tone); }
    .an-kpi-label { display: block; font-size: 11.5px; font-weight: 600; letter-spacing: .07em; text-transform: uppercase; color: var(--muted); }
    .an-kpi-value { display: block; font-size: 28px; font-weight: 700; line-height: 1.15; letter-spacing: -.02em; }

    /* ---------- Filters ---------- */
    .an-filters { background: #fff; border: 1px solid var(--line); border-radius: var(--radius); box-shadow: var(--shadow); padding: 22px 24px; margin-bottom: 24px; }
    .an-filters-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
    .an-filters-head h2 { display: flex; align-items: center; gap: 10px; font-size: 15px; font-weight: 700; }
    .an-filters-head h2 i { color: var(--blue); }
    .an-active { padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; color: var(--blue); background: #eef4fd; }
    .an-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr; gap: 14px; }
    .an-label { display: block; margin-bottom: 7px; font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); }
    .an-input, .an-select {
        width: 100%; height: 44px; padding: 0 14px; border-radius: 12px; border: 1px solid #d3dcea; background-color: #fff;
        font-size: 14px; color: var(--ink); transition: border-color .15s ease, box-shadow .15s ease;
    }
    .an-input:focus, .an-select:focus { outline: 0; border-color: var(--blue); box-shadow: 0 0 0 4px rgba(29,111,214,.12); }
    .an-search { position: relative; }
    .an-search i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 13px; color: var(--faint); pointer-events: none; }
    .an-search .an-input { padding-left: 38px; }
    .an-filter-actions { display: flex; gap: 10px; margin-top: 18px; }
    .an-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 18px; border-radius: 12px;
        font-size: 13.5px; font-weight: 600; text-decoration: none; border: 1px solid transparent; cursor: pointer;
        transition: background .15s ease, box-shadow .15s ease;
    }
    .an-btn.primary { color: #fff; background: linear-gradient(135deg, #2a7fe8, #1d6fd6); box-shadow: 0 6px 16px rgba(29,111,214,.28); }
    .an-btn.primary:hover { color: #fff; box-shadow: 0 8px 20px rgba(29,111,214,.38); }
    .an-btn.ghost { color: var(--ink); background: #fff; border-color: #d3dcea; }
    .an-btn.ghost:hover { background: #f1f5fb; }

    /* ---------- Cards ---------- */
    .an-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 22px; }
    .an-card {
        --tone: var(--blue); --tint: #e8f1fd;
        position: relative; display: flex; flex-direction: column; background: #fff; border: 1px solid var(--line);
        border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; transition: transform .18s ease, box-shadow .18s ease;
    }
    .an-card::before { content: ""; position: absolute; left: 0; right: 0; top: 0; height: 4px; background: var(--tone); }
    .an-card:hover { transform: translateY(-3px); box-shadow: 0 14px 32px rgba(16,36,72,.10); }
    .an-card.urgent { --tone: var(--red);   --tint: #fdecec; }
    .an-card.high   { --tone: var(--amber); --tint: #fdf1d9; }
    .an-card-body { flex: 1; display: flex; flex-direction: column; padding: 24px 22px 22px; }
    .an-badges { display: flex; align-items: center; justify-content: space-between; gap: 8px; min-height: 26px; }
    .an-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; font-size: 10.5px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
    .an-badge.featured { color: #8a5a00; background: #fdf1d9; }
    .an-badge.priority { color: var(--tone); background: var(--tint); margin-left: auto; }
    .an-title { margin: 14px 0 8px; font-size: 17px; font-weight: 700; line-height: 1.35; letter-spacing: -.01em; }
    .an-text { flex: 1; font-size: 13.5px; line-height: 1.65; color: var(--muted); }
    .an-meta { display: flex; flex-wrap: wrap; gap: 6px 16px; margin: 18px 0 16px; padding-top: 16px; border-top: 1px solid var(--line); font-size: 12px; color: var(--muted); }
    .an-meta i { margin-right: 6px; color: var(--faint); }
    .an-read {
        display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 14px; border-radius: 12px;
        font-size: 13.5px; font-weight: 600; color: var(--blue); text-decoration: none; background: #eef4fd; transition: background .15s ease, color .15s ease;
    }
    .an-read:hover { color: #fff; background: var(--blue); }
    .an-read i { font-size: 11px; }

    .an-pager { display: flex; justify-content: center; margin-top: 28px; }

    /* ---------- Empty ---------- */
    .an-empty { padding: 72px 24px; text-align: center; background: #fff; border: 1px dashed #c9d3e2; border-radius: var(--radius); }
    .an-empty-icon { width: 72px; height: 72px; margin: 0 auto 16px; border-radius: 20px; display: grid; place-items: center; font-size: 28px; background: #e8f1fd; color: var(--blue); }
    .an-empty h3 { font-size: 17px; font-weight: 700; margin-bottom: 6px; }
    .an-empty p { font-size: 13.5px; color: var(--muted); }
    .an-empty .an-btn { margin-top: 20px; }

    /* ---------- Responsive ---------- */
    @media (max-width: 1280px) { .an-cards { grid-template-columns: repeat(2, 1fr); } .an-grid { grid-template-columns: repeat(4, 1fr); } .an-search-col { grid-column: 1 / -1; } }
    @media (max-width: 767px) {
        .an { padding: 18px 14px 90px; }
        .an-hero { padding: 22px 20px; border-radius: 16px; }
        .an-hero-icon { display: none; }
        .an-kpis { grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .an-kpi { flex-direction: column; align-items: flex-start; gap: 10px; padding: 14px; }
        .an-kpi-value { font-size: 22px; }
        .an-kpi-label { font-size: 10px; }
        .an-cards { grid-template-columns: 1fr; }
        .an-grid { grid-template-columns: 1fr 1fr; }
        .an-filters { padding: 18px; }
        .an-filter-actions .an-btn { flex: 1; }
    }
    @media (prefers-reduced-motion: reduce) { .an-card { transition: none; } }
</style>

<div class="an">

    {{-- ================= HERO ================= --}}
    <header class="an-hero">
        <div class="an-crumbs">
            <span>Student Portal</span><i class="fas fa-chevron-right"></i><span>Announcements</span>
        </div>
        <div class="an-hero-row">
            <span class="an-hero-icon"><i class="fas fa-bullhorn"></i></span>
            <div>
                <h1>School announcements</h1>
                <p>Stay informed about important news, updates and activities from your school.</p>
            </div>
        </div>
    </header>

    {{-- ================= KPIs ================= --}}
    <section class="an-kpis">
        <div class="an-kpi">
            <span class="an-kpi-icon"><i class="fas fa-bell"></i></span>
            <div><span class="an-kpi-label">Available</span><span class="an-kpi-value">{{ $total }}</span></div>
        </div>
        <div class="an-kpi is-amber">
            <span class="an-kpi-icon"><i class="fas fa-star"></i></span>
            <div><span class="an-kpi-label">Featured</span><span class="an-kpi-value">{{ $featured }}</span></div>
        </div>
        <div class="an-kpi is-red">
            <span class="an-kpi-icon"><i class="fas fa-triangle-exclamation"></i></span>
            <div><span class="an-kpi-label">Important</span><span class="an-kpi-value">{{ $urgent }}</span></div>
        </div>
    </section>

    {{-- ================= FILTERS ================= --}}
    <section class="an-filters">
        <form method="GET" action="{{ route('students.announcements.index') }}">
            <div class="an-filters-head">
                <h2><i class="fas fa-sliders"></i> Filter announcements</h2>
                @if($filtersActive)<span class="an-active">Filters applied</span>@endif
            </div>

            <div class="an-grid">
                <div class="an-search-col">
                    <label class="an-label" for="an-search">Search</label>
                    <div class="an-search">
                        <i class="fas fa-magnifying-glass"></i>
                        <input id="an-search" type="text" name="search" value="{{ request('search') }}" class="an-input" placeholder="Search announcements…">
                    </div>
                </div>

                <div>
                    <label class="an-label" for="an-type">Type</label>
                    <select id="an-type" name="type" class="an-select">
                        <option value="">All types</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="an-label" for="an-priority">Priority</label>
                    <select id="an-priority" name="priority" class="an-select">
                        <option value="">All priorities</option>
                        <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                    </select>
                </div>

                <div>
                    <label class="an-label" for="an-from">From</label>
                    <input id="an-from" type="date" name="date_from" value="{{ request('date_from') }}" class="an-input">
                </div>

                <div>
                    <label class="an-label" for="an-to">To</label>
                    <input id="an-to" type="date" name="date_to" value="{{ request('date_to') }}" class="an-input">
                </div>
            </div>

            <div class="an-filter-actions">
                <button type="submit" class="an-btn primary"><i class="fas fa-filter"></i> Apply filters</button>
                <a href="{{ route('students.announcements.index') }}" class="an-btn ghost"><i class="fas fa-rotate-left"></i> Reset</a>
            </div>
        </form>
    </section>

    {{-- ================= RESULTS ================= --}}
    @if($announcements->count())

        <section class="an-cards">
            @foreach($announcements as $announcement)
                @php
                    $priority = strtolower($announcement->priority ?? 'normal');
                    $priorityClass = in_array($priority, ['urgent', 'high']) ? $priority : 'normal';
                @endphp

                <article class="an-card {{ $priorityClass }}">
                    <div class="an-card-body">
                        <div class="an-badges">
                            @if($announcement->is_featured)
                                <span class="an-badge featured"><i class="fas fa-star"></i> Featured</span>
                            @endif
                            <span class="an-badge priority">{{ ucfirst($priority) }}</span>
                        </div>

                        <h3 class="an-title">{{ $announcement->title }}</h3>

                        <p class="an-text">
                            {{ \Illuminate\Support\Str::limit(strip_tags($announcement->description ?? $announcement->content ?? ''), 150) }}
                        </p>

                        <div class="an-meta">
                            <span><i class="far fa-calendar"></i>{{ $announcement->created_at ? $announcement->created_at->format('d M Y') : 'N/A' }}</span>
                            @if($announcement->type)
                                <span><i class="far fa-folder"></i>{{ ucfirst($announcement->type) }}</span>
                            @endif
                        </div>

                        <a href="{{ route('students.announcements.show', $announcement->id) }}" class="an-read">
                            Read announcement <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </article>
            @endforeach
        </section>

        @if($isPaginated && method_exists($announcements, 'hasPages') && $announcements->hasPages())
            <div class="an-pager">{{ $announcements->withQueryString()->links() }}</div>
        @endif

    @else

        <section class="an-empty">
            <div class="an-empty-icon"><i class="fas fa-bullhorn"></i></div>
            <h3>No announcements found</h3>
            <p>There are no announcements matching your current filters.</p>
            @if($filtersActive)
                <a href="{{ route('students.announcements.index') }}" class="an-btn ghost"><i class="fas fa-rotate-left"></i> Clear filters</a>
            @endif
        </section>

    @endif

</div>

@endsection