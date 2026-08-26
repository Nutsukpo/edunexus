<?php

namespace App\Http\Controllers;

use App\Models\LessonNote;
use App\Models\Staff;
use App\Models\StudentClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LessonNoteApprovalController extends Controller
{
    /**
     * Display a listing of ALL lesson notes with approval actions.
     */
    public function index(Request $request)
    {
        $query = LessonNote::with(['staff', 'studentClass', 'subject', 'academicYear', 'term']);

        // Filters
        if ($request->has('staff_id') && $request->staff_id) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->has('student_class_id') && $request->student_class_id) {
            $query->where('student_class_id', $request->student_class_id);
        }

        if ($request->has('subject_id') && $request->subject_id) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('topic', 'LIKE', "%{$search}%")
                    ->orWhere('sub_topic', 'LIKE', "%{$search}%")
                    ->orWhere('note_code', 'LIKE', "%{$search}%")
                    ->orWhere('content', 'LIKE', "%{$search}%");
            });
        }

        $perPage = $request->per_page ?? 15;
        $lessonNotes = $query->orderBy('created_at', 'desc')
                            ->orderBy('lesson_date', 'desc')
                            ->paginate($perPage);

        $staffs = Staff::all();
        $classes = StudentClass::all();
        $subjects = Subject::all();
        
        $stats = [
            'all' => LessonNote::count(),
            'pending' => LessonNote::where('status', 'pending')->count(),
            'draft' => LessonNote::where('status', 'draft')->count(),
            'approved' => LessonNote::where('status', 'approved')->count(),
            'rejected' => LessonNote::where('status', 'rejected')->count(),
        ];

        return view('approvals.index', compact(
            'lessonNotes', 
            'staffs', 
            'classes', 
            'subjects',
            'stats'
        ));
    }

    /**
     * Show a specific lesson note with approval actions.
     */
    public function show($id)
    {
        $lessonNote = LessonNote::with([
            'staff', 
            'studentClass', 
            'subject', 
            'academicYear', 
            'term', 
            'commentedBy'
        ])->findOrFail($id);

        return view('approvals.show', compact('lessonNote'));
    }

    /**
     * Approve a lesson note.
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $lessonNote = LessonNote::findOrFail($id);

            if ($lessonNote->status === 'approved') {
                return redirect()->back()
                    ->with('warning', 'This lesson note has already been approved.');
            }

            $lessonNote->status = 'approved';
            $lessonNote->approved_at = now();
            $lessonNote->approved_by = auth()->id() ?? null;

            $comments = $lessonNote->comments ?? [];
            $comments[] = [
                'type' => 'approval',
                'comment' => $request->approval_notes ?? 'Lesson note approved.',
                'commented_by' => auth()->id(),
                'commented_at' => now()->toDateTimeString(),
                'status' => 'approved'
            ];
            $lessonNote->comments = $comments;

            if ($request->approval_notes) {
                $lessonNote->comment = $request->approval_notes;
                $lessonNote->commented_by = auth()->id();
            }

            $lessonNote->save();

            DB::commit();

            return redirect()->route('approvals.index')
                ->with('success', 'Lesson note approved successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to approve lesson note: ' . $e->getMessage());
        }
    }

    /**
     * Reject a lesson note.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $lessonNote = LessonNote::findOrFail($id);

            if ($lessonNote->status === 'approved') {
                return redirect()->back()
                    ->with('warning', 'Cannot reject an already approved lesson note.');
            }

            $lessonNote->status = 'rejected';
            $lessonNote->rejected_at = now();
            $lessonNote->rejected_by = auth()->id() ?? null;

            $comments = $lessonNote->comments ?? [];
            $comments[] = [
                'type' => 'rejection',
                'comment' => $request->rejection_reason,
                'commented_by' => auth()->id(),
                'commented_at' => now()->toDateTimeString(),
                'status' => 'rejected'
            ];
            $lessonNote->comments = $comments;

            $lessonNote->comment = $request->rejection_reason;
            $lessonNote->commented_by = auth()->id();

            $lessonNote->save();

            DB::commit();

            return redirect()->route('approvals.index')
                ->with('success', 'Lesson note rejected successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to reject lesson note: ' . $e->getMessage());
        }
    }

    /**
     * Request changes for a lesson note.
     */
    public function requestChanges(Request $request, $id)
    {
        $request->validate([
            'feedback' => 'required|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $lessonNote = LessonNote::findOrFail($id);

            if ($lessonNote->status === 'approved') {
                return redirect()->back()
                    ->with('warning', 'Cannot request changes for an already approved lesson note.');
            }

            $lessonNote->status = 'draft';
            $lessonNote->feedback_requested_at = now();
            $lessonNote->feedback_requested_by = auth()->id() ?? null;

            $comments = $lessonNote->comments ?? [];
            $comments[] = [
                'type' => 'feedback_request',
                'comment' => $request->feedback,
                'commented_by' => auth()->id(),
                'commented_at' => now()->toDateTimeString(),
                'status' => 'draft'
            ];
            $lessonNote->comments = $comments;

            $lessonNote->comment = $request->feedback;
            $lessonNote->commented_by = auth()->id();

            $lessonNote->save();

            DB::commit();

            return redirect()->route('approvals.index')
                ->with('success', 'Feedback sent successfully. Lesson note status changed to draft.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to request changes: ' . $e->getMessage());
        }
    }
}