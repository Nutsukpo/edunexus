{{-- resources/views/students/timetable.blade.php --}}

@extends('students.layouts.app')

@section('title', 'My Timetable - EduNexus')

@section('content')

<style>
    :root {
        --edu-blue: #0d6efd;
        --edu-blue-dark: #084298;
        --edu-blue-soft: #eaf3ff;
        --edu-border: #dce7f5;
        --edu-text: #1f2937;
        --edu-muted: #64748b;
        --edu-white: #ffffff;
    }

    .student-timetable-page {
        padding: 20px 0 40px;
        color: var(--edu-text);
    }

    /* =========================================================
       HEADER
    ========================================================= */

    .timetable-header {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #084298 0%, #0d6efd 55%, #3d8bfd 100%);
        color: #fff;
        border-radius: 18px;
        padding: 28px 32px;
        margin-bottom: 24px;
        box-shadow: 0 10px 30px rgba(13, 110, 253, .18);
    }

    .timetable-header::after {
        content: "";
        position: absolute;
        width: 280px;
        height: 280px;
        border-radius: 50%;
        right: -90px;
        top: -130px;
        background: rgba(255,255,255,.08);
        pointer-events: none;
    }

    .timetable-header-content {
        position: relative;
        z-index: 2;
    }

    .timetable-header h2 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 750;
    }

    .timetable-header h2 i {
        margin-right: 10px;
    }

    .timetable-header p {
        margin: 7px 0 0;
        color: rgba(255,255,255,.82);
        font-size: .94rem;
    }

    /* =========================================================
       CURRENT CLASS CARD
    ========================================================= */

    .current-class-card {
        background: #fff;
        border: 1px solid var(--edu-border);
        border-radius: 16px;
        padding: 20px 22px;
        margin-bottom: 24px;
        box-shadow: 0 5px 18px rgba(15, 23, 42, .05);
    }

    .class-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .class-icon {
        width: 50px;
        height: 50px;
        border-radius: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--edu-blue-soft);
        color: var(--edu-blue);
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .class-label {
        color: var(--edu-muted);
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .6px;
        font-weight: 700;
        margin-bottom: 2px;
    }

    .class-name {
        font-size: 1.15rem;
        font-weight: 750;
        color: var(--edu-text);
    }

    .academic-info {
        text-align: right;
    }

    .academic-info .label {
        color: var(--edu-muted);
        font-size: .75rem;
        text-transform: uppercase;
        font-weight: 700;
    }

    .academic-info .value {
        font-weight: 650;
        color: var(--edu-text);
        margin-top: 2px;
    }

    /* =========================================================
       CONTROL CARD
    ========================================================= */

    .timetable-controls {
        background: #fff;
        border: 1px solid var(--edu-border);
        border-radius: 16px;
        padding: 18px 20px;
        margin-bottom: 20px;
        box-shadow: 0 5px 18px rgba(15, 23, 42, .04);
    }

    .control-label {
        font-size: .78rem;
        font-weight: 750;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--edu-muted);
        margin-bottom: 7px;
    }

    .form-select {
        min-height: 44px;
        border: 1px solid #cfdced;
        border-radius: 10px;
        color: var(--edu-text);
        font-size: .92rem;
    }

    .form-select:focus {
        border-color: var(--edu-blue);
        box-shadow: 0 0 0 .2rem rgba(13,110,253,.12);
    }

    /* =========================================================
       TIMETABLE CARD
    ========================================================= */

    .timetable-card {
        background: #fff;
        border: 1px solid var(--edu-border);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(15, 23, 42, .06);
    }

    .timetable-card-header {
        padding: 17px 20px;
        border-bottom: 1px solid var(--edu-border);
        background: #f8fbff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        flex-wrap: wrap;
    }

    .file-details {
        min-width: 0;
    }

    .file-title {
        font-weight: 750;
        color: var(--edu-text);
        font-size: 1rem;
        word-break: break-word;
    }

    .file-meta {
        color: var(--edu-muted);
        font-size: .82rem;
        margin-top: 4px;
    }

    .file-meta span {
        margin-right: 12px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #e8f7ee;
        color: #198754;
        border: 1px solid #ccebd8;
        border-radius: 20px;
        padding: 5px 10px;
        font-size: .76rem;
        font-weight: 700;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .action-buttons .btn {
        border-radius: 9px;
        font-size: .82rem;
        font-weight: 650;
    }

    /* =========================================================
       VIEWER
    ========================================================= */

    .viewer-container {
        background: #eef4fb;
        padding: 12px;
    }

    .viewer-frame {
        width: 100%;
        height: 720px;
        border: 0;
        display: block;
        background: #fff;
        border-radius: 10px;
    }

    .image-viewer {
        width: 100%;
        min-height: 500px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border-radius: 10px;
        padding: 20px;
    }

    .image-viewer img {
        max-width: 100%;
        max-height: 680px;
        object-fit: contain;
        border-radius: 5px;
        box-shadow: 0 4px 15px rgba(0,0,0,.08);
    }

    /* =========================================================
       EMPTY STATE
    ========================================================= */

    .empty-timetable {
        background: #fff;
        border: 1px solid var(--edu-border);
        border-radius: 18px;
        padding: 65px 25px;
        text-align: center;
        box-shadow: 0 7px 22px rgba(15,23,42,.05);
    }

    .empty-icon {
        width: 78px;
        height: 78px;
        border-radius: 50%;
        background: var(--edu-blue-soft);
        color: var(--edu-blue);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 18px;
        font-size: 2rem;
    }

    .empty-timetable h4 {
        font-weight: 750;
        margin-bottom: 8px;
    }

    .empty-timetable p {
        max-width: 540px;
        margin: 0 auto;
        color: var(--edu-muted);
        line-height: 1.6;
    }

    /* =========================================================
       VERSION LIST
    ========================================================= */

    .versions-card {
        margin-top: 22px;
        background: #fff;
        border: 1px solid var(--edu-border);
        border-radius: 16px;
        overflow: hidden;
    }

    .versions-header {
        padding: 15px 18px;
        background: #f8fbff;
        border-bottom: 1px solid var(--edu-border);
        font-weight: 750;
    }

    .version-item {
        padding: 13px 18px;
        border-bottom: 1px solid #edf2f7;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
    }

    .version-item:last-child {
        border-bottom: 0;
    }

    .version-name {
        font-weight: 650;
        font-size: .9rem;
    }

    .version-date {
        color: var(--edu-muted);
        font-size: .78rem;
    }

    .current-version {
        font-size: .7rem;
        background: var(--edu-blue-soft);
        color: var(--edu-blue-dark);
        padding: 4px 8px;
        border-radius: 20px;
        font-weight: 700;
    }

    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 768px) {

        .student-timetable-page {
            padding-top: 10px;
        }

        .timetable-header {
            padding: 22px;
            border-radius: 14px;
        }

        .timetable-header h2 {
            font-size: 1.45rem;
        }

        .academic-info {
            text-align: left;
            margin-top: 15px;
        }

        .viewer-frame {
            height: 560px;
        }

        .timetable-card-header {
            align-items: flex-start;
        }

        .action-buttons {
            width: 100%;
        }

        .action-buttons .btn {
            flex: 1;
        }
    }
</style>

<div class="student-timetable-page">

```
{{-- =====================================================
     HEADER
====================================================== --}}
<div class="timetable-header">
    <div class="timetable-header-content">
        <h2>
            <i class="fas fa-calendar-alt"></i>
            My Timetable
        </h2>

        <p>
            View the timetable assigned to your current class.
        </p>
    </div>
</div>


{{-- =====================================================
     CURRENT CLASS
====================================================== --}}
@if(isset($currentClass) && $currentClass)

    <div class="current-class-card">
        <div class="row align-items-center">

            <div class="col-md-7">

                <div class="class-info">

                    <div class="class-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>

                    <div>
                        <div class="class-label">
                            Current Class
                        </div>

                        <div class="class-name">
                            {{ $currentClass->name ?? 'Not Assigned' }}
                        </div>
                    </div>

                </div>

            </div>

            <div class="col-md-5">

                <div class="academic-info">

                    <div class="label">
                        Academic Year
                    </div>

                    <div class="value">
                        {{ $academicYear->name ?? 'Current Academic Year' }}
                    </div>

                </div>

            </div>

        </div>
    </div>

@endif


{{-- =====================================================
     TIMETABLE EXISTS
====================================================== --}}
@if(isset($timetable) && $timetable)

    @php

        /*
        |--------------------------------------------------------------------------
        | Determine whether this is the selected/current timetable
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | We DO NOT call $timetable->isCurrent().
        |
        | Timetable model has no isCurrent() method.
        |
        | The controller already selects the timetable belonging to
        | the student's current class.
        |
        */

        $currentTimetableId = (int) $timetable->id;

        $fileType = strtolower(
            (string) ($timetable->file_type ?? pathinfo($timetable->file_name ?? '', PATHINFO_EXTENSION))
        );

        $isImage = in_array($fileType, [
            'jpg',
            'jpeg',
            'png',
            'gif',
            'webp'
        ]);

        $isPdf = $fileType === 'pdf';

    @endphp


    {{-- =================================================
         VERSION SELECTOR
    ================================================== --}}
    @if(isset($availableTimetables) && $availableTimetables->count() > 0)

        <div class="timetable-controls">

            <div class="row align-items-end">

                <div class="col-md-8">

                    <div class="control-label">
                        Available Timetables
                    </div>

                    <select
                        id="timetableSelector"
                        class="form-select"
                    >

                        @foreach($availableTimetables as $version)

                            @php
                                $versionType = strtolower(
                                    (string) (
                                        $version->file_type
                                        ?? pathinfo($version->file_name ?? '', PATHINFO_EXTENSION)
                                    )
                                );
                            @endphp

                            <option
                                value="{{ $version->id }}"
                                data-stream-url="{{ route('students.timetable.stream', $version->id) }}"
                                data-download-url="{{ route('students.timetable.download', $version->id) }}"
                                data-file-name="{{ $version->file_name }}"
                                data-file-type="{{ $versionType }}"
                                data-date="{{ $version->created_at ? $version->created_at->format('M d, Y') : 'N/A' }}"
                                {{ (int) $version->id === $currentTimetableId ? 'selected' : '' }}
                            >
                                {{ $version->file_name }}
                                — {{ $version->created_at ? $version->created_at->format('M d, Y') : 'N/A' }}
                            </option>

                        @endforeach

                    </select>

                </div>

                <div class="col-md-4 text-md-end mt-3 mt-md-0">

                    <span class="status-badge">
                        <i class="fas fa-check-circle"></i>
                        Active Timetable
                    </span>

                </div>

            </div>

        </div>

    @endif


    {{-- =================================================
         MAIN TIMETABLE
    ================================================== --}}
    <div class="timetable-card">

        <div class="timetable-card-header">

            <div class="file-details">

                <div
                    class="file-title"
                    id="timetableFileName"
                >
                    <i class="fas fa-file-alt text-primary me-2"></i>
                    {{ $timetable->file_name }}
                </div>

                <div class="file-meta">

                    @if($timetable->created_at)
                        <span>
                            <i class="far fa-calendar me-1"></i>
                            Uploaded {{ $timetable->created_at->format('M d, Y') }}
                        </span>
                    @endif

                    @if($timetable->file_size)
                        <span>
                            <i class="fas fa-database me-1"></i>
                            {{ number_format($timetable->file_size / 1024, 1) }} KB
                        </span>
                    @endif

                </div>

            </div>


            <div class="action-buttons">

                <a
                    id="downloadTimetable"
                    href="{{ route('students.timetable.download', $timetable->id) }}"
                    class="btn btn-primary"
                >
                    <i class="fas fa-download me-1"></i>
                    Download
                </a>

                <button
                    type="button"
                    id="fullscreenTimetable"
                    class="btn btn-outline-primary"
                >
                    <i class="fas fa-expand me-1"></i>
                    Fullscreen
                </button>

            </div>

        </div>


        {{-- =================================================
             VIEWER
        ================================================== --}}
        <div
            class="viewer-container"
            id="timetableViewer"
        >

            @if($isImage)

                <div class="image-viewer">

                    <img
                        id="timetableImage"
                        src="{{ route('students.timetable.stream', $timetable->id) }}"
                        alt="Student Timetable"
                    >

                </div>

            @elseif($isPdf)

                <iframe
                    id="timetableFrame"
                    class="viewer-frame"
                    src="{{ route('students.timetable.stream', $timetable->id) }}"
                    title="Student Timetable"
                ></iframe>

            @else

                <div class="empty-timetable">

                    <div class="empty-icon">
                        <i class="fas fa-file-download"></i>
                    </div>

                    <h4>
                        Timetable File Ready
                    </h4>

                    <p>
                        This timetable format cannot be displayed directly
                        in the browser. Please download the timetable to
                        view it.
                    </p>

                    <a
                        href="{{ route('students.timetable.download', $timetable->id) }}"
                        class="btn btn-primary mt-4"
                    >
                        <i class="fas fa-download me-1"></i>
                        Download Timetable
                    </a>

                </div>

            @endif

        </div>

    </div>


    {{-- =================================================
         DESCRIPTION
    ================================================== --}}
    @if($timetable->description)

        <div class="versions-card">

            <div class="versions-header">
                <i class="fas fa-info-circle text-primary me-2"></i>
                Timetable Information
            </div>

            <div class="p-3 text-muted">
                {{ $timetable->description }}
            </div>

        </div>

    @endif


    {{-- =================================================
         AVAILABLE VERSIONS
    ================================================== --}}
    @if(isset($availableTimetables) && $availableTimetables->count() > 1)

        <div class="versions-card">

            <div class="versions-header">
                <i class="fas fa-history text-primary me-2"></i>
                Timetable Versions
            </div>

            @foreach($availableTimetables as $version)

                <div class="version-item">

                    <div>

                        <div class="version-name">
                            {{ $version->file_name }}
                        </div>

                        <div class="version-date">
                            Uploaded
                            {{ $version->created_at ? $version->created_at->format('M d, Y H:i') : 'N/A' }}
                        </div>

                    </div>

                    @if((int) $version->id === $currentTimetableId)

                        <span class="current-version">
                            CURRENT
                        </span>

                    @endif

                </div>

            @endforeach

        </div>

    @endif


{{-- =====================================================
     NO TIMETABLE
====================================================== --}}
@else

    <div class="empty-timetable">

        <div class="empty-icon">
            <i class="fas fa-calendar-times"></i>
        </div>

        <h4>
            No Timetable Available
        </h4>

        <p>

            @if(isset($message) && $message)
                {{ $message }}
            @else
                A timetable has not yet been uploaded for your
                current class. Please contact the school administrator.
            @endif

        </p>

    </div>

@endif


</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const selector = document.getElementById('timetableSelector');
    const downloadButton = document.getElementById('downloadTimetable');
    const viewer = document.getElementById('timetableViewer');
    const fileNameElement = document.getElementById('timetableFileName');
    const fullscreenButton = document.getElementById('fullscreenTimetable');

    /*
    |--------------------------------------------------------------------------
    | Change timetable version
    |--------------------------------------------------------------------------
    */

    if (selector) {

        selector.addEventListener('change', function () {

            const selected = this.options[this.selectedIndex];

            if (!selected) {
                return;
            }

            const streamUrl = selected.dataset.streamUrl;
            const downloadUrl = selected.dataset.downloadUrl;
            const fileName = selected.dataset.fileName || 'Timetable';
            const fileType = (selected.dataset.fileType || '').toLowerCase();

            if (downloadButton && downloadUrl) {
                downloadButton.href = downloadUrl;
            }

            if (fileNameElement) {

                fileNameElement.innerHTML =
                    '<i class="fas fa-file-alt text-primary me-2"></i>' +
                    escapeHtml(fileName);

            }

            /*
            |--------------------------------------------------------------------------
            | Rebuild viewer
            |--------------------------------------------------------------------------
            */

            if (!viewer || !streamUrl) {
                return;
            }

            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileType)) {

                viewer.innerHTML = `
                    <div class="image-viewer">
                        <img
                            id="timetableImage"
                            src="${streamUrl}"
                            alt="Student Timetable"
                        >
                    </div>
                `;

            } else if (fileType === 'pdf') {

                viewer.innerHTML = `
                    <iframe
                        id="timetableFrame"
                        class="viewer-frame"
                        src="${streamUrl}"
                        title="Student Timetable"
                    ></iframe>
                `;

            } else {

                viewer.innerHTML = `
                    <div class="empty-timetable">
                        <div class="empty-icon">
                            <i class="fas fa-file-download"></i>
                        </div>

                        <h4>Timetable File Ready</h4>

                        <p>
                            This file format cannot be displayed directly
                            in the browser. Please download the timetable.
                        </p>

                        <a
                            href="${downloadUrl}"
                            class="btn btn-primary mt-4"
                        >
                            <i class="fas fa-download me-1"></i>
                            Download Timetable
                        </a>
                    </div>
                `;

            }

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Fullscreen
    |--------------------------------------------------------------------------
    */

    if (fullscreenButton) {

        fullscreenButton.addEventListener('click', function () {

            const frame = document.getElementById('timetableFrame');
            const image = document.getElementById('timetableImage');

            const element = frame || image || viewer;

            if (!element) {
                return;
            }

            if (element.requestFullscreen) {
                element.requestFullscreen();
            } else if (element.webkitRequestFullscreen) {
                element.webkitRequestFullscreen();
            }

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Escape HTML
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value) {

        const div = document.createElement('div');

        div.textContent = value;

        return div.innerHTML;

    }

});
</script>

@endsection
