@extends('layouts.master')

@section('title', 'Live Attendance Map')

@section('content')
<div class="container-fluid py-4">

    {{-- HEADER WITH CONTROLS --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h3 class="mb-0">
                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                Live Staff Location Map
            </h3>
            <p class="text-muted mt-2 mb-0">
                <i class="fas fa-info-circle me-1"></i> 
                Real-time locations and clock-in times of staff who have clocked in today
            </p>
        </div>
        <div class="d-flex gap-2">
            <!-- <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshMap()">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="zoomToFit()">
                <i class="fas fa-expand-alt me-1"></i> Fit All
            </button> -->
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleMarkers()">
                <i class="fas fa-eye me-1"></i> Toggle Markers
            </button>
            <button type="button" class="btn btn-outline-success btn-sm" onclick="exportClockInData()">
                <i class="fas fa-download me-1"></i> Export
            </button>
        </div>
    </div>

    {{-- CLOCK-IN TIMES SUMMARY TABLE --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">
                <i class="fas fa-clock text-primary me-2"></i>
                Staff Clock-In Records - Today
                <span class="badge bg-dark ms-2">{{ count($attendances ?? []) }}</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div style="max-height: 300px; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>#</th>
                            <th>Staff</th>
                            <th>Date</th>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances ?? [] as $index => $attendance)
                            @php
                                // Parse time strings correctly
                                $clockIn = $attendance->clock_in_time ? \Carbon\Carbon::parse($attendance->clock_in_time) : null;
                                $clockOut = $attendance->clock_out_time ? \Carbon\Carbon::parse($attendance->clock_out_time) : null;
                                
                                // Format time for display (only time portion)
                                $clockInDisplay = $clockIn ? $clockIn->format('h:i A') : '--';
                                $clockOutDisplay = $clockOut ? $clockOut->format('h:i A') : '--';
                                $clockInFull = $clockIn ? $clockIn->format('Y-m-d h:i:s A') : '--';
                                
                                // Calculate duration
                                $duration = '--';
                                if($clockIn){
                                    $end = $clockOut ?? now();
                                    $hours = $clockIn->diffInHours($end);
                                    $minutes = $clockIn->copy()->addHours($hours)->diffInMinutes($end);
                                    if($hours > 0) {
                                        $duration = $hours . 'h ' . $minutes . 'm';
                                    } else {
                                        $duration = $minutes . ' minutes';
                                    }
                                }
                                
                                // Determine status
                                $status = 'Working';
                                $badge = 'success';
                                if($clockOut){
                                    $status = 'Clocked Out';
                                    $badge = 'secondary';
                                }
                            @endphp
                            <tr data-lat="{{ $attendance->clock_in_latitude }}" data-lng="{{ $attendance->clock_in_longitude }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-semibold">
                                        {{ $attendance->staff->first_name ?? 'Unknown' }} {{ $attendance->staff->last_name ?? '' }}
                                    </div>
                                </td>
                                <td>
                                <small class="text-muted">{{ $attendance->date ?? now()->format('Y, d, M') }}</small>
                                </td>
                                <td>
                                    @if($clockIn)
                                        <span class="badge bg-white text-dark px-3 py-2">
                                            <i class="fas fa-clock me-1"></i>{{ $clockInDisplay }}
                                        </span>
                                        <br>
                                        <!-- <small class="text-muted">{{ $attendance->date ?? now()->format('M d, Y') }}</small> -->
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </td>
                                <td>
                                    @if($clockOut)
                                        <span class="badge bg-white text-dark px-3 py-2">
                                            <i class="fas fa-clock me-1"></i>{{ $clockOutDisplay }}
                                        </span>
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </td>
                                <td><span class="fw-bold bg-white text-dark">{{ $duration }}</span></td>
                                <td><span class="badge bg-white text-dark">{{ $status }}</span></td>
                                <td>
                                    @if($attendance->clock_in_latitude && $attendance->clock_in_longitude)
                                        <button class="btn btn-sm btn-outline-primary" onclick="locateOnMap({{ $attendance->clock_in_latitude }}, {{ $attendance->clock_in_longitude }})">
                                            <i class="fas fa-map-marker-alt"></i> Locate
                                        </button>
                                    @else
                                        <span class="text-muted">No GPS</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-5 text-muted">No attendance records found today</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- STATS CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-white text-dark shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 opacity-75">Active Staff</h6>
                            <h2 class="mb-0 mt-1">{{ $attendances->count() ?? 0 }}</h2>
                        </div>
                        <i class="fas fa-users fa-2x opacity-50 text-primary" ></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-white text-dark shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 opacity-75">Clocked In</h6>
                            <h2 class="mb-0 mt-1">{{ $attendances->filter(function($l) { return $l->clock_in_time; })->count() ?? 0 }}</h2>
                        </div>
                        <i class="fas fa-sign-in-alt fa-2x opacity-50 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-white text-dark shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 opacity-75">Working Now</h6>
                            <h2 class="mb-0 mt-1">{{ $attendances->filter(function($l) { return $l->clock_in_time && !$l->clock_out_time; })->count() ?? 0 }}</h2>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-white text-dark shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 opacity-75">Completed</h6>
                            <h2 class="mb-0 mt-1">{{ $attendances->filter(function($l) { return $l->clock_out_time; })->count() ?? 0 }}</h2>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MAP AND STAFF LIST --}}
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-map-marked-alt text-primary me-2"></i>
                            Staff Location Map
                        </h5>
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-map-marker-alt text-primary"></i> 
                            <span id="markerCount">0</span> Staff Located
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="map" style="height: 550px; width: 100%; border-radius: 0 0 8px 8px;"></div>
                </div>
                <div class="card-footer bg-white py-2">
                    <div class="d-flex justify-content-center gap-4 flex-wrap">
                        <span><i class="fas fa-circle text-success"></i> Working</span>
                        <span><i class="fas fa-circle text-secondary"></i> Clocked Out</span>
                        <span><i class="fas fa-info-circle text-info"></i> Click marker for details</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                        <h5 class="mb-0">
                            <i class="fas fa-users text-primary me-2"></i>
                            Staff on Map
                        </h5>
                        <input type="text" id="staffSearchInput" class="form-control form-control-sm" style="width: 150px;" placeholder="Search...">
                    </div>
                </div>
                <div class="card-body p-0" style="max-height: 510px; overflow-y: auto;">
                    <div id="staffListContainer" class="list-group list-group-flush"></div>
                </div>
            </div>
        </div>
    </div>

</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    .list-group-item {
        cursor: pointer;
        transition: all 0.2s;
    }
    .list-group-item:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
    }
    .list-group-item.active {
        background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
        border-left: 3px solid #667eea;
    }
    .staff-avatar-sm {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 16px;
    }
    .status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }
    .status-working { color: #28a745; }
    .status-working .status-dot { background: #28a745; }
    .status-clockedout { color: #6c757d; }
    .status-clockedout .status-dot { background: #6c757d; }
    
    @keyframes pulse {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(40,167,69,0.7); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(40,167,69,0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(40,167,69,0); }
    }
    .pulse-marker {
        animation: pulse 2s infinite;
    }
    .toast-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        font-size: 13px;
        animation: slideIn 0.3s ease;
    }
    .toast-success { border-left: 4px solid #28a745; }
    .toast-warning { border-left: 4px solid #ffc107; }
    .toast-info { border-left: 4px solid #17a2b8; }
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
</style>

<script>
    // Map variables
    let map;
    let markers = [];
    let markerLayer;
    
    // Get today's date for combining with time strings
    const todayDate = new Date().toLocaleDateString();
    
    // Staff data from controller
    const staffLocations = @json($locations);
    
    console.log('Staff Locations:', staffLocations);
    
    // Filter staff with GPS coordinates
    const staffWithGps = staffLocations.filter(function(staff) {
        return staff.clock_in_latitude && staff.clock_in_longitude;
    });
    
    console.log('Staff with GPS:', staffWithGps.length);
    
    // Update marker count
    if (document.getElementById('markerCount')) {
        document.getElementById('markerCount').innerText = staffWithGps.length;
    }
    
    // Helper function to combine date and time string
    function combineDateTime(timeStr) {
        if (!timeStr) return null;
        // timeStr format: "15:21:42"
        return new Date(`${todayDate} ${timeStr}`);
    }
    
    // Format time for display (extract time portion only)
    function formatTime(timeStr) {
        if (!timeStr) return 'N/A';
        // timeStr format: "15:21:42" -> "03:21 PM"
        const [hours, minutes] = timeStr.split(':');
        const hour = parseInt(hours);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const hour12 = hour % 12 || 12;
        return `${hour12.toString().padStart(2, '0')}:${minutes} ${ampm}`;
    }
    
    // Calculate duration between two time strings
    function calculateDurationFromTimeStrings(clockInStr, clockOutStr) {
        if (!clockInStr) return 'N/A';
        
        const clockInDateTime = combineDateTime(clockInStr);
        const clockOutDateTime = clockOutStr ? combineDateTime(clockOutStr) : new Date();
        
        if (!clockInDateTime) return 'N/A';
        
        const diffMs = clockOutDateTime - clockInDateTime;
        const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
        const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
        
        if (diffHours > 0) {
            return `${diffHours}h ${diffMinutes}m`;
        } else {
            return `${diffMinutes} minutes`;
        }
    }
    
    // Calculate time ago from time string
    function getTimeAgo(timeStr) {
        if (!timeStr) return 'Unknown';
        
        const timeDate = combineDateTime(timeStr);
        if (!timeDate) return 'Unknown';
        
        const now = new Date();
        const diffMinutes = Math.floor((now - timeDate) / 60000);
        
        if (diffMinutes < 1) return 'Just now';
        if (diffMinutes < 60) return `${diffMinutes} min ago`;
        if (diffMinutes < 1440) return `${Math.floor(diffMinutes / 60)} hr ${diffMinutes % 60} min ago`;
        return `${Math.floor(diffMinutes / 1440)} days ago`;
    }
    
    // Initialize map
    function initializeMap() {
        const defaultCenter = [5.6037, -0.1870];
        let center = defaultCenter;
        
        if (staffWithGps.length > 0) {
            center = [
                parseFloat(staffWithGps[0].clock_in_latitude), 
                parseFloat(staffWithGps[0].clock_in_longitude)
            ];
        }
        
        map = L.map('map').setView(center, 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);
        
        markerLayer = L.layerGroup().addTo(map);
        
        updateMarkers();
        updateStaffList();
        
        setTimeout(function() { zoomToFit(); }, 500);
    }
    
    function getMarkerColor(attendance) {
        if (attendance.clock_out_time) return '#6c757d';
        return '#28a745';
    }
    
    function updateMarkers() {
        if (markerLayer) markerLayer.clearLayers();
        markers = [];
        
        let bounds = L.latLngBounds();
        
        staffWithGps.forEach(function(attendance, idx) {
            var position = [
                parseFloat(attendance.clock_in_latitude), 
                parseFloat(attendance.clock_in_longitude)
            ];
            var staffName = (attendance.staff.first_name || '') + ' ' + (attendance.staff.last_name || '');
            var color = getMarkerColor(attendance);
            var hasClockedOut = !!attendance.clock_out_time;
            
            // Format times correctly
            var clockInDisplay = formatTime(attendance.clock_in_time);
            var clockOutDisplay = formatTime(attendance.clock_out_time);
            var duration = calculateDurationFromTimeStrings(attendance.clock_in_time, attendance.clock_out_time);
            var timeAgo = getTimeAgo(attendance.clock_in_time);
            var clockInFull = attendance.clock_in_time ? `${todayDate} ${attendance.clock_in_time}` : 'N/A';
            var clockOutFull = attendance.clock_out_time ? `${todayDate} ${attendance.clock_out_time}` : 'Not clocked out';
            
            var pulseEffect = !hasClockedOut ? 'pulse-marker' : '';
            
            var markerHtml = `
                <div class="${pulseEffect}" style="
                    width: 32px;
                    height: 32px;
                    background-color: ${color};
                    border: 3px solid white;
                    border-radius: 50%;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;
                    transition: transform 0.2s;
                ">
                    <span style="color: white; font-weight: bold; font-size: 13px;">
                        ${staffName.charAt(0).toUpperCase()}
                    </span>
                </div>
            `;
            
            var marker = L.marker(position, {
                icon: L.divIcon({ html: markerHtml, className: 'custom-marker', iconSize: [32, 32], popupAnchor: [0, -16] })
            }).addTo(markerLayer);
            
            marker.bindPopup(`
                <div style="min-width: 260px;">
                    <div style="font-weight: bold; margin-bottom: 10px; border-bottom: 2px solid #007bff; padding-bottom: 5px;">
                        <i class="fas fa-user-circle"></i> ${staffName}
                    </div>
                    <div style="font-size: 13px; line-height: 1.8;">
                        <div><i class="fas fa-clock text-primary"></i> <strong>Clock In:</strong> ${clockInDisplay} (${timeAgo})</div>
                        <div><i class="fas fa-sign-out-alt text-primary"></i> <strong>Clock Out:</strong> ${clockOutDisplay}</div>
                        <div><i class="fas fa-hourglass-half text-warning"></i> <strong>Duration:</strong> ${duration}</div>
                        <div><i class="fas fa-tag"></i> <strong>Status:</strong> <span style="color: ${color};">${hasClockedOut ? 'Clocked Out' : 'Working'}</span></div>
                        <div><i class="fas fa-map-marker-alt"></i> <strong>Location:</strong><br>
                            <code>${parseFloat(attendance.clock_in_latitude).toFixed(6)}, ${parseFloat(attendance.clock_in_longitude).toFixed(6)}</code>
                        </div>
                    </div>
                </div>
            `);
            
            marker.on('click', function() {
                highlightStaffInList(idx);
            });
            
            markers.push(marker);
            bounds.extend(position);
        });
        
        if (markers.length > 0) map.fitBounds(bounds);
        if (markers.length === 1) map.setZoom(15);
    }
    
    function updateStaffList() {
        var container = document.getElementById('staffListContainer');
        if (!container) return;
        
        if (staffWithGps.length === 0) {
            container.innerHTML = '<div class="text-center py-5 text-muted"><i class="fas fa-map-marker-alt-slash fa-3x mb-3 d-block"></i>No staff location data available</div>';
            return;
        }
        
        var html = '';
        staffWithGps.forEach(function(attendance, idx) {
            var staffName = (attendance.staff.first_name || '') + ' ' + (attendance.staff.last_name || '');
            var clockInDisplay = formatTime(attendance.clock_in_time);
            var hasClockedOut = !!attendance.clock_out_time;
            var status = hasClockedOut ? 'Clocked Out' : 'Working';
            var statusClass = hasClockedOut ? 'status-clockedout' : 'status-working';
            var timeAgo = getTimeAgo(attendance.clock_in_time);
            var duration = calculateDurationFromTimeStrings(attendance.clock_in_time, attendance.clock_out_time);
            var firstLetter = staffName.charAt(0).toUpperCase();
            
            html += `
                <div class="list-group-item list-group-item-action" data-idx="${idx}" onclick="focusOnMarker(${idx})">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="staff-avatar-sm">
                                ${firstLetter}
                            </div>
                            <div>
                                <h6 class="mb-0">${escapeHtml(staffName)}</h6>
                                <small class="text-muted"><i class="fas fa-clock me-1"></i>${clockInDisplay}</small>
                                <div><small class="text-muted"><i class="fas fa-hourglass-half me-1"></i>${timeAgo}</small></div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="${statusClass}">
                                <i class="status-dot"></i> ${status}
                            </div>
                            <small class="text-muted">${duration}</small>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }
    
    function focusOnMarker(index) {
        if (markers[index]) {
            var marker = markers[index];
            var latLng = marker.getLatLng();
            map.setView(latLng, 18);
            marker.openPopup();
            highlightStaffInList(index);
        }
    }
    
    function highlightStaffInList(index) {
        var items = document.querySelectorAll('.list-group-item');
        items.forEach(function(item) {
            item.classList.remove('active');
        });
        
        if (items[index]) {
            items[index].classList.add('active');
            items[index].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        
        markers.forEach(function(marker, idx) {
            var markerElement = marker._icon;
            if (markerElement) {
                if (idx === index) {
                    markerElement.style.transform = 'scale(1.2)';
                    setTimeout(function() {
                        if (markerElement) markerElement.style.transform = 'scale(1)';
                    }, 500);
                }
            }
        });
    }
    
    function locateOnMap(lat, lng) {
        if (map && lat && lng) {
            map.setView([parseFloat(lat), parseFloat(lng)], 18);
            
            markers.forEach(function(marker, idx) {
                var markerLatLng = marker.getLatLng();
                if (Math.abs(markerLatLng.lat - parseFloat(lat)) < 0.0001 && 
                    Math.abs(markerLatLng.lng - parseFloat(lng)) < 0.0001) {
                    marker.openPopup();
                    highlightStaffInList(idx);
                }
            });
        }
    }
    
    function refreshMap() {
        location.reload();
    }
    
    function zoomToFit() {
        if (markers.length > 0) {
            var bounds = L.latLngBounds(markers.map(function(m) { return m.getLatLng(); }));
            map.fitBounds(bounds);
            if (markers.length === 1) map.setZoom(15);
            showToast('Zoomed to fit all markers', 'info');
        } else {
            showToast('No markers to fit', 'warning');
        }
    }
    
    function toggleMarkers() {
        if (markerLayer) {
            if (map.hasLayer(markerLayer)) {
                markerLayer.remove();
                showToast('Markers hidden', 'info');
            } else {
                markerLayer.addTo(map);
                showToast('Markers shown', 'info');
            }
        }
    }
    
    function exportClockInData() {
        var csvContent = "Staff Name,Clock In Time,Clock Out Time,Duration,Status,Latitude,Longitude\n";
        
        staffWithGps.forEach(function(attendance) {
            var staffName = (attendance.staff.first_name || '') + ' ' + (attendance.staff.last_name || '');
            var clockInDisplay = formatTime(attendance.clock_in_time);
            var clockOutDisplay = formatTime(attendance.clock_out_time) === 'N/A' ? 'Not clocked out' : formatTime(attendance.clock_out_time);
            var duration = calculateDurationFromTimeStrings(attendance.clock_in_time, attendance.clock_out_time);
            var status = attendance.clock_out_time ? 'Clocked Out' : 'Working';
            
            csvContent += '"' + staffName + '","' + clockInDisplay + '","' + clockOutDisplay + '","' + duration + '","' + status + '","' + attendance.clock_in_latitude + '","' + attendance.clock_in_longitude + '"\n';
        });
        
        var blob = new Blob([csvContent], { type: 'text/csv' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'clock_in_data_' + new Date().toISOString().slice(0, 19) + '.csv';
        a.click();
        URL.revokeObjectURL(url);
        
        showToast('Data exported successfully!', 'success');
    }
    
    function showToast(message, type) {
        var toast = document.createElement('div');
        toast.className = 'toast-notification toast-' + type;
        toast.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : (type === 'warning' ? 'exclamation-triangle' : 'info-circle')) + ' me-2"></i>' + message;
        document.body.appendChild(toast);
        setTimeout(function() { toast.remove(); }, 3000);
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Search functionality
    document.addEventListener('DOMContentLoaded', function() {
        var searchInput = document.getElementById('staffSearchInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                var searchTerm = this.value.toLowerCase();
                var items = document.querySelectorAll('.list-group-item');
                items.forEach(function(item) {
                    var text = item.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }
    });
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        if (staffWithGps.length > 0) {
            initializeMap();
        } else {
            var mapDiv = document.getElementById('map');
            if (mapDiv) {
                mapDiv.innerHTML = `
                    <div class="d-flex flex-column justify-content-center align-items-center h-100 bg-light">
                        <i class="fas fa-map-marker-alt-slash fa-4x text-muted mb-3"></i>
                        <h5>No GPS Location Data Available</h5>
                        <p class="text-muted text-center px-4">No staff members have GPS coordinates recorded for today's attendance.</p>
                    </div>
                `;
                mapDiv.style.display = 'flex';
                mapDiv.style.alignItems = 'center';
                mapDiv.style.justifyContent = 'center';
            }
        }
    });
</script>

@endsection