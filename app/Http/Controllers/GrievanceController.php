<?php

namespace App\Http\Controllers;

use App\Models\Grievance;
use App\Models\GrievanceCategory;
use App\Models\GrievanceComment;
use App\Models\Staff;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class GrievanceController extends Controller
{
    /**
     * Display a listing of grievances.
     */
    public function index(Request $request)
    {
        $query = Grievance::with(['staff', 'category', 'assignedTo']);

        // Filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // If staff user, show only their grievances
        if (auth()->user()->role === 'staff') {
            $query->where('staff_id', auth()->user()->staff_id);
        }

        // If admin/hr, show all or assigned to them
        if (auth()->user()->role === 'admin' || auth()->user()->role === 'hr') {
            if ($request->has('assigned_to_me') && $request->assigned_to_me) {
                $query->where('assigned_to', auth()->user()->staff_id);
            }
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('grievance_code', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $grievances = $query->orderBy('created_at', 'desc')->paginate(15);
        $categories = GrievanceCategory::active()->get();
        $statuses = [
            'draft', 'submitted', 'under_review', 'investigation',
            'resolution_proposed', 'resolved', 'closed', 'rejected', 'appealed'
        ];
        $priorities = ['low', 'medium', 'high', 'urgent'];

        return view('grievance.index', compact('grievances', 'categories', 'statuses', 'priorities'));
    }

    /**
     * Show the form for creating a new grievance.
     */
    public function create()
    {
        $categories = GrievanceCategory::active()->get();
        $staff = Staff::all();
        $departments = Department::all();
        
        return view('grievance.create', compact('categories', 'staff', 'departments'));
    }

    /**
     * Store a newly created grievance.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'nullable|exists:grievance_categories,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'is_confidential' => 'boolean',
            'is_anonymous' => 'boolean',
            'attachment' => 'nullable|file|max:10240',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Get staff_id from authenticated user
            $staffId = auth()->user()->staff_id;
            
            // If staff_id is null, try to find it
            if (empty($staffId)) {
                $staff = \App\Models\Staff::where('email', auth()->user()->email)->first();
                if ($staff) {
                    $staffId = $staff->id;
                    // Update the user's staff_id for future use
                    auth()->user()->update(['staff_id' => $staffId]);
                }
            }

            // If still null, create a staff record for this user
            if (empty($staffId)) {
                $staff = \App\Models\Staff::create([
                    'full_name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                    'position' => 'Staff Member',
                    'department' => 'General',
                    'staff_number' => 'STF' . date('Y') . rand(1000, 9999),
                ]);
                $staffId = $staff->id;
                // Update the user's staff_id
                auth()->user()->update(['staff_id' => $staffId]);
            }

            $grievance = new Grievance();
            $grievance->grievance_code = $grievance->generateGrievanceCode();
            $grievance->title = $request->title;
            $grievance->description = $request->description;
            $grievance->staff_id = $staffId;
            $grievance->category_id = $request->category_id;
            $grievance->priority = $request->priority;
            $grievance->is_confidential = $request->is_confidential ?? true;
            $grievance->is_anonymous = $request->is_anonymous ?? false;
            
            // Check if saving as draft
            if ($request->has('is_draft') && $request->is_draft == 1) {
                $grievance->status = 'draft';
            } else {
                $grievance->status = 'submitted';
                $grievance->submission_date = now();
            }

            // Handle attachments...
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $fileName = time() . '_' . \Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('grievances/attachments', $fileName, 'public');
                $grievance->attachment = $path;
            }

            if ($request->hasFile('attachments')) {
                $attachments = [];
                foreach ($request->file('attachments') as $file) {
                    $fileName = time() . '_' . \Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('grievances/attachments', $fileName, 'public');
                    $attachments[] = $path;
                }
                $grievance->attachments = $attachments;
            }

            $grievance->save();

            // Add history
            $action = ($grievance->status === 'draft') ? 'created' : 'submitted';
            $description = ($grievance->status === 'draft') ? 'Grievance saved as draft' : 'Grievance submitted by staff';
            $grievance->addHistory($action, $description, null, ['status' => $grievance->status]);

            DB::commit();

            $message = ($grievance->status === 'draft') 
                ? 'Grievance saved as draft successfully!' 
                : 'Grievance submitted successfully! You will be notified of the progress.';

            return redirect()->route('grievance.show', $grievance->id)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to submit grievance: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified grievance.
     */
    public function show($id)
    {
        $grievance = Grievance::with([
            'staff',
            'category',
            'assignedTo',
            'department',
            'comments.staff',
            'histories.performedBy',
            'escalations.fromStaff',
            'escalations.toStaff'
        ])->findOrFail($id);

        // Check permission
        if (auth()->user()->role === 'staff' && $grievance->staff_id !== auth()->user()->staff_id) {
            abort(403, 'You do not have permission to view this grievance.');
        }

        $staff = Staff::all();
        $categories = GrievanceCategory::active()->get();

        return view('grievance.show', compact('grievance', 'staff', 'categories'));
    }

    /**
     * Show the form for editing the specified grievance.
     */
    public function edit($id)
    {
        $grievance = Grievance::findOrFail($id);

        // Only allow editing if in draft or submitted status
        if (!$grievance->canEdit()) {
            return redirect()->route('grievance.show', $id)
                ->with('error', 'This grievance cannot be edited as it is already being processed.');
        }

        // Check permission
        if (auth()->user()->role === 'staff' && $grievance->staff_id !== auth()->user()->staff_id) {
            abort(403, 'You do not have permission to edit this grievance.');
        }

        $categories = GrievanceCategory::active()->get();
        $staff = Staff::all();
        $departments = Department::all();

        return view('grievance.edit', compact('grievance', 'categories', 'staff', 'departments'));
    }

    /**
     * Update the specified grievance.
     */
    public function update(Request $request, $id)
    {
        $grievance = Grievance::findOrFail($id);

        if (!$grievance->canEdit()) {
            return redirect()->route('grievance.show', $id)
                ->with('error', 'This grievance cannot be edited.');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'nullable|exists:grievance_categories,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'is_confidential' => 'boolean',
            'is_anonymous' => 'boolean',
            'attachment' => 'nullable|file|max:10240',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $oldValues = $grievance->toArray();

            // Handle attachment
            if ($request->hasFile('attachment')) {
                // Delete old attachment
                if ($grievance->attachment) {
                    Storage::disk('public')->delete($grievance->attachment);
                }
                
                $file = $request->file('attachment');
                $fileName = time() . '_' . \Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('grievances/attachments', $fileName, 'public');
                $grievance->attachment = $path;
            }

            // Handle multiple attachments
            if ($request->hasFile('attachments')) {
                // Delete old attachments
                if ($grievance->attachments) {
                    foreach ($grievance->attachments as $oldAttachment) {
                        Storage::disk('public')->delete($oldAttachment);
                    }
                }
                
                $attachments = [];
                foreach ($request->file('attachments') as $file) {
                    $fileName = time() . '_' . \Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('grievances/attachments', $fileName, 'public');
                    $attachments[] = $path;
                }
                $grievance->attachments = $attachments;
            }

            $grievance->update($request->except(['attachment', 'attachments']));

            // Add history
            $newValues = $grievance->toArray();
            $grievance->addHistory('updated', 'Grievance updated', $oldValues, $newValues);

            DB::commit();

            return redirect()->route('grievance.show', $grievance->id)
                ->with('success', 'Grievance updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update grievance: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified grievance.
     */
    public function destroy($id)
    {
        $grievance = Grievance::findOrFail($id);

        if (!$grievance->canDelete()) {
            return redirect()->back()
                ->with('error', 'This grievance cannot be deleted.');
        }

        try {
            DB::beginTransaction();

            // Delete attachments
            if ($grievance->attachment) {
                Storage::disk('public')->delete($grievance->attachment);
            }
            
            if ($grievance->attachments) {
                foreach ($grievance->attachments as $attachment) {
                    Storage::disk('public')->delete($attachment);
                }
            }

            $grievance->delete();

            DB::commit();

            return redirect()->route('grievance.index')
                ->with('success', 'Grievance deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to delete grievance: ' . $e->getMessage());
        }
    }

    /**
     * Assign grievance to a staff member.
     */
    public function assign(Request $request, $id)
    {
        $grievance = Grievance::findOrFail($id);

        if ($grievance->status === 'closed' || $grievance->status === 'resolved') {
            return redirect()->back()
                ->with('error', 'Cannot assign a closed or resolved grievance.');
        }

        $validator = Validator::make($request->all(), [
            'assigned_to' => 'required|exists:staff,id',
            'department_id' => 'nullable|exists:departments,id',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $oldAssignedTo = $grievance->assigned_to;
            $grievance->assigned_to = $request->assigned_to;
            $grievance->department_id = $request->department_id;
            $grievance->status = 'under_review';
            $grievance->review_date = now();
            $grievance->remarks = $request->remarks;
            $grievance->save();

            // Add history
            $assignedStaff = Staff::find($request->assigned_to);
            $grievance->addHistory(
                'assigned',
                "Grievance assigned to {$assignedStaff->full_name}",
                ['assigned_to' => $oldAssignedTo],
                ['assigned_to' => $request->assigned_to]
            );

            DB::commit();

            return redirect()->route('grievance.show', $grievance->id)
                ->with('success', 'Grievance assigned successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to assign grievance: ' . $e->getMessage());
        }
    }

    /**
     * Update grievance status.
     */
    public function updateStatus(Request $request, $id)
    {
        $grievance = Grievance::findOrFail($id);

        if ($grievance->status === 'closed') {
            return redirect()->back()
                ->with('error', 'Cannot update a closed grievance.');
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:under_review,investigation,resolution_proposed,resolved,closed,rejected',
            'remarks' => 'nullable|string',
            'resolution' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $oldStatus = $grievance->status;
            $newStatus = $request->status;

            $grievance->status = $newStatus;
            $grievance->remarks = $request->remarks;

            if ($newStatus === 'resolved') {
                $grievance->resolution_date = now();
            }

            if ($newStatus === 'closed') {
                $grievance->closure_date = now();
            }

            if ($newStatus === 'rejected') {
                $grievance->appeal_deadline = now()->addDays(14);
            }

            // Add resolution if provided
            if ($request->has('resolution') && $request->resolution) {
                // You might want to store resolution in a separate field or JSON
                $additionalDetails = $grievance->additional_details ?? [];
                $additionalDetails['resolution'] = $request->resolution;
                $grievance->additional_details = $additionalDetails;
            }

            $grievance->save();

            // Add history
            $grievance->addHistory(
                'updated',
                "Status changed from {$oldStatus} to {$newStatus}",
                ['status' => $oldStatus],
                ['status' => $newStatus]
            );

            DB::commit();

            return redirect()->route('grievance.show', $grievance->id)
                ->with('success', "Grievance status updated to " . ucwords(str_replace('_', ' ', $newStatus)) . "!");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }

    /**
     * Add comment to grievance.
     */
    public function addComment(Request $request, $id)
    {
        $grievance = Grievance::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'comment' => 'required|string',
            'is_internal' => 'boolean',
            'attachment' => 'nullable|file|max:10240',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $comment = new GrievanceComment();
            $comment->grievance_id = $grievance->id;
            $comment->staff_id = auth()->user()->staff_id;
            $comment->comment = $request->comment;
            $comment->is_internal = $request->is_internal ?? false;

            // Handle attachment
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $fileName = time() . '_' . \Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('grievances/comments', $fileName, 'public');
                $comment->attachment = $path;
            }

            $comment->save();

            // Add history
            $grievance->addHistory(
                'commented',
                "Comment added by " . (auth()->user()->staff->full_name ?? 'Staff'),
                null,
                ['comment' => $request->comment]
            );

            DB::commit();

            return redirect()->route('grievance.show', $grievance->id)
                ->with('success', 'Comment added successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to add comment: ' . $e->getMessage());
        }
    }

    /**
     * Escalate grievance.
     */
    public function escalate(Request $request, $id)
    {
        $grievance = Grievance::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'to_staff_id' => 'required|exists:staff,id',
            'reason' => 'required|string',
            'level' => 'required|in:level_1,level_2,level_3,level_4',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $escalation = $grievance->escalate(
                $request->to_staff_id,
                $request->reason,
                $request->level
            );

            // Update grievance assigned_to
            $grievance->assigned_to = $request->to_staff_id;
            $grievance->save();

            // Add history
            $toStaff = Staff::find($request->to_staff_id);
            $grievance->addHistory(
                'updated',
                "Grievance escalated to {$toStaff->full_name} (Level: " . ucwords(str_replace('_', ' ', $request->level)) . ")",
                null,
                ['escalation' => $escalation->toArray()]
            );

            DB::commit();

            return redirect()->route('grievance.show', $grievance->id)
                ->with('success', 'Grievance escalated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to escalate grievance: ' . $e->getMessage());
        }
    }

    /**
     * Appeal a rejected grievance.
     */
    public function appeal($id)
    {
        $grievance = Grievance::findOrFail($id);

        if (!$grievance->canAppeal()) {
            return redirect()->back()
                ->with('error', 'This grievance cannot be appealed.');
        }

        try {
            DB::beginTransaction();

            $grievance->status = 'appealed';
            $grievance->save();

            // Add history
            $grievance->addHistory('appealed', 'Grievance appealed by staff', ['status' => 'rejected'], ['status' => 'appealed']);

            DB::commit();

            return redirect()->route('grievance.show', $grievance->id)
                ->with('success', 'Grievance appealed successfully! It will be reviewed again.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to appeal grievance: ' . $e->getMessage());
        }
    }

    /**
     * Get statistics for dashboard.
     */
    public function statistics()
    {
        $stats = [
            'total' => Grievance::count(),
            'pending' => Grievance::pending()->count(),
            'resolved' => Grievance::resolved()->count(),
            'by_status' => Grievance::selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->get(),
            'by_priority' => Grievance::selectRaw('priority, count(*) as count')
                ->groupBy('priority')
                ->get(),
            'by_category' => Grievance::with('category')
                ->selectRaw('category_id, count(*) as count')
                ->groupBy('category_id')
                ->get(),
            'recent' => Grievance::with(['staff', 'category'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}