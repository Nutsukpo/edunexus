<?php

namespace App\Http\Controllers;

use App\Models\LessonNote;
use App\Models\Staff;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\EdunexusAuthorizationService;

class LessonNoteController extends Controller
{
    public function __construct(private EdunexusAuthorizationService $authorization)
    {
    }

    /**
     * Display a listing of lesson notes.
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('lesson-notes.view'), 403);

        $query = LessonNote::with(['staff', 'studentClass', 'subject', 'academicYear', 'term']);

        if ($this->authorization->isScopedStaff(auth()->user())) {
            $staff = $this->authorization->staffFor(auth()->user());
            abort_if(!$staff, 403);

            $query->where('staff_id', $staff->id);
        }

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
        $lessonNotes = $query->orderBy('lesson_date', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->paginate($perPage);

        // Get data for filters
        $staffs = $this->authorization->isScopedStaff(auth()->user())
            ? collect([$this->authorization->staffFor(auth()->user())])->filter()
            : Staff::all();

        $classes = $this->authorization->accessibleClassSubjects(auth()->user())->orderBy('name')->get();
        $subjects = $this->authorization->isScopedStaff(auth()->user())
            ? Subject::whereHas('classSubjectStaff', function ($q) {
                $q->where('staff_id', $this->authorization->staffFor(auth()->user())->id);
            })->orderBy('name')->get()
            : Subject::all();

        return view('lesson-notes.index', compact('lessonNotes', 'staffs', 'classes', 'subjects'));
    }

    /**
     * Show the form for creating a new lesson note.
     */
    public function create()
    {
        abort_unless(auth()->user()->can('lesson-notes.create'), 403);

        $staffs = $this->authorization->isScopedStaff(auth()->user())
            ? collect([$this->authorization->staffFor(auth()->user())])->filter()
            : Staff::all();

        $classes = $this->authorization->accessibleClassSubjects(auth()->user())->orderBy('name')->get();
        $subjects = $this->authorization->isScopedStaff(auth()->user())
            ? Subject::whereHas('classSubjectStaff', function ($q) {
                $q->where('staff_id', $this->authorization->staffFor(auth()->user())->id);
            })->orderBy('name')->get()
            : Subject::all();
        $academicYears = AcademicYear::all();
        $terms = Term::all();
        
        return view('lesson-notes.create', compact('staffs', 'classes', 'subjects', 'academicYears', 'terms'));
    }

    /**
     * Store a newly created lesson note.
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('lesson-notes.create'), 403);

        abort_unless(auth()->user()->can('lesson-notes.edit'), 403);

        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'student_class_id' => 'required|exists:student_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'topic' => 'required|string|max:255',
            'content' => 'required|string',
            'lesson_date' => 'required|date',
            'type' => 'required|in:daily,weekly,monthly,termly',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'attachment' => 'nullable|file|max:10240',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        if ($this->authorization->isScopedStaff(auth()->user())) {
            $staff = $this->authorization->staffFor(auth()->user());

            abort_unless($staff && (int) $request->staff_id === (int) $staff->id, 403);
            abort_unless(
                $this->authorization->canAccessClassSubject(
                    auth()->user(),
                    (int) $request->student_class_id,
                    (int) $request->subject_id,
                    (int) $request->academic_year_id
                ),
                403
            );
        }

        try {
            DB::beginTransaction();

            $data = $request->all();

            // Generate note code
            $lessonNote = new LessonNote();
            $data['note_code'] = $lessonNote->generateNoteCode();

            // Handle single attachment
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('lesson_notes/attachments', $fileName, 'public');
                $data['attachment'] = $path;
            }

            // Handle multiple attachments
            if ($request->hasFile('attachments')) {
                $attachments = [];
                foreach ($request->file('attachments') as $file) {
                    $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('lesson_notes/attachments', $fileName, 'public');
                    $attachments[] = $path;
                }
                $data['attachments'] = $attachments;
            }

            // Convert string to array for JSON fields
            $jsonFields = ['resources', 'learning_objectives', 'learning_outcomes', 'teaching_aids', 'assessment_methods', 'student_participation'];
            foreach ($jsonFields as $field) {
                if (isset($data[$field]) && is_string($data[$field])) {
                    // If it's a comma-separated string, convert to array
                    if (strpos($data[$field], ',') !== false) {
                        $data[$field] = array_map('trim', explode(',', $data[$field]));
                    } else {
                        $data[$field] = [$data[$field]];
                    }
                } elseif (!isset($data[$field])) {
                    $data[$field] = null;
                }
            }

            $lessonNote = LessonNote::create($data);

            DB::commit();

            return redirect()->route('lesson-notes.index')
                           ->with('success', 'Lesson note created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->with('error', 'Failed to create lesson note: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * Display the specified lesson note.
     */
    public function show($id)
    {
        abort_unless(auth()->user()->can('lesson-notes.view'), 403);

        $lessonNote = LessonNote::with([
            'staff', 
            'studentClass', 
            'subject', 
            'academicYear', 
            'term', 
            'commentedBy'
        ])->findOrFail($id);
        
        $staffs = Staff::all();
        
        return view('lesson-notes.show', compact('lessonNote', 'staffs'));
    }

    /**
     * Show the form for editing the specified lesson note.
     */
    public function edit($id)
    {
        abort_unless(auth()->user()->can('lesson-notes.edit'), 403);

        $lessonNote = LessonNote::findOrFail($id);

        if ($this->authorization->isScopedStaff(auth()->user())) {
            abort_unless(
                $this->authorization->canAccessClassSubject(
                    auth()->user(),
                    (int) $lessonNote->student_class_id,
                    (int) $lessonNote->subject_id,
                    (int) $lessonNote->academic_year_id
                ),
                403
            );
        }
        $staffs = Staff::all();
        $classes = StudentClass::all();
        $subjects = Subject::all();
        $academicYears = AcademicYear::all();
        $terms = Term::all();
        
        return view('lesson-notes.edit', compact('lessonNote', 'staffs', 'classes', 'subjects', 'academicYears', 'terms'));
    }

    /**
     * Update the specified lesson note.
     */
    public function update(Request $request, $id)
    {
        $lessonNote = LessonNote::findOrFail($id);

        if ($this->authorization->isScopedStaff(auth()->user())) {
            $staff = $this->authorization->staffFor(auth()->user());
            abort_unless($staff && (int) $lessonNote->staff_id === (int) $staff->id, 403);
            abort_unless(
                $this->authorization->canAccessClassSubject(
                    auth()->user(),
                    (int) $lessonNote->student_class_id,
                    (int) $lessonNote->subject_id,
                    (int) $lessonNote->academic_year_id
                ),
                403
            );
        }

        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'student_class_id' => 'required|exists:student_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'topic' => 'required|string|max:255',
            'content' => 'required|string',
            'lesson_date' => 'required|date',
            'type' => 'required|in:daily,weekly,monthly,termly',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'attachment' => 'nullable|file|max:10240',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->all();

            // Handle single attachment
            if ($request->hasFile('attachment')) {
                // Delete old attachment
                if ($lessonNote->attachment) {
                    Storage::disk('public')->delete($lessonNote->attachment);
                }
                
                $file = $request->file('attachment');
                $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('lesson_notes/attachments', $fileName, 'public');
                $data['attachment'] = $path;
            }

            // Handle multiple attachments
            if ($request->hasFile('attachments')) {
                // Delete old attachments
                if ($lessonNote->attachments) {
                    foreach ($lessonNote->attachments as $oldAttachment) {
                        Storage::disk('public')->delete($oldAttachment);
                    }
                }
                
                $attachments = [];
                foreach ($request->file('attachments') as $file) {
                    $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('lesson_notes/attachments', $fileName, 'public');
                    $attachments[] = $path;
                }
                $data['attachments'] = $attachments;
            }

            // Convert string to array for JSON fields
            $jsonFields = ['resources', 'learning_objectives', 'learning_outcomes', 'teaching_aids', 'assessment_methods', 'student_participation'];
            foreach ($jsonFields as $field) {
                if (isset($data[$field]) && is_string($data[$field])) {
                    if (strpos($data[$field], ',') !== false) {
                        $data[$field] = array_map('trim', explode(',', $data[$field]));
                    } else {
                        $data[$field] = [$data[$field]];
                    }
                } elseif (!isset($data[$field])) {
                    $data[$field] = null;
                }
            }

            $lessonNote->update($data);

            DB::commit();

            return redirect()->route('lesson-notes.index')
                           ->with('success', 'Lesson note updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->with('error', 'Failed to update lesson note: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * Remove the specified lesson note.
     */
    public function destroy($id)
    {
        $lessonNote = LessonNote::findOrFail($id);

        try {
            DB::beginTransaction();

            // Delete attachments
            if ($lessonNote->attachment) {
                Storage::disk('public')->delete($lessonNote->attachment);
            }
            
            if ($lessonNote->attachments) {
                foreach ($lessonNote->attachments as $attachment) {
                    Storage::disk('public')->delete($attachment);
                }
            }

            $lessonNote->delete();

            DB::commit();

            return redirect()->route('lesson-notes.index')
                           ->with('success', 'Lesson note deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->with('error', 'Failed to delete lesson note: ' . $e->getMessage());
        }
    }

    /**
     * Clone a lesson note.
     */
    public function clone($id)
    {
        $original = LessonNote::findOrFail($id);

        try {
            DB::beginTransaction();

            $newNote = $original->replicate();
            $newNote->note_code = (new LessonNote())->generateNoteCode();
            $newNote->lesson_date = now()->toDateString();
            $newNote->status = null;
            $newNote->comment = null;
            $newNote->comments = [];
            $newNote->commented_by = null;
            $newNote->created_at = now();
            $newNote->updated_at = now();
            
            // Don't clone attachments
            $newNote->attachment = null;
            $newNote->attachments = [];
            
            $newNote->save();

            DB::commit();

            return redirect()->route('lesson-notes.index')
                           ->with('success', 'Lesson note cloned successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->with('error', 'Failed to clone lesson note: ' . $e->getMessage());
        }
    }

    /**
     * Store a comment for a lesson note.
     */
    public function storeComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string',
            'commented_by' => 'required|exists:staff,id'
        ]);

        $lessonNote = LessonNote::findOrFail($id);
        
        $comments = $lessonNote->comments ?? [];
        $newComment = [
            'comment' => $request->comment,
            'commented_by' => $request->commented_by,
            'commented_at' => now()->toDateTimeString()
        ];
        $comments[] = $newComment;

        $lessonNote->update([
            'comments' => $comments,
            'comment' => $request->comment,
            'commented_by' => $request->commented_by
        ]);

        return redirect()->route('lesson-notes.show', $id)
                       ->with('success', 'Comment added successfully');
    }

    public function downloadAttachment(LessonNote $lessonNote, $file)
{
    try {
        // Decode the base64 encoded file path
        $filePath = base64_decode($file);
        
        // Security check: Verify the file belongs to this lesson note
        $validAttachment = false;
        
        // Check if it's the main attachment
        if ($lessonNote->attachment === $filePath) {
            $validAttachment = true;
        }
        
        // Check if it's in the attachments array
        if (!$validAttachment && $lessonNote->attachments) {
            $attachments = is_array($lessonNote->attachments) 
                ? $lessonNote->attachments 
                : json_decode($lessonNote->attachments, true);
                
            if (is_array($attachments) && in_array($filePath, $attachments)) {
                $validAttachment = true;
            }
        }
        
        if (!$validAttachment) {
            abort(403, 'Unauthorized access to this file.');
        }
        
        // **FIX: Use the 'public' disk explicitly**
        if (!Storage::disk('public')->exists($filePath)) {
            return back()->with('error', 'File not found in storage.');
        }
        
        // Get file information
        $fileName = basename($filePath);
        $mimeType = Storage::disk('public')->mimeType($filePath);
        
        // Return file download response
        return Storage::disk('public')->download($filePath, $fileName, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
        ]);
        
    } catch (\Exception $e) {
        return back()->with('error', 'Error downloading file: ' . $e->getMessage());
    }
}

    /**
     * Stream file for preview (optional - for large files)
     *
     * @param LessonNote $lessonNote
     * @param string $file Base64 encoded file path
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function streamAttachment(LessonNote $lessonNote, $file)
    {
        $filePath = base64_decode($file);
        
        // Security check similar to download method
        // ... validation code ...
        
        // **FIX: Use the 'public' disk explicitly**
        if (!Storage::disk('public')->exists($filePath)) {
            abort(404);
        }
        
        return Storage::disk('public')->response($filePath);
    }

        
}
