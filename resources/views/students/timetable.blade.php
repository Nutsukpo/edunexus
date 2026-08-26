{{-- resources/views/students/timetable.blade.php --}}
@extends('students.layouts.app')

@section('title', 'My Timetable - EduNexus')

@section('content')
<style>
    .timetable-container {
        padding: 20px 0;
    }

    .timetable-header {
        background: linear-gradient(135deg, #1a0000 0%, #4a0000 30%, #6b0000 60%, #8b0000 100%);
        color: white;
        border-radius: 16px;
        padding: 30px 35px;
        margin-bottom: 30px;
        box-shadow: 0 8px 32px rgba(139, 0, 0, 0.25);
        position: relative;
        overflow: hidden;
    }

    .timetable-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 50%;
        pointer-events: none;
    }

    .timetable-header h2 {
        font-weight: 700;
        font-size: 2rem;
        margin-bottom: 5px;
        letter-spacing: -0.5px;
    }

    .timetable-header h2 i {
        color: #fca5a5;
        margin-right: 12px;
    }

    .timetable-header .sub-info {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.95rem;
    }

    .timetable-header .sub-info i {
        margin-right: 6px;
        color: #fca5a5;
    }

    .timetable-header .header-badge {
        background: rgba(255, 255, 255, 0.12);
        padding: 8px 18px;
        border-radius: 30px;
        font-size: 0.85rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .timetable-header .header-badge i {
        color: #fca5a5;
    }

    /* PDF Viewer */
    .pdf-viewer-wrapper {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        overflow: hidden;
        border: 1px solid rgba(0, 0, 0, 0.04);
        min-height: 500px;
        position: relative;
    }

    .pdf-viewer-wrapper .pdf-loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        z-index: 5;
    }

    .pdf-viewer-wrapper .pdf-loading .spinner {
        width: 50px;
        height: 50px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #dc2626;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 15px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .pdf-viewer-wrapper .pdf-loading p {
        color: #64748b;
        font-size: 0.9rem;
    }

    .pdf-viewer-wrapper iframe {
        width: 100%;
        height: 800px;
        border: none;
        display: block;
    }

    .pdf-viewer-wrapper .pdf-toolbar {
        padding: 12px 20px;
        background: #f8fafc;
        border-bottom: 1px solid #eef2f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .pdf-viewer-wrapper .pdf-toolbar .pdf-info {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    .pdf-viewer-wrapper .pdf-toolbar .pdf-info .file-name {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.95rem;
    }

    .pdf-viewer-wrapper .pdf-toolbar .pdf-info .file-meta {
        color: #94a3b8;
        font-size: 0.75rem;
    }

    .pdf-viewer-wrapper .pdf-toolbar .pdf-actions {
        display: flex;
        gap: 8px;
    }

    .no-timetable {
        text-align: center;
        padding: 80px 20px;
    }

    .no-timetable i {
        font-size: 4rem;
        color: #e2e8f0;
        margin-bottom: 20px;
    }

    .no-timetable h4 {
        color: #1e293b;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .no-timetable p {
        color: #94a3b8;
        max-width: 500px;
        margin: 0 auto 20px;
    }

    .timetable-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 25px;
    }

    .timetable-controls .btn-outline-edunexus {
        border: 2px solid #dc2626;
        color: #dc2626;
        background: transparent;
        padding: 8px 18px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }

    .timetable-controls .btn-outline-edunexus:hover {
        background: #dc2626;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(220, 38, 38, 0.25);
    }

    .timetable-controls .btn-edunexus {
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }

    .timetable-controls .btn-edunexus:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
        color: white;
    }

    .timetable-version-selector {
        background: #f8fafc;
        padding: 10px 15px;
        border-radius: 12px;
        border: 1px solid #eef2f6;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .timetable-version-selector label {
        font-weight: 500;
        color: #475569;
        font-size: 0.85rem;
        margin: 0;
    }

    .timetable-version-selector select {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 0.85rem;
        background: white;
        min-width: 200px;
        color: #1e293b;
    }

    .timetable-version-selector select:focus {
        border-color: #dc2626;
        outline: none;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
    }

    .current-badge {
        background: #22c55e;
        color: white;
        font-size: 0.6rem;
        padding: 2px 10px;
        border-radius: 20px;
        font-weight: 600;
    }

    .version-badge {
        background: #dc2626;
        color: white;
        font-size: 0.6rem;
        padding: 2px 10px;
        border-radius: 20px;
        font-weight: 600;
    }

    /* Info Cards */
    .info-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
        padding: 20px;
        border: 1px solid #eef2f6;
        height: 100%;
    }

    .info-card .card-title {
        color: #64748b;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .info-card .info-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .info-card .info-item:last-child {
        border-bottom: none;
    }

    .info-card .info-item .label {
        color: #94a3b8;
        font-size: 0.8rem;
    }

    .info-card .info-item .value {
        font-weight: 500;
        color: #1e293b;
        font-size: 0.85rem;
    }

    .help-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .help-list li {
        padding: 8px 0;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #64748b;
        font-size: 0.85rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .help-list li:last-child {
        border-bottom: none;
    }

    .help-list li i {
        color: #dc2626;
        width: 20px;
    }

    @media (max-width: 768px) {
        .timetable-header {
            padding: 20px;
        }
        .timetable-header h2 {
            font-size: 1.4rem;
        }
        .pdf-viewer-wrapper iframe {
            height: 500px;
        }
        .timetable-controls {
            flex-direction: column;
            align-items: stretch;
        }
        .timetable-version-selector {
            flex-direction: column;
            align-items: stretch;
        }
        .timetable-version-selector select {
            width: 100%;
        }
    }

    @media print {
        .timetable-controls,
        .timetable-version-selector,
        .pdf-toolbar,
        .navbar,
        .footer,
        .menu-btn {
            display: none !important;
        }
        .pdf-viewer-wrapper {
            box-shadow: none;
            border: none;
        }
        .pdf-viewer-wrapper iframe {
            height: 100vh;
        }
    }
</style>

<div class="timetable-container">
    <!-- Header -->
    <div class="timetable-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h2><i class="fas fa-calendar-alt"></i> My Timetable</h2>
                <div class="sub-info">
                    <i class="fas fa-user-graduate"></i>
                    <span>{{ $student->full_name ?? 'Student' }}</span>
                    <span class="mx-2">|</span>
                    <i class="fas fa-graduation-cap"></i>
                    <span>{{ $student->class->name ?? 'Not Assigned' }}</span>
                    <span class="mx-2">|</span>
                    <i class="fas fa-calendar-week"></i>
                    <span>{{ isset($term) && $term ? $term->name : (isset($academicYear) && $academicYear ? $academicYear->name : 'Current Term') }}</span>
                </div>
            </div>
            <div class="header-badge mt-2 mt-sm-0">
                <i class="fas fa-clock"></i>
                <span>{{ now()->format('l, F d, Y') }}</span>
            </div>
        </div>
    </div>

    <!-- Controls -->
    <div class="timetable-controls">
        @if(isset($availableTimetables) && $availableTimetables->count() > 0)
        <div class="timetable-version-selector">
            <label for="timetableSelect">
                <i class="fas fa-history"></i> Timetable Version:
            </label>
            <select id="timetableSelect" class="form-select form-select-sm">
                @foreach($availableTimetables as $t)
                    <option value="{{ $t->id }}" 
                        data-url="{{ $t->full_url ?? '' }}"
                        data-name="{{ $t->file_name }}"
                        data-size="{{ $t->formatted_file_size ?? 'N/A' }}"
                        data-date="{{ $t->created_at ? $t->created_at->format('M d, Y') : 'N/A' }}"
                        data-current="{{ $t->isCurrent() ? 'true' : 'false' }}"
                        {{ isset($timetable) && $timetable && $timetable->id == $t->id ? 'selected' : '' }}>
                        {{ $t->file_name }} 
                        @if($t->isCurrent()) (Current) @endif
                        - {{ $t->created_at ? $t->created_at->format('M d, Y') : 'N/A' }}
                    </option>
                @endforeach
            </select>
            @if(isset($timetable) && $timetable && $timetable->isCurrent())
                <span class="current-badge">
                    <i class="fas fa-check-circle"></i> Current
                </span>
            @endif
            @if($availableTimetables->count() > 1)
                <span class="version-badge">
                    {{ $availableTimetables->count() }} versions
                </span>
            @endif
        </div>
        @endif
    </div>

    <!-- PDF Viewer -->
    <div class="pdf-viewer-wrapper">
        @if(isset($timetable) && $timetable)
            <div class="pdf-toolbar">
                <div class="pdf-info">
                    <span class="file-name" id="pdfFileName">
                        <i class="fas fa-file-pdf text-danger"></i> {{ $timetable->file_name }}
                    </span>
                    <span class="file-meta" id="pdfMeta">
                        {{ $timetable->formatted_file_size ?? 'N/A' }} • 
                        Uploaded: {{ $timetable->created_at ? $timetable->created_at->format('M d, Y') : 'N/A' }}
                        @if($timetable->description)
                            • {{ $timetable->description }}
                        @endif
                    </span>
                </div>
                <div class="pdf-actions">
                    <button class="btn btn-outline-danger btn-sm" id="viewFullscreen">
                        <i class="fas fa-expand"></i> Fullscreen
                    </button>
                </div>
            </div>

            <div id="pdfLoading" class="pdf-loading" style="display: none;">
                <div class="spinner"></div>
                <p>Loading timetable...</p>
            </div>

            <iframe 
                id="pdfIframe"
                src="{{ route('students.timetable.stream', $timetable->id) }}"
                onload="document.getElementById('pdfLoading').style.display='none'">
            </iframe>
        @else
            <div class="no-timetable">
                <i class="fas fa-calendar-times"></i>
                <h4>No Timetable Available</h4>
                <p>
                    @if(isset($message))
                        {{ $message }}
                    @else
                        Your timetable has not been uploaded yet. Please contact your class teacher or school administrator for assistance.
                    @endif
                </p>
            </div>
        @endif
    </div>

    <!-- Timetable Information -->
    @if(isset($timetable) && $timetable)
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="info-card">
                <h6 class="card-title">
                    <i class="fas fa-info-circle text-danger"></i> Timetable Information
                </h6>
                <div class="info-item">
                    <span class="label">Class</span>
                    <span class="value">{{ $timetable->class->name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Term</span>
                    <span class="value">{{ $timetable->term->name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Academic Year</span>
                    <span class="value">{{ $timetable->academicYear->name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Status</span>
                    <span class="value">
                        <span class="badge {{ $timetable->isCurrent() ? 'bg-success' : 'bg-secondary' }}">
                            {{ $timetable->isCurrent() ? 'Current' : 'Archived' }}
                        </span>
                    </span>
                </div>
                <div class="info-item">
                    <span class="label">File Name</span>
                    <span class="value">{{ $timetable->file_name }}</span>
                </div>
                <div class="info-item">
                    <span class="label">File Size</span>
                    <span class="value">{{ $timetable->formatted_file_size ?? 'N/A' }}</span>
                </div>
                @if($timetable->description)
                <div class="info-item">
                    <span class="label">Description</span>
                    <span class="value">{{ $timetable->description }}</span>
                </div>
                @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-card">
                <h6 class="card-title">
                    <i class="fas fa-question-circle text-danger"></i> Quick Help
                </h6>
                <ul class="help-list">
                    <li>
                        <i class="fas fa-download"></i>
                        <span>Download the timetable for offline viewing</span>
                    </li>
                    <li>
                        <i class="fas fa-print"></i>
                        <span>Print a physical copy of your timetable</span>
                    </li>
                    <li>
                        <i class="fas fa-expand"></i>
                        <span>View in fullscreen mode for better readability</span>
                    </li>
                    <li>
                        <i class="fas fa-history"></i>
                        <span>Switch between different timetable versions using the dropdown</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
$(document).ready(function() {
    // Get the base URL for routes
    const baseUrl = "{{ url('/') }}";
    
    // Timetable version selector
    $('#timetableSelect').on('change', function() {
        const selected = $(this).find(':selected');
        const timetableId = $(this).val();
        const fileName = selected.data('name');
        const fileSize = selected.data('size');
        const uploadDate = selected.data('date');
        const isCurrent = selected.data('current') === 'true';
        
        if (!timetableId) return;
        
        // Update toolbar info
        $('#pdfFileName').html(`<i class="fas fa-file-pdf text-danger"></i> ${fileName}`);
        $('#pdfMeta').text(`${fileSize} • Uploaded: ${uploadDate}`);
        
        // Show loading
        $('#pdfLoading').show();
        $('#pdfIframe').hide();
        
        // Update iframe - use the stream route with full URL
        const iframeUrl = baseUrl + '/student/timetable/stream/' + timetableId;
        $('#pdfIframe').attr('src', iframeUrl);
        
        // Update download button with data attribute
        $('#downloadTimetable').data('id', timetableId);
        
        // Update current badge
        $('.current-badge').remove();
        if (isCurrent) {
            $('.timetable-version-selector').append(
                '<span class="current-badge"><i class="fas fa-check-circle"></i> Current</span>'
            );
        }
    });

    // Download timetable
    $('#downloadTimetable').on('click', function() {
        const timetableId = $('#timetableSelect').val();
        if (!timetableId) {
            if (typeof toastr !== 'undefined') {
                toastr.warning('No timetable available to download');
            } else {
                alert('No timetable available to download');
            }
            return;
        }
        
        // Use the download route
        const downloadUrl = baseUrl + '/student/timetable/download/' + timetableId;
        window.location.href = downloadUrl;
    });

    // Print timetable
    $('#printTimetable').on('click', function() {
        const iframe = document.getElementById('pdfIframe');
        if (iframe && iframe.contentWindow) {
            try {
                iframe.contentWindow.print();
            } catch(e) {
                if (typeof toastr !== 'undefined') {
                    toastr.warning('Please wait for the timetable to fully load before printing');
                }
            }
        } else {
            if (typeof toastr !== 'undefined') {
                toastr.warning('Please wait for the timetable to load before printing');
            }
        }
    });

    // Fullscreen
    $('#viewFullscreen').on('click', function() {
        const iframe = document.getElementById('pdfIframe');
        if (iframe) {
            if (iframe.requestFullscreen) {
                iframe.requestFullscreen();
            } else if (iframe.webkitRequestFullscreen) {
                iframe.webkitRequestFullscreen();
            } else if (iframe.msRequestFullscreen) {
                iframe.msRequestFullscreen();
            } else {
                // Fallback - open in new window
                const url = iframe.src;
                window.open(url, '_blank', 'width=1000,height=800');
            }
        }
    });

    // Handle iframe load
    $('#pdfIframe').on('load', function() {
        $('#pdfLoading').hide();
        $(this).show();
    });

    // Handle iframe error
    $('#pdfIframe').on('error', function() {
        $('#pdfLoading').hide();
        if (typeof toastr !== 'undefined') {
            toastr.error('Failed to load the timetable. Please try again.');
        }
    });

    // Handle iframe load error for the initial load
    setTimeout(function() {
        const iframe = document.getElementById('pdfIframe');
        if (iframe && !iframe.contentWindow) {
            $('#pdfLoading').hide();
            if (typeof toastr !== 'undefined') {
                toastr.error('Failed to load the timetable. Please refresh the page.');
            }
        }
    }, 10000); // 10 second timeout
});
</script>

@endsection