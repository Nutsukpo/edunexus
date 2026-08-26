@extends('students.layouts.app')

@section('title', 'Announcements - EduNexus')

@section('content')
<style>
    /* Header */
    .announcement-header {
        background: linear-gradient(135deg, #1a0000 0%, #4a0000 30%, #6b0000 60%, #8b0000 100%);
        color: white;
        border-radius: 16px;
        padding: 25px 30px;
        margin-bottom: 25px;
        box-shadow: 0 8px 32px rgba(139, 0, 0, 0.25);
        position: relative;
        overflow: hidden;
    }

    .announcement-header::before {
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

    .announcement-header h2 {
        font-weight: 700;
        font-size: 1.6rem;
        margin-bottom: 5px;
        letter-spacing: -0.5px;
        position: relative;
        z-index: 1;
    }

    .announcement-header h2 i {
        color: #fca5a5;
        margin-right: 12px;
    }

    .announcement-header .sub-info {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.85rem;
        position: relative;
        z-index: 1;
    }

    .announcement-header .header-badge {
        background: rgba(255, 255, 255, 0.12);
        padding: 6px 16px;
        border-radius: 30px;
        font-size: 0.8rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        position: relative;
        z-index: 1;
    }

    /* Filter Section */
    .filter-section {
        background: white;
        border-radius: 12px;
        padding: 15px 20px;
        border: 1px solid #eef2f6;
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .filter-section .filter-group {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filter-section .filter-group label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748b;
        margin: 0;
    }

    .filter-section select,
    .filter-section input {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 0.8rem;
        background: white;
        color: #1e293b;
    }

    .filter-section select:focus,
    .filter-section input:focus {
        border-color: #dc2626;
        outline: none;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
    }

    .search-box {
        position: relative;
    }

    .search-box i {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .search-box input {
        padding-left: 30px;
        min-width: 200px;
    }

    .btn-edunexus-sm {
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        color: white;
        border: none;
        padding: 6px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.75rem;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .btn-edunexus-sm:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
        color: white;
    }

    .btn-outline-sm {
        border: 1px solid #e2e8f0;
        color: #64748b;
        background: transparent;
        padding: 6px 16px;
        border-radius: 8px;
        font-weight: 500;
        font-size: 0.75rem;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .btn-outline-sm:hover {
        background: #f1f5f9;
    }

    /* Announcement Cards */
    .announcement-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #eef2f6;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
        margin-bottom: 16px;
        cursor: pointer;
    }

    .announcement-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        border-color: rgba(220, 38, 38, 0.15);
    }

    .announcement-card.unread {
        border-left: 4px solid #dc2626;
    }

    .announcement-card .card-header {
        padding: 16px 20px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        background: #fafafa;
        border-bottom: 1px solid #f1f5f9;
    }

    .announcement-card .card-header .header-left {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        flex: 1;
    }

    .announcement-card .card-header .icon-wrapper {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.1rem;
    }

    .icon-wrapper.important {
        background: rgba(220, 38, 38, 0.1);
        color: #dc2626;
    }

    .icon-wrapper.event {
        background: rgba(234, 179, 8, 0.1);
        color: #eab308;
    }

    .icon-wrapper.holiday {
        background: rgba(34, 197, 94, 0.1);
        color: #22c55e;
    }

    .icon-wrapper.general {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .announcement-card .title-section {
        flex: 1;
    }

    .announcement-card .title {
        font-weight: 600;
        font-size: 1rem;
        color: #1e293b;
        margin-bottom: 2px;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .badge-priority {
        font-size: 0.55rem;
        padding: 2px 10px;
        border-radius: 20px;
        font-weight: 600;
    }

    .badge-priority.high {
        background: rgba(220, 38, 38, 0.1);
        color: #dc2626;
    }

    .badge-priority.medium {
        background: rgba(234, 179, 8, 0.1);
        color: #eab308;
    }

    .badge-priority.low {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .badge-priority.new {
        background: rgba(220, 38, 38, 0.15);
        color: #dc2626;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    .announcement-card .meta {
        font-size: 0.7rem;
        color: #94a3b8;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .announcement-card .date-badge {
        font-size: 0.65rem;
        color: #94a3b8;
        text-align: right;
        line-height: 1.3;
        flex-shrink: 0;
    }

    .announcement-card .date-badge .day {
        font-size: 0.8rem;
        font-weight: 600;
        color: #1e293b;
        display: block;
    }

    .announcement-card .card-body {
        padding: 16px 20px;
    }

    .announcement-card .content {
        color: #475569;
        font-size: 0.9rem;
        line-height: 1.6;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .announcement-card .card-footer {
        padding: 10px 20px;
        background: #fafafa;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    .announcement-card .stat {
        font-size: 0.7rem;
        color: #94a3b8;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 12px;
        border: 1px solid #eef2f6;
    }

    .empty-state i {
        font-size: 4rem;
        color: #e2e8f0;
        margin-bottom: 20px;
    }

    /* Pagination */
    .pagination-wrapper {
        margin-top: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .pagination-wrapper .info {
        font-size: 0.8rem;
        color: #94a3b8;
    }

    /* Modal */
    .modal-content {
        border-radius: 16px;
        border: none;
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(135deg, #1a0000, #4a0000, #6b0000, #8b0000);
        color: white;
        border: none;
    }

    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
        padding: 25px 30px;
    }

    @media (max-width: 768px) {
        .announcement-header {
            padding: 18px 20px;
        }

        .announcement-header h2 {
            font-size: 1.3rem;
        }

        .filter-section {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-section .filter-group {
            flex-direction: column;
            align-items: stretch;
        }

        .search-box input {
            min-width: unset;
            width: 100%;
        }

        .announcement-card .card-header {
            flex-direction: column;
        }

        .announcement-card .card-footer {
            flex-direction: column;
            align-items: flex-start;
        }

        .pagination-wrapper {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
    }
</style>

<div class="announcement-container">
    <!-- Header -->
    <div class="announcement-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h2><i class="fas fa-bullhorn"></i> Announcements</h2>
                <div class="sub-info">
                    <i class="fas fa-user-graduate"></i>
                    <span>{{ $student->full_name ?? 'Student' }}</span>
                    <span class="mx-2">|</span>
                    <i class="fas fa-calendar-alt"></i>
                    <span>Stay updated with the latest news</span>
                </div>
            </div>
            <div class="header-badge mt-2 mt-sm-0">
                <i class="fas fa-bell"></i>
                <span id="unreadBadge">{{ $unreadCount ?? 0 }} Unread</span>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-section">
        <div class="filter-group">
            <label for="filterType"><i class="fas fa-sliders-h"></i> Filter:</label>
            <select id="filterType" class="form-select-sm">
                <option value="all">All</option>
                <option value="important">Important</option>
                <option value="event">Events</option>
                <option value="holiday">Holidays</option>
                <option value="general">General</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="sortBy"><i class="fas fa-sort"></i> Sort:</label>
            <select id="sortBy" class="form-select-sm">
                <option value="latest">Latest First</option>
                <option value="oldest">Oldest First</option>
                <option value="priority">By Priority</option>
            </select>
        </div>
        <div class="filter-group">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchAnnouncement" placeholder="Search..." class="form-control-sm">
            </div>
            <button class="btn-edunexus-sm" id="searchBtn">
                <i class="fas fa-search"></i> Search
            </button>
            <button class="btn-outline-sm" id="resetBtn">
                <i class="fas fa-undo"></i> Reset
            </button>
            @if($unreadCount > 0)
                <button class="btn-edunexus-sm" id="markAllReadBtn">
                    <i class="fas fa-check-double"></i> Mark All Read
                </button>
            @endif
        </div>
    </div>

    <!-- Announcements -->
    <div id="announcementList">
        @if($announcements->count() > 0)
            @foreach($announcements as $announcement)
                @php
                    $iconClass = 'general';
                    $icon = 'fa-bullhorn';
                    
                    if($announcement->type == 'important') {
                        $iconClass = 'important';
                        $icon = 'fa-exclamation-circle';
                    } elseif($announcement->type == 'event') {
                        $iconClass = 'event';
                        $icon = 'fa-calendar-check';
                    } elseif($announcement->type == 'holiday') {
                        $iconClass = 'holiday';
                        $icon = 'fa-umbrella-beach';
                    }
                    
                    $priorityLabel = 'Normal';
                    $priorityBadge = 'low';
                    
                    if($announcement->priority == 'high') {
                        $priorityLabel = 'High Priority';
                        $priorityBadge = 'high';
                    } elseif($announcement->priority == 'medium') {
                        $priorityLabel = 'Medium Priority';
                        $priorityBadge = 'medium';
                    }
                @endphp
                
                <div class="announcement-card {{ !$announcement->is_read ? 'unread' : '' }}" 
                     data-id="{{ $announcement->id }}"
                     onclick="viewAnnouncement({{ $announcement->id }})">
                    
                    <div class="card-header">
                        <div class="header-left">
                            <div class="icon-wrapper {{ $iconClass }}">
                                <i class="fas {{ $icon }}"></i>
                            </div>
                            <div class="title-section">
                                <div class="title">
                                    {{ $announcement->title }}
                                    <span class="badge-priority {{ $priorityBadge }}">
                                        <i class="fas 
                                            {{ $priorityBadge == 'high' ? 'fa-arrow-up' : ($priorityBadge == 'medium' ? 'fa-minus' : 'fa-arrow-down') }}">
                                        </i>
                                        {{ $priorityLabel }}
                                    </span>
                                    @if(!$announcement->is_read)
                                        <span class="badge-priority new">
                                            <i class="fas fa-circle"></i> New
                                        </span>
                                    @endif
                                </div>
                                <div class="meta">
                                    <span><i class="fas fa-user"></i> {{ $announcement->author->name ?? 'Admin' }}</span>
                                    <span><i class="fas fa-tag"></i> {{ ucfirst($announcement->type) }}</span>
                                    <span><i class="fas fa-clock"></i> {{ $announcement->created_at->format('h:i A') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="date-badge">
                            <span class="day">{{ $announcement->created_at->format('d') }}</span>
                            {{ $announcement->created_at->format('M Y') }}
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="content">
                            {!! Str::limit(strip_tags($announcement->content), 200) !!}
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <div class="footer-left">
                            <span class="stat">
                                <i class="fas fa-eye"></i> {{ $announcement->views ?? 0 }} views
                            </span>
                            @if($announcement->expires_at)
                                <span class="stat">
                                    <i class="fas fa-hourglass-end"></i> Expires: {{ $announcement->expires_at->format('d M Y') }}
                                </span>
                            @endif
                        </div>
                        <div>
                            <button class="btn-outline-sm" onclick="event.stopPropagation(); viewAnnouncement({{ $announcement->id }})">
                                <i class="fas fa-arrow-right"></i> Read More
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
            
            <div class="pagination-wrapper">
                <div class="info">
                    Showing {{ $announcements->firstItem() ?? 0 }} to {{ $announcements->lastItem() ?? 0 }} of {{ $announcements->total() ?? 0 }}
                </div>
                {{ $announcements->links() }}
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-bullhorn"></i>
                <h4>No Announcements</h4>
                <p class="text-muted">No announcements available. Check back later.</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="announcementModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-bullhorn me-2" style="color: #fca5a5;"></i>
                    Announcement Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-danger"></div>
                    <p class="mt-2 text-muted">Loading...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-outline-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// View announcement
function viewAnnouncement(id) {
    const modal = new bootstrap.Modal(document.getElementById('announcementModal'));
    const content = document.getElementById('modalContent');
    
    content.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-danger"></div>
            <p class="mt-2 text-muted">Loading...</p>
        </div>
    `;
    
    modal.show();
    
    fetch(`/student/announcements/${id}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Mark card as read
            const card = document.querySelector(`.announcement-card[data-id="${id}"]`);
            if (card) {
                card.classList.remove('unread');
                const newBadge = card.querySelector('.badge-priority.new');
                if (newBadge) newBadge.remove();
                updateUnreadCount();
            }
            
            content.innerHTML = `
                <h4 class="fw-bold mb-2">${data.announcement.title}</h4>
                <div class="d-flex flex-wrap gap-3 mb-3">
                    <span class="text-muted small"><i class="fas fa-user me-1"></i> ${data.announcement.author}</span>
                    <span class="text-muted small"><i class="fas fa-calendar me-1"></i> ${data.announcement.date}</span>
                    <span class="text-muted small"><i class="fas fa-clock me-1"></i> ${data.announcement.time}</span>
                    <span class="text-muted small"><i class="fas fa-tag me-1"></i> ${data.announcement.type}</span>
                    ${data.announcement.priority === 'high' ? `<span class="badge bg-danger">High Priority</span>` : ''}
                </div>
                <div class="border-top pt-3">${data.announcement.content}</div>
                ${data.announcement.expires_at ? 
                    `<div class="mt-3 text-muted small"><i class="fas fa-hourglass-end me-1"></i> Expires on ${data.announcement.expires_at}</div>` : ''}
            `;
        } else {
            content.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-circle text-danger" style="font-size: 3rem;"></i>
                    <p class="mt-2 text-muted">${data.message || 'Failed to load'}</p>
                </div>
            `;
        }
    })
    .catch(() => {
        content.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-exclamation-circle text-danger" style="font-size: 3rem;"></i>
                <p class="mt-2 text-muted">Failed to load. Please try again.</p>
            </div>
        `;
    });
}

// Update unread count
function updateUnreadCount() {
    fetch('/student/announcements/unread-count', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const badge = document.getElementById('unreadBadge');
            if (badge) {
                badge.textContent = data.count + ' Unread';
                if (data.count === 0) {
                    badge.textContent = 'No Unread';
                    const markBtn = document.getElementById('markAllReadBtn');
                    if (markBtn) markBtn.style.display = 'none';
                }
            }
        }
    })
    .catch(() => {});
}

// Mark all read
document.getElementById('markAllReadBtn')?.addEventListener('click', function() {
    if (!confirm('Mark all announcements as read?')) return;
    
    fetch('/student/announcements/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.querySelectorAll('.announcement-card.unread').forEach(card => {
                card.classList.remove('unread');
                const badge = card.querySelector('.badge-priority.new');
                if (badge) badge.remove();
            });
            updateUnreadCount();
            this.style.display = 'none';
            if (typeof toastr !== 'undefined') {
                toastr.success('All marked as read!');
            }
        }
    });
});

// Filters
document.addEventListener('DOMContentLoaded', function() {
    const filterType = document.getElementById('filterType');
    const sortBy = document.getElementById('sortBy');
    const searchInput = document.getElementById('searchAnnouncement');
    const searchBtn = document.getElementById('searchBtn');
    const resetBtn = document.getElementById('resetBtn');
    
    function applyFilters() {
        const params = new URLSearchParams();
        if (filterType.value !== 'all') params.set('type', filterType.value);
        if (sortBy.value !== 'latest') params.set('sort', sortBy.value);
        if (searchInput.value.trim()) params.set('search', searchInput.value.trim());
        
        window.location.href = window.location.pathname + '?' + params.toString();
    }
    
    filterType.addEventListener('change', applyFilters);
    sortBy.addEventListener('change', applyFilters);
    searchBtn.addEventListener('click', applyFilters);
    searchInput.addEventListener('keypress', e => { if (e.key === 'Enter') applyFilters(); });
    resetBtn.addEventListener('click', () => window.location.href = window.location.pathname);
});
</script>

@endsection