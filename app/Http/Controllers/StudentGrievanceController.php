<?php

namespace App\Http\Controllers;

use App\Models\StudentGrievance;
use App\Models\StudentGrievanceCategory;
use App\Models\StudentGrievanceComment;
use App\Models\Student;
use App\Models\Staff;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class StudentGrievanceController extends Controller
{
    /**
     * Display a listing of grievances.
     */
    public function index(Request $request)
    {
        $query = StudentGrievance::with(['student', 'category', 'assignedTo']);

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

        // If student user, show only their grievances
     

        // If admin/staff, show all or assigned to them
       

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('grievance_code', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $grievances = $query->orderBy('created_at', 'desc')->paginate(15);
        
        $categories = StudentGrievanceCategory::active()->get();
        $statuses = [
            'draft', 'submitted', 'under_review', 'investigation',
            'resolution_proposed', 'resolved', 'closed', 'rejected', 'appealed'
        ];
        $priorities = ['low', 'medium', 'high', 'urgent'];

        return view('student-grievance.index', compact('grievances', 'categories', 'statuses', 'priorities'));
    }

    /**
     * Show the form for creating a new grievance.
     */
    public function create()
    {
        $categories = StudentGrievanceCategory::active()->get();
        $students = Student::all();
        $staff = Staff::all();
        $classes = StudentClass::all(); // FIX: Changed from Classes::all() to StudentClass::all()
        
        return view('student-grievance.create', compact('categories', 'students', 'staff', 'classes'));
    }

    /**
     * Store a newly created grievance.
     */
   /**
 * Store a newly created grievance.
 */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'nullable|exists:students_grievance_categories,id',
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

            // Get student_id from authenticated user
            $studentId = auth()->user()->student_id;
            
            // If student_id is not set, try to find it by checking the students table
            if (empty($studentId)) {
                // Check if the user has a student record
                $student = Student::where('user_id', auth()->id())->first();
                
                // If not found by user_id, try to find by email
                if (!$student) {
                    // Check if students table has email column
                    try {
                        $student = Student::where('email', auth()->user()->email)->first();
                    } catch (\Exception $e) {
                        // If email column doesn't exist, try other columns
                        $student = Student::where('user_id', auth()->id())->first();
                    }
                }
                
                if ($student) {
                    $studentId = $student->id;
                    // Update the user's student_id for future use
                    auth()->user()->update(['student_id' => $studentId]);
                } else {
                    // If no student record exists, you might want to create one or throw an error
                    throw new \Exception('Student record not found. Please contact administrator.');
                }
            }

            $grievance = new StudentGrievance();
            $grievance->grievance_code = $grievance->generateGrievanceCode();
            $grievance->title = $request->title;
            $grievance->description = $request->description;
            $grievance->student_id = $studentId;
            $grievance->category_id = $request->category_id;
            $grievance->class_id = $request->class_id;
            $grievance->priority = $request->priority;
            $grievance->is_confidential = $request->is_confidential ?? true;
            $grievance->is_anonymous = $request->is_anonymous ?? false;
            
            if ($request->has('is_draft') && $request->is_draft == 1) {
                $grievance->status = 'draft';
            } else {
                $grievance->status = 'submitted';
                $grievance->submission_date = now();
            }

            // Handle attachments
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $fileName = time() . '_' . \Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('student-grievances/attachments', $fileName, 'public');
                $grievance->attachment = $path;
            }

            if ($request->hasFile('attachments')) {
                $attachments = [];
                foreach ($request->file('attachments') as $file) {
                    $fileName = time() . '_' . \Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('student-grievances/attachments', $fileName, 'public');
                    $attachments[] = $path;
                }
                $grievance->attachments = $attachments;
            }

            $grievance->save();

            // Add history
            $action = ($grievance->status === 'draft') ? 'created' : 'submitted';
            $description = ($grievance->status === 'draft') ? 'Grievance saved as draft' : 'Grievance submitted by student';
            $grievance->addHistory($action, $description, null, ['status' => $grievance->status], 'student');

            DB::commit();

            $message = ($grievance->status === 'draft') 
                ? 'Grievance saved as draft successfully!' 
                : 'Grievance submitted successfully! You will be notified of the progress.';

            return redirect()->route('student-grievance.show', $grievance->id)
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
            $grievance = StudentGrievance::with([
                'student',
                'category',
                'assignedTo',
                'class',
                'comments.staff',
                'comments.student',
                'histories',
                'escalations.fromStaff',
                'escalations.toStaff'
            ])->findOrFail($id);

            // Check permission
            if (auth()->user()->role === 'student' && $grievance->student_id !== auth()->user()->student_id) {
                abort(403, 'You do not have permission to view this grievance.');
            }

            $staff = Staff::all();
            $categories = StudentGrievanceCategory::active()->get();
            $students = Student::all();

            return view('student-grievance.show', compact('grievance', 'staff', 'categories', 'students'));
        }

    /**
     * Show the form for editing the specified grievance.
     */
    public function edit($id)
    {
        $grievance = StudentGrievance::findOrFail($id);

        if (!$grievance->canEdit()) {
            return redirect()->route('student-grievance.show', $id)
                ->with('error', 'This grievance cannot be edited as it is already being processed.');
        }

        if (auth()->user()->role === 'student' && $grievance->student_id !== auth()->user()->student_id) {
            abort(403, 'You do not have permission to edit this grievance.');
        }

        $categories = StudentGrievanceCategory::active()->get();
        $students = Student::all();
        $staff = Staff::all();
        $classes = StudentClass::all(); // FIX: Changed from Classes::all() to StudentClass::all()

        return view('student-grievance.edit', compact('grievance', 'categories', 'students', 'staff', 'classes'));
    }

    /**
     * Update the specified grievance.
     */
    public function update(Request $request, $id)
    {
        $grievance = StudentGrievance::findOrFail($id);

        if (!$grievance->canEdit()) {
            return redirect()->route('student-grievance.show', $id)
                ->with('error', 'This grievance cannot be edited.');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'nullable|exists:students_grievance_categories,id',
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

            // Handle attachments
            if ($request->hasFile('attachment')) {
                if ($grievance->attachment) {
                    Storage::disk('public')->delete($grievance->attachment);
                }
                $file = $request->file('attachment');
                $fileName = time() . '_' . \Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('student-grievances/attachments', $fileName, 'public');
                $grievance->attachment = $path;
            }

            if ($request->hasFile('attachments')) {
                if ($grievance->attachments) {
                    foreach ($grievance->attachments as $oldAttachment) {
                        Storage::disk('public')->delete($oldAttachment);
                    }
                }
                $attachments = [];
                foreach ($request->file('attachments') as $file) {
                    $fileName = time() . '_' . \Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('student-grievances/attachments', $fileName, 'public');
                    $attachments[] = $path;
                }
                $grievance->attachments = $attachments;
            }

            $grievance->update($request->except(['attachment', 'attachments']));

            // Add history
            $newValues = $grievance->toArray();
            $grievance->addHistory('updated', 'Grievance updated', $oldValues, $newValues, 'student');

            DB::commit();

            return redirect()->route('student-grievance.show', $grievance->id)
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
        $grievance = StudentGrievance::findOrFail($id);

        if (!$grievance->canDelete()) {
            return redirect()->back()
                ->with('error', 'This grievance cannot be deleted.');
        }

        try {
            DB::beginTransaction();

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

            return redirect()->route('student-grievance.index')
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
        $grievance = StudentGrievance::findOrFail($id);

        if ($grievance->status === 'closed' || $grievance->status === 'resolved') {
            return redirect()->back()
                ->with('error', 'Cannot assign a closed or resolved grievance.');
        }

        $validator = Validator::make($request->all(), [
            'assigned_to' => 'required|exists:staff,id',
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
            $grievance->status = 'under_review';
            $grievance->review_date = now();
            $grievance->remarks = $request->remarks;
            $grievance->save();

            $assignedStaff = Staff::find($request->assigned_to);
            $grievance->addHistory(
                'assigned',
                "Grievance assigned to {$assignedStaff->full_name}",
                ['assigned_to' => $oldAssignedTo],
                ['assigned_to' => $request->assigned_to],
                'staff'
            );

            DB::commit();

            return redirect()->route('student-grievance.show', $grievance->id)
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
        $grievance = StudentGrievance::findOrFail($id);

        if ($grievance->status === 'closed') {
            return redirect()->back()
                ->with('error', 'Cannot update a closed grievance.');
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:under_review,investigation,resolution_proposed,resolved,closed,rejected',
            'remarks' => 'nullable|string',
            'staff_response' => 'nullable|string',
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
            $grievance->staff_response = $request->staff_response;

            if ($newStatus === 'resolved') {
                $grievance->resolution_date = now();
            }

            if ($newStatus === 'closed') {
                $grievance->closure_date = now();
            }

            if ($newStatus === 'rejected') {
                $grievance->appeal_deadline = now()->addDays(14);
            }

            $grievance->save();

            $grievance->addHistory(
                'updated',
                "Status changed from {$oldStatus} to {$newStatus}",
                ['status' => $oldStatus],
                ['status' => $newStatus],
                'staff'
            );

            DB::commit();

            return redirect()->route('student-grievance.show', $grievance->id)
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
        $grievance = StudentGrievance::findOrFail($id);

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

            $comment = new StudentGrievanceComment();
            $comment->grievance_id = $grievance->id;
            $comment->comment = $request->comment;
            $comment->is_internal = $request->is_internal ?? false;

            // Determine who is commenting
            if (auth()->user()->role === 'student') {
                $comment->student_id = auth()->user()->student_id;
                $performerType = 'student';
            } else {
                $comment->staff_id = auth()->user()->staff_id;
                $performerType = 'staff';
            }

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $fileName = time() . '_' . \Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('student-grievances/comments', $fileName, 'public');
                $comment->attachment = $path;
            }

            $comment->save();

            $grievance->addHistory(
                'commented',
                "Comment added by " . (auth()->user()->role === 'student' ? 'Student' : 'Staff'),
                null,
                ['comment' => $request->comment],
                $performerType
            );

            DB::commit();

            return redirect()->route('student-grievance.show', $grievance->id)
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
        $grievance = StudentGrievance::findOrFail($id);

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

            $escalation = $grievance->escalations()->create([
                'from_staff_id' => $grievance->assigned_to,
                'to_staff_id' => $request->to_staff_id,
                'reason' => $request->reason,
                'level' => $request->level,
                'escalation_date' => now(),
                'response_deadline' => now()->addDays(7),
                'status' => 'pending',
            ]);

            $grievance->assigned_to = $request->to_staff_id;
            $grievance->save();

            $toStaff = Staff::find($request->to_staff_id);
            $grievance->addHistory(
                'updated',
                "Grievance escalated to {$toStaff->full_name} (Level: " . ucwords(str_replace('_', ' ', $request->level)) . ")",
                null,
                ['escalation' => $escalation->toArray()],
                'staff'
            );

            DB::commit();

            return redirect()->route('student-grievance.show', $grievance->id)
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
        $grievance = StudentGrievance::findOrFail($id);

        if (!$grievance->canAppeal()) {
            return redirect()->back()
                ->with('error', 'This grievance cannot be appealed.');
        }

        try {
            DB::beginTransaction();

            $grievance->status = 'appealed';
            $grievance->save();

            $grievance->addHistory('appealed', 'Grievance appealed by student', ['status' => 'rejected'], ['status' => 'appealed'], 'student');

            DB::commit();

            return redirect()->route('student-grievance.show', $grievance->id)
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
            'total' => StudentGrievance::count(),
            'pending' => StudentGrievance::pending()->count(),
            'resolved' => StudentGrievance::whereIn('status', ['resolved', 'closed'])->count(),
            'by_status' => StudentGrievance::selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->get(),
            'by_priority' => StudentGrievance::selectRaw('priority, count(*) as count')
                ->groupBy('priority')
                ->get(),
            'by_category' => StudentGrievance::with('category')
                ->selectRaw('category_id, count(*) as count')
                ->groupBy('category_id')
                ->get(),
            'recent' => StudentGrievance::with(['student', 'category'])
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