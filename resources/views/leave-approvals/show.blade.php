@extends('layouts.master')

@section('title', 'Review Leave Application')

@section('content')
<style>
    .leave-page{background:#f4f7fb;min-height:100vh;padding:24px 0 40px}.leave-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 4px 14px rgba(15,23,42,.06);overflow:hidden;margin-bottom:20px}.leave-card-header{padding:16px 20px;border-bottom:1px solid #e8edf3}.leave-card-header h5{margin:0;font-weight:800;color:#172033}.leave-card-body{padding:20px}.label{display:block;color:#64748b;font-size:.74rem;font-weight:700;text-transform:uppercase;letter-spacing:.03em;margin-bottom:5px}.value{font-weight:700;color:#172033}.reason{background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:14px;line-height:1.6}.status-banner{padding:15px 18px;border-radius:12px;margin-bottom:20px;border:1px solid}.status-banner.draft{background:#f1f5f9;border-color:#cbd5e1;color:#334155}.status-banner.pending{background:#fff8e6;border-color:#f6d365;color:#854d0e}.status-banner.approved{background:#ecfdf3;border-color:#a7f3d0;color:#166534}.status-banner.rejected{background:#fff1f2;border-color:#fecdd3;color:#991b1b}.status-badge{display:inline-flex;align-items:center;gap:6px;padding:7px 11px;border-radius:8px;font-weight:800;font-size:.82rem}.status-badge.draft{background:#e2e8f0;color:#334155}.status-badge.pending{background:#fef3c7;color:#92400e}.status-badge.approved{background:#dcfce7;color:#166534}.status-badge.rejected{background:#fee2e2;color:#991b1b}.decision-section{padding:16px;border:1px solid #e5e7eb;border-radius:12px;margin-bottom:16px}.decision-section.approve{border-left:4px solid #16a34a}.decision-section.modify{border-left:4px solid #2563eb}.decision-section.reject{border-left:4px solid #dc2626}.signature-box{height:190px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;overflow:hidden;position:relative}.signature-box canvas{width:100%;height:100%;display:block;cursor:crosshair;touch-action:none}.signature-hint{position:absolute;left:12px;bottom:8px;color:#94a3b8;font-size:.72rem;pointer-events:none}.meta{padding:10px 0;border-bottom:1px solid #eef2f7}.meta:last-child{border-bottom:0}.meta-label{font-size:.74rem;font-weight:700;text-transform:uppercase;color:#64748b}.meta-value{font-weight:700;color:#172033;margin-top:3px}.signature-preview{border:1px solid #e2e8f0;border-radius:10px;padding:12px;text-align:center;background:#fff}.signature-preview img{max-width:100%;max-height:130px;object-fit:contain}.form-control{border-radius:8px;border-color:#cbd5e1}.form-control:focus{border-color:#60a5fa;box-shadow:0 0 0 .2rem rgba(37,99,235,.12)}
</style>

<div class="leave-page">
<div class="container-fluid">

<div class="leave-card">
    <div class="leave-card-body d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-file-signature text-primary me-2"></i>Review Leave Application</h4>
            <div class="text-muted">Review the application before making an approval decision.</div>
        </div>
        <a href="{{ route('leave-approvals.index') }}" class="btn btn-secondary mt-2 mt-md-0"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

@if(session('success'))<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}</div>@endif
@if($errors->any())
<div class="alert alert-danger"><strong>Please correct the following:</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

@php($status = $leave->status ?? 'draft')
<div class="status-banner {{ $status }}">
    <div class="d-flex align-items-center">
        <div class="me-3" style="font-size:1.7rem">
            @if($status==='draft')<i class="fas fa-file-alt"></i>@elseif($status==='pending')<i class="fas fa-clock"></i>@elseif($status==='approved')<i class="fas fa-check-circle"></i>@elseif($status==='rejected')<i class="fas fa-times-circle"></i>@else<i class="fas fa-info-circle"></i>@endif
        </div>
        <div>
            <strong>
                @if($status==='draft')Draft Application
                @elseif($status==='pending')Awaiting Approval
                @elseif($status==='approved')Leave Approved
                @elseif($status==='rejected')Leave Rejected
                @else{{ ucfirst($status) }}@endif
            </strong>
            <div class="small">
                @if($status==='draft')This application has not yet been submitted for approval.
                @elseif($status==='pending')This application requires an approval decision.
                @elseif($status==='approved')This application has already been approved.
                @elseif($status==='rejected')This application has already been rejected.
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
<div class="col-lg-8">

<div class="leave-card"><div class="leave-card-header"><h5><i class="fas fa-user text-primary me-2"></i>Employee Information</h5></div><div class="leave-card-body"><div class="row">
<div class="col-md-6 mb-4"><span class="label">Full Name</span><div class="value">{{ $leave->full_name ?: 'N/A' }}</div></div>
<div class="col-md-6 mb-4"><span class="label">Designation</span><div class="value">{{ $leave->designation ?: 'N/A' }}</div></div>
<div class="col-md-6"><span class="label">Contact Number</span><div class="value">{{ $leave->contact_number ?: 'N/A' }}</div></div>
<div class="col-md-6"><span class="label">Leave Type</span><div class="value">{{ $leave->leave_type ?: 'N/A' }}</div></div>
</div></div></div>

<div class="leave-card"><div class="leave-card-header"><h5><i class="fas fa-calendar-alt text-primary me-2"></i>Leave Details</h5></div><div class="leave-card-body"><div class="row">
<div class="col-md-4 mb-4"><span class="label">Date of Application</span><div class="value">{{ $leave->date_of_application?->format('d M Y') ?? 'N/A' }}</div></div>
<div class="col-md-4 mb-4"><span class="label">Commencement Date</span><div class="value">{{ $leave->date_commencement?->format('d M Y') ?? 'N/A' }}</div></div>
<div class="col-md-4 mb-4"><span class="label">Resumption Date</span><div class="value">{{ $leave->date_resumption?->format('d M Y') ?? 'N/A' }}</div></div>
<div class="col-md-4 mb-4"><span class="label">Days Entitled</span><div class="value">{{ $leave->days_entitled ?? 0 }}</div></div>
<div class="col-md-4 mb-4"><span class="label">Days Already Utilized</span><div class="value">{{ $leave->days_already_utilized ?? 0 }}</div></div>
<div class="col-md-4 mb-4"><span class="label">Days Applied</span><div class="value text-primary fs-5">{{ $leave->days_applied_for ?? 0 }}</div></div>
<div class="col-12"><span class="label">Reason for Leave</span><div class="reason">{!! nl2br(e($leave->reason ?: 'No reason provided.')) !!}</div></div>
@if($leave->date_last_leave)<div class="col-md-4 mt-4"><span class="label">Date of Last Leave</span><div class="value">{{ $leave->date_last_leave->format('d M Y') }}</div></div>@endif
</div></div></div>

<div class="leave-card"><div class="leave-card-header"><h5><i class="fas fa-signature text-primary me-2"></i>Applicant Signature</h5></div><div class="leave-card-body">
@if($leave->signature)<div class="signature-preview"><img src="{{ $leave->signature }}" alt="Applicant Signature"></div>@else<div class="text-muted"><i class="fas fa-info-circle me-1"></i>No applicant signature provided.</div>@endif
</div></div>

</div>

<div class="col-lg-4">
@if($status==='pending')
<div class="leave-card"><div class="leave-card-header"><h5><i class="fas fa-gavel text-primary me-2"></i>Approval Decision</h5></div><div class="leave-card-body">
<div class="alert alert-info small"><strong>Decision required.</strong> Approve the requested days, approve fewer days, or reject the application.</div>

<div class="decision-section approve">
<form method="POST" action="{{ route('leave-approvals.approve', $leave) }}" id="approveForm">@csrf
<label class="form-label fw-bold" for="approve_days_granted">Days to Grant <span class="text-danger">*</span></label>
<input type="number" id="approve_days_granted" name="days_granted" class="form-control mb-2" min="1" max="{{ max(1,(int)$leave->days_applied_for) }}" value="{{ old('days_granted',$leave->days_applied_for) }}" required>
<textarea name="recommendation" class="form-control mb-3" rows="3" placeholder="Approval comments...">{{ old('recommendation') }}</textarea>
<input type="hidden" name="administrator_signature" id="administrator_signature">
<button type="submit" class="btn btn-success w-100" id="approveButton"><i class="fas fa-check-circle me-1"></i>Approve Leave</button>
</form></div>

<div class="decision-section modify">
<form method="POST" action="{{ route('leave-approvals.modify-approve', $leave) }}" id="modifyApproveForm">@csrf
<label class="form-label fw-bold" for="modify_days_granted">Reduced Days</label>
<input type="number" id="modify_days_granted" name="days_granted" class="form-control mb-2" min="1" max="{{ max(1,(int)$leave->days_applied_for-1) }}" value="{{ old('days_granted') }}" placeholder="Fewer than requested">
<textarea name="recommendation" id="modify_recommendation" class="form-control mb-3" rows="3" placeholder="Reason for reduction...">{{ old('recommendation') }}</textarea>
<input type="hidden" name="administrator_signature" id="modifyAdministratorSignature">
<button type="submit" class="btn btn-outline-primary w-100" id="modifyApproveButton"><i class="fas fa-edit me-1"></i>Modify &amp; Approve</button>
</form></div>

<div class="decision-section reject">
<form method="POST" action="{{ route('leave-approvals.reject', $leave) }}" id="rejectForm">@csrf
<label class="form-label fw-bold" for="reject_recommendation">Reason for Rejection <span class="text-danger">*</span></label>
<textarea name="recommendation" id="reject_recommendation" class="form-control mb-3" rows="3" placeholder="Reason for rejection..." required></textarea>
<input type="hidden" name="administrator_signature" id="rejectAdministratorSignature">
<button type="submit" class="btn btn-outline-danger w-100" id="rejectButton"><i class="fas fa-times-circle me-1"></i>Reject Leave</button>
</form></div>

<div class="decision-section">
<label class="form-label fw-bold">Your Signature <span class="text-danger">*</span></label>
<div class="signature-box" id="signaturePad"><canvas id="signatureCanvas"></canvas><span class="signature-hint">Sign inside this box</span></div>
<button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="clearSignature"><i class="fas fa-eraser me-1"></i>Clear Signature</button>
</div>
</div></div>

@elseif($status==='draft')
<div class="leave-card"><div class="leave-card-header"><h5><i class="fas fa-file-alt text-primary me-2"></i>Application Status</h5></div><div class="leave-card-body"><span class="status-badge draft"><i class="fas fa-file-alt"></i>Draft</span><p class="text-muted mt-3 mb-0">This application has not been submitted for approval. Approve and reject actions are unavailable until it is submitted.</p></div></div>

@else
<div class="leave-card"><div class="leave-card-header"><h5><i class="fas fa-clipboard-check text-primary me-2"></i>Decision</h5></div><div class="leave-card-body">
<div class="meta"><div class="meta-label">Status</div><div class="meta-value">
@if($status==='approved')<span class="status-badge approved"><i class="fas fa-check-circle"></i>Approved</span>
@elseif($status==='rejected')<span class="status-badge rejected"><i class="fas fa-times-circle"></i>Rejected</span>
@else<span class="status-badge draft">{{ ucfirst($status) }}</span>@endif
</div></div>
@if($leave->days_granted!==null)<div class="meta"><div class="meta-label">Days Granted</div><div class="meta-value fs-4">{{ $leave->days_granted }}</div></div>@endif
@if($leave->administrator_name)<div class="meta"><div class="meta-label">Decision By</div><div class="meta-value">{{ $leave->administrator_name }}</div></div>@endif
@if($leave->administrator_date)<div class="meta"><div class="meta-label">Decision Date</div><div class="meta-value">{{ $leave->administrator_date->format('d M Y') }}</div></div>@endif
@if($leave->recommendation)<div class="meta"><div class="meta-label">Recommendation / Comment</div><div class="reason mt-2">{!! nl2br(e($leave->recommendation)) !!}</div></div>@endif
@if($leave->administrator_signature)<div class="mt-3"><div class="meta-label mb-2">Administrator Signature</div><div class="signature-preview"><img src="{{ $leave->administrator_signature }}" alt="Administrator Signature"></div></div>@endif
</div></div>
@endif
</div></div>
</div>
</div>
@endsection

@if(($leave->status ?? null)==='pending')
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',function(){
    const canvas=document.getElementById('signatureCanvas'),pad=document.getElementById('signaturePad');
    if(!canvas||!pad)return;
    const ctx=canvas.getContext('2d');let drawing=false,hasSignature=false,lastX=0,lastY=0;
    function resize(){const r=pad.getBoundingClientRect();if(!r.width||!r.height)return;const dpr=Math.max(window.devicePixelRatio||1,1);canvas.width=Math.round(r.width*dpr);canvas.height=Math.round(r.height*dpr);canvas.style.width=r.width+'px';canvas.style.height=r.height+'px';ctx.setTransform(dpr,0,0,dpr,0,0);ctx.lineWidth=2;ctx.lineCap='round';ctx.lineJoin='round';ctx.strokeStyle='#111827';}
    function point(e){const r=canvas.getBoundingClientRect(),t=e.touches&&e.touches[0];return{x:(t?t.clientX:e.clientX)-r.left,y:(t?t.clientY:e.clientY)-r.top};}
    function start(e){e.preventDefault();const p=point(e);drawing=true;lastX=p.x;lastY=p.y;ctx.beginPath();ctx.arc(p.x,p.y,.8,0,Math.PI*2);ctx.fill();hasSignature=true;}
    function move(e){if(!drawing)return;e.preventDefault();const p=point(e);ctx.beginPath();ctx.moveTo(lastX,lastY);ctx.lineTo(p.x,p.y);ctx.stroke();lastX=p.x;lastY=p.y;hasSignature=true;}
    function stop(e){if(e)e.preventDefault();drawing=false;}
    function copy(){if(!hasSignature)return false;const data=canvas.toDataURL('image/png');['administrator_signature','modifyAdministratorSignature','rejectAdministratorSignature'].forEach(id=>{const el=document.getElementById(id);if(el)el.value=data;});return true;}
    canvas.addEventListener('mousedown',start);canvas.addEventListener('mousemove',move);canvas.addEventListener('mouseup',stop);canvas.addEventListener('mouseleave',stop);canvas.addEventListener('touchstart',start,{passive:false});canvas.addEventListener('touchmove',move,{passive:false});canvas.addEventListener('touchend',stop,{passive:false});canvas.addEventListener('touchcancel',stop,{passive:false});resize();window.addEventListener('resize',resize);
    document.getElementById('clearSignature')?.addEventListener('click',()=>{const dpr=Math.max(window.devicePixelRatio||1,1);ctx.clearRect(0,0,canvas.width/dpr,canvas.height/dpr);hasSignature=false;['administrator_signature','modifyAdministratorSignature','rejectAdministratorSignature'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});});
    const approve=document.getElementById('approveForm');approve?.addEventListener('submit',e=>{if(!copy()){e.preventDefault();alert('Please provide your signature before approving the leave.');return;}const days=document.getElementById('approve_days_granted');if(!days.value||Number(days.value)<1){e.preventDefault();alert('Please enter the number of days to grant.');return;}document.getElementById('approveButton').disabled=true;});
    const modify=document.getElementById('modifyApproveForm');modify?.addEventListener('submit',e=>{if(!copy()){e.preventDefault();alert('Please provide your signature before approving the leave.');return;}const days=Number(document.getElementById('modify_days_granted').value),requested={{ (int)($leave->days_applied_for??0) }},reason=document.getElementById('modify_recommendation').value.trim();if(!days||days<1){e.preventDefault();alert('Please enter the reduced number of days.');return;}if(requested&&days>=requested){e.preventDefault();alert('Reduced days must be fewer than the requested days.');return;}if(!reason){e.preventDefault();alert('Please provide a reason for reducing the leave.');return;}if(!confirm('Approve this leave with the reduced number of days?')){e.preventDefault();return;}document.getElementById('modifyApproveButton').disabled=true;});
    const reject=document.getElementById('rejectForm');reject?.addEventListener('submit',e=>{if(!copy()){e.preventDefault();alert('Please provide your signature before rejecting the leave.');return;}const reason=document.getElementById('reject_recommendation').value.trim();if(!reason){e.preventDefault();alert('Please enter the reason for rejection.');return;}if(!confirm('Are you sure you want to reject this leave application?')){e.preventDefault();return;}document.getElementById('rejectButton').disabled=true;});
});
</script>
@endpush
@endif
