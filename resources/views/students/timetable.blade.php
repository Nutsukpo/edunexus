{{-- resources/views/students/timetable.blade.php --}}

@extends('students.layouts.app')

@section('title', 'My Timetable - EduNexus')

@section('content')

@php
    $hasTimetable = isset($timetable) && $timetable;
    $versions     = isset($availableTimetables) ? $availableTimetables : collect();
    $className    = isset($currentClass) && $currentClass ? ($currentClass->name ?? 'Not assigned') : null;
    $yearName     = $academicYear->name ?? 'Current academic year';

    if ($hasTimetable) {
        // The controller already selects the timetable for the student's current class.
        $currentTimetableId = (int) $timetable->id;
        $fileType = strtolower((string) ($timetable->file_type ?? pathinfo($timetable->file_name ?? '', PATHINFO_EXTENSION)));
        $isImage  = in_array($fileType, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        $isPdf    = $fileType === 'pdf';
    }
@endphp

<style>
    .tt {
        --navy: #0b2447; --navy-2: #123a73; --blue: #1d6fd6; --sky: #4da3ff; --green: #16a36a;
        --ink: #14213d; --muted: #667388; --faint: #98a3b5; --line: #e7ebf1; --bg: #f4f6fa;
        --radius: 16px; --shadow: 0 1px 2px rgba(16,36,72,.04), 0 6px 20px rgba(16,36,72,.05);
        background: var(--bg); color: var(--ink); min-height: 100vh;
        padding: 28px clamp(16px, 3vw, 36px) 56px;
        font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
    }
    .tt *, .tt *::before, .tt *::after { box-sizing: border-box; }
    .tt h1, .tt h2, .tt h3, .tt p { margin: 0; }

    /* ---------- Hero ---------- */
    .tt-hero {
        position: relative; overflow: hidden; color: #fff; border-radius: 20px; padding: 30px 34px; margin-bottom: 22px;
        background: linear-gradient(120deg, var(--navy) 0%, var(--navy-2) 60%, #1b5aa8 100%);
        box-shadow: 0 12px 32px rgba(11,36,71,.22);
    }
    .tt-hero::after {
        content: ""; position: absolute; right: -80px; top: -120px; width: 320px; height: 320px; border-radius: 50%;
        background: radial-gradient(circle, rgba(77,163,255,.35), transparent 70%); pointer-events: none;
    }
    .tt-crumbs { position: relative; z-index: 1; display: flex; align-items: center; gap: 8px; font-size: 12px; color: rgba(255,255,255,.65); margin-bottom: 16px; }
    .tt-crumbs i { font-size: 9px; }
    .tt-hero-row { position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; gap: 22px; flex-wrap: wrap; }
    .tt-hero h1 { font-size: clamp(22px, 2.6vw, 30px); font-weight: 700; letter-spacing: -.01em; margin-bottom: 6px; }
    .tt-hero p { font-size: 14px; color: rgba(255,255,255,.75); }
    .tt-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .tt-chip { display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 999px; font-size: 13px; background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.18); }
    .tt-chip i { color: var(--sky); }

    /* ---------- Cards ---------- */
    .tt-card { background: #fff; border: 1px solid var(--line); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; margin-bottom: 22px; }
    .tt-card:last-child { margin-bottom: 0; }
    .tt-card-pad { padding: 22px 24px; }
    .tt-eyebrow { display: block; margin-bottom: 4px; font-size: 10.5px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: var(--blue); }

    /* Selector */
    .tt-select-row { display: flex; align-items: flex-end; justify-content: space-between; gap: 18px; flex-wrap: wrap; }
    .tt-select-wrap { flex: 1; min-width: 240px; }
    .tt-label { display: block; margin-bottom: 8px; font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); }
    .tt-select {
        width: 100%; height: 46px; padding: 0 14px; border-radius: 12px; border: 1px solid #d3dcea; background-color: #fff;
        font-size: 14px; color: var(--ink); transition: border-color .15s ease, box-shadow .15s ease;
    }
    .tt-select:focus { outline: 0; border-color: var(--blue); box-shadow: 0 0 0 4px rgba(29,111,214,.12); }
    .tt-live { display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 999px; font-size: 12.5px; font-weight: 700; color: #0f7d51; background: #dff5ea; }
    .tt-live i { width: 7px; height: 7px; border-radius: 50%; background: var(--green); box-shadow: 0 0 0 3px rgba(22,163,106,.2); }

    /* Viewer toolbar */
    .tt-bar { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; padding: 18px 24px; border-bottom: 1px solid var(--line); }
    .tt-file { display: flex; align-items: center; gap: 14px; min-width: 0; }
    .tt-file-icon { width: 44px; height: 44px; flex-shrink: 0; border-radius: 13px; display: grid; place-items: center; font-size: 18px; background: #e8f1fd; color: var(--blue); }
    .tt-file-name { font-size: 15px; font-weight: 700; word-break: break-word; }
    .tt-file-meta { display: flex; flex-wrap: wrap; gap: 4px 16px; margin-top: 4px; font-size: 12.5px; color: var(--muted); }
    .tt-file-meta i { margin-right: 6px; color: var(--faint); }
    .tt-actions { display: flex; gap: 10px; flex-wrap: wrap; }
    .tt-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 16px; border-radius: 12px;
        font-size: 13.5px; font-weight: 600; text-decoration: none; border: 1px solid transparent; cursor: pointer;
        transition: background .15s ease, box-shadow .15s ease, border-color .15s ease;
    }
    .tt-btn.primary { color: #fff; background: linear-gradient(135deg, #2a7fe8, #1d6fd6); box-shadow: 0 6px 16px rgba(29,111,214,.28); }
    .tt-btn.primary:hover { color: #fff; box-shadow: 0 8px 20px rgba(29,111,214,.38); }
    .tt-btn.ghost { color: var(--ink); background: #fff; border-color: #d3dcea; }
    .tt-btn.ghost:hover { background: #f1f5fb; }
    .tt-btn.small { padding: 7px 12px; font-size: 12.5px; border-radius: 10px; }

    /* Viewer */
    .tt-viewer { background: #e9eef6; padding: 14px; }
    .tt-frame { display: block; width: 100%; height: 720px; border: 0; border-radius: 12px; background: #fff; }
    .tt-image { display: flex; align-items: center; justify-content: center; min-height: 460px; padding: 20px; border-radius: 12px; background: #fff; }
    .tt-image img { max-width: 100%; max-height: 700px; object-fit: contain; border-radius: 6px; box-shadow: 0 4px 18px rgba(16,36,72,.10); }

    /* Info / versions */
    .tt-head { display: flex; align-items: center; gap: 12px; padding: 18px 24px; border-bottom: 1px solid var(--line); }
    .tt-head-icon { width: 36px; height: 36px; border-radius: 10px; display: grid; place-items: center; background: #e8f1fd; color: var(--blue); }
    .tt-head h2 { font-size: 15.5px; font-weight: 700; }
    .tt-desc { padding: 20px 24px; font-size: 14px; line-height: 1.65; color: var(--muted); }
    .tt-version { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 14px 24px; border-top: 1px solid var(--line); }
    .tt-version:first-of-type { border-top: 0; }
    .tt-version.is-current { background: #f7fbff; }
    .tt-version strong { display: block; font-size: 14px; word-break: break-word; }
    .tt-version small { display: block; margin-top: 3px; font-size: 12px; color: var(--muted); }
    .tt-pill { padding: 4px 10px; border-radius: 999px; font-size: 10.5px; font-weight: 700; letter-spacing: .1em; color: #0f7d51; background: #dff5ea; white-space: nowrap; }

    /* Empty */
    .tt-empty { padding: 70px 24px; text-align: center; }
    .tt-empty-icon { width: 76px; height: 76px; margin: 0 auto 18px; border-radius: 22px; display: grid; place-items: center; font-size: 30px; background: #e8f1fd; color: var(--blue); }
    .tt-empty h3 { font-size: 18px; font-weight: 700; margin-bottom: 8px; }
    .tt-empty p { max-width: 480px; margin: 0 auto; font-size: 14px; line-height: 1.6; color: var(--muted); }
    .tt-empty .tt-btn { margin-top: 22px; }
    .tt-viewer .tt-empty { background: #fff; border-radius: 12px; }

    @media (max-width: 767px) {
        .tt { padding: 18px 14px 90px; }
        .tt-hero { padding: 22px 20px; border-radius: 16px; }
        .tt-bar { padding: 16px; }
        .tt-actions { width: 100%; }
        .tt-actions .tt-btn { flex: 1; }
        .tt-frame { height: 560px; }
        .tt-card-pad, .tt-head, .tt-desc, .tt-version { padding-left: 16px; padding-right: 16px; }
    }
    @media print { .tt-actions, .tt-select-row { display: none; } }
</style>

<div class="tt">

    {{-- ================= HERO ================= --}}
    <header class="tt-hero">
        <div class="tt-crumbs">
            <span>Student Portal</span><i class="fas fa-chevron-right"></i><span>Timetable</span>
        </div>
        <div class="tt-hero-row">
            <div>
                <h1>My timetable</h1>
                <p>View the timetable assigned to your current class.</p>
            </div>
            @if($className)
                <div class="tt-chips">
                    <span class="tt-chip"><i class="fas fa-graduation-cap"></i>{{ $className }}</span>
                    <span class="tt-chip"><i class="fas fa-calendar"></i>{{ $yearName }}</span>
                </div>
            @endif
        </div>
    </header>

    @if($hasTimetable)

        {{-- ================= VERSION SELECTOR ================= --}}
        @if($versions->count() > 0)
            <section class="tt-card tt-card-pad">
                <div class="tt-select-row">
                    <div class="tt-select-wrap">
                        <label class="tt-label" for="timetableSelector">Available timetables</label>
                        <select id="timetableSelector" class="tt-select">
                            @foreach($versions as $version)
                                @php
                                    $versionType = strtolower((string) ($version->file_type ?? pathinfo($version->file_name ?? '', PATHINFO_EXTENSION)));
                                    $versionDate = $version->created_at ? $version->created_at->format('M d, Y') : 'N/A';
                                @endphp
                                <option
                                    value="{{ $version->id }}"
                                    data-stream-url="{{ route('students.timetable.stream', $version->id) }}"
                                    data-download-url="{{ route('students.timetable.download', $version->id) }}"
                                    data-file-name="{{ $version->file_name }}"
                                    data-file-type="{{ $versionType }}"
                                    data-date="{{ $versionDate }}"
                                    {{ (int) $version->id === $currentTimetableId ? 'selected' : '' }}
                                >
                                    {{ $version->file_name }} — {{ $versionDate }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <span class="tt-live"><i></i> Active timetable</span>
                </div>
            </section>
        @endif

        {{-- ================= VIEWER ================= --}}
        <section class="tt-card">
            <div class="tt-bar">
                <div class="tt-file">
                    <span class="tt-file-icon"><i class="fas fa-file-lines"></i></span>
                    <div style="min-width:0">
                        <div class="tt-file-name" id="timetableFileName">{{ $timetable->file_name }}</div>
                        <div class="tt-file-meta">
                            @if($timetable->created_at)
                                <span><i class="far fa-calendar"></i><span id="timetableDate">Uploaded {{ $timetable->created_at->format('M d, Y') }}</span></span>
                            @endif
                            @if($timetable->file_size)
                                <span><i class="fas fa-database"></i>{{ number_format($timetable->file_size / 1024, 1) }} KB</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="tt-actions">
                    <a id="downloadTimetable" href="{{ route('students.timetable.download', $timetable->id) }}" class="tt-btn primary">
                        <i class="fas fa-download"></i> Download
                    </a>
                    <button type="button" id="fullscreenTimetable" class="tt-btn ghost">
                        <i class="fas fa-expand"></i> Fullscreen
                    </button>
                </div>
            </div>

            <div class="tt-viewer" id="timetableViewer">
                @if($isImage)
                    <div class="tt-image">
                        <img id="timetableImage" src="{{ route('students.timetable.stream', $timetable->id) }}" alt="Student timetable">
                    </div>
                @elseif($isPdf)
                    <iframe id="timetableFrame" class="tt-frame" src="{{ route('students.timetable.stream', $timetable->id) }}" title="Student timetable"></iframe>
                @else
                    <div class="tt-empty">
                        <div class="tt-empty-icon"><i class="fas fa-file-arrow-down"></i></div>
                        <h3>Timetable file ready</h3>
                        <p>This format can't be displayed in the browser. Please download the timetable to view it.</p>
                        <a href="{{ route('students.timetable.download', $timetable->id) }}" class="tt-btn primary">
                            <i class="fas fa-download"></i> Download timetable
                        </a>
                    </div>
                @endif
            </div>
        </section>

        {{-- ================= DESCRIPTION ================= --}}
        @if($timetable->description)
            <section class="tt-card">
                <div class="tt-head">
                    <span class="tt-head-icon"><i class="fas fa-circle-info"></i></span>
                    <h2>Timetable information</h2>
                </div>
                <div class="tt-desc">{{ $timetable->description }}</div>
            </section>
        @endif

        {{-- ================= VERSIONS ================= --}}
        @if($versions->count() > 1)
            <section class="tt-card">
                <div class="tt-head">
                    <span class="tt-head-icon"><i class="fas fa-clock-rotate-left"></i></span>
                    <h2>Timetable versions</h2>
                </div>

                @foreach($versions as $version)
                    @php $isCurrentVersion = (int) $version->id === $currentTimetableId; @endphp
                    <div class="tt-version {{ $isCurrentVersion ? 'is-current' : '' }}">
                        <div>
                            <strong>{{ $version->file_name }}</strong>
                            <small>Uploaded {{ $version->created_at ? $version->created_at->format('M d, Y H:i') : 'N/A' }}</small>
                        </div>
                        @if($isCurrentVersion)
                            <span class="tt-pill">CURRENT</span>
                        @else
                            <button type="button" class="tt-btn ghost small" data-view-version="{{ $version->id }}">
                                <i class="far fa-eye"></i> View
                            </button>
                        @endif
                    </div>
                @endforeach
            </section>
        @endif

    @else

        {{-- ================= NO TIMETABLE ================= --}}
        <section class="tt-card">
            <div class="tt-empty">
                <div class="tt-empty-icon"><i class="fas fa-calendar-xmark"></i></div>
                <h3>No timetable available</h3>
                <p>
                    @if(isset($message) && $message)
                        {{ $message }}
                    @else
                        A timetable has not yet been uploaded for your current class. Please contact the school administrator.
                    @endif
                </p>
            </div>
        </section>

    @endif

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    var selector = document.getElementById('timetableSelector');
    var downloadButton = document.getElementById('downloadTimetable');
    var viewer = document.getElementById('timetableViewer');
    var fileNameEl = document.getElementById('timetableFileName');
    var dateEl = document.getElementById('timetableDate');
    var fullscreenButton = document.getElementById('fullscreenTimetable');

    var imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    function el(tag, attrs, html) {
        var node = document.createElement(tag);
        Object.keys(attrs || {}).forEach(function (k) { node.setAttribute(k, attrs[k]); });
        if (html) node.innerHTML = html;
        return node;
    }

    function renderViewer(streamUrl, downloadUrl, fileType) {
        viewer.innerHTML = '';

        if (imageTypes.indexOf(fileType) !== -1) {
            var wrap = el('div', { 'class': 'tt-image' });
            wrap.appendChild(el('img', { id: 'timetableImage', src: streamUrl, alt: 'Student timetable' }));
            viewer.appendChild(wrap);
        } else if (fileType === 'pdf') {
            viewer.appendChild(el('iframe', { id: 'timetableFrame', 'class': 'tt-frame', src: streamUrl, title: 'Student timetable' }));
        } else {
            var box = el('div', { 'class': 'tt-empty' },
                '<div class="tt-empty-icon"><i class="fas fa-file-arrow-down"></i></div>' +
                '<h3>Timetable file ready</h3>' +
                '<p>This format can\'t be displayed in the browser. Please download the timetable to view it.</p>');
            var link = el('a', { 'class': 'tt-btn primary', href: downloadUrl }, '<i class="fas fa-download"></i> Download timetable');
            box.appendChild(link);
            viewer.appendChild(box);
        }
    }

    if (selector && viewer) {
        selector.addEventListener('change', function () {
            var opt = this.options[this.selectedIndex];
            if (!opt) return;

            var streamUrl = opt.dataset.streamUrl;
            var downloadUrl = opt.dataset.downloadUrl;
            var fileType = (opt.dataset.fileType || '').toLowerCase();

            if (downloadButton && downloadUrl) downloadButton.href = downloadUrl;
            if (fileNameEl) fileNameEl.textContent = opt.dataset.fileName || 'Timetable';
            if (dateEl && opt.dataset.date) dateEl.textContent = 'Uploaded ' + opt.dataset.date;

            if (streamUrl) renderViewer(streamUrl, downloadUrl, fileType);
        });

        // "View" buttons in the versions list drive the selector.
        document.querySelectorAll('[data-view-version]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                selector.value = this.getAttribute('data-view-version');
                selector.dispatchEvent(new Event('change'));
                window.scrollTo({ top: viewer.getBoundingClientRect().top + window.scrollY - 120, behavior: 'smooth' });
            });
        });
    }

    if (fullscreenButton && viewer) {
        fullscreenButton.addEventListener('click', function () {
            var target = document.getElementById('timetableFrame') || document.getElementById('timetableImage') || viewer;
            if (target.requestFullscreen) target.requestFullscreen();
            else if (target.webkitRequestFullscreen) target.webkitRequestFullscreen();
        });
    }
});
</script>

@endsection