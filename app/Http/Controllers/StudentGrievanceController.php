<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentGrievance;
use App\Models\StudentGrievanceCategory;
use App\Models\StudentGrievanceComment;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StudentGrievanceController extends Controller
{
    /**
     * ============================================================
     * INDEX
     * ============================================================
     *
     * Display grievances.
     *
     * Students only see their own grievances.
     * Staff/admin users can see all grievances.
     */
    public function index(Request $request)
    {
        $isStudent = $this->isStudent();

        if ($isStudent) {
            $student = $this->student();

            $query = StudentGrievance::query()
                ->with(['category', 'class', 'assignedTo'])
                ->where('student_id', $student->id);
        } else {
            $query = StudentGrievance::query()
                ->with(['student', 'category', 'class', 'assignedTo']);
        }

        /*
         * Search
         */
        if ($request->filled('search')) {

            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {

                $q->where('grievance_code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");

                if (!$this->isStudent()) {

                    $q->orWhereHas('student', function ($studentQuery) use ($search) {

                        $studentQuery
                            ->where('student_id', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
                }
            });
        }


        /*
         * Status filter
         */
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }


        /*
         * Priority filter
         */
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }


        /*
         * Category filter
         */
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }


        /*
         * Ordering
         */
        $query->orderByDesc('created_at');


        $grievances = $query->paginate(15)->withQueryString();

        $categories = StudentGrievanceCategory::active()->get();

        $statuses = [
            'draft',
            'submitted',
            'under_review',
            'investigation',
            'resolution_proposed',
            'resolved',
            'closed',
            'rejected',
            'appealed',
        ];

        $priorities = [
            'low',
            'medium',
            'high',
            'urgent',
        ];


        return view(
            'student-grievance.index',
            compact(
                'grievances',
                'categories',
                'statuses',
                'priorities'
            )
        );
    }


    /**
     * ============================================================
     * CREATE
     * ============================================================
     */
    public function create()
    {
        /*
         * Only authenticated students should create
         * grievances from the student portal.
         */
        $student = $this->student();

        if (!$student) {
            abort(403, 'Student authentication required.');
        }


        $categories = StudentGrievanceCategory::active()->get();

        /*
         * The student only needs the classes available
         * for selection. We do not expose every student
         * or every staff member here.
         */
        $classes = StudentClass::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();


        return view(
            'student-grievance.create',
            compact(
                'categories',
                'classes'
            )
        );
    }


    /**
     * ============================================================
     * STORE
     * ============================================================
     *
     * Create a new grievance.
     */
    public function store(Request $request)
    {
        /*
         * Get authenticated student using the correct guard.
         */
        $student = $this->student();

        if (!$student) {

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Your student session has expired. Please log in again.'
                );
        }


        /*
         * Validate request.
         */
        $validator = Validator::make(
            $request->all(),
            [
                'title' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'description' => [
                    'required',
                    'string',
                ],

                'category_id' => [
                    'nullable',
                    'exists:students_grievance_categories,id',
                ],

                'class_id' => [
                    'nullable',
                    'exists:student_classes,id',
                ],

                'priority' => [
                    'required',
                    'in:low,medium,high,urgent',
                ],

                'is_confidential' => [
                    'nullable',
                    'boolean',
                ],

                'is_anonymous' => [
                    'nullable',
                    'boolean',
                ],

                'attachment' => [
                    'nullable',
                    'file',
                    'max:10240',
                ],

                'attachments' => [
                    'nullable',
                    'array',
                ],

                'attachments.*' => [
                    'nullable',
                    'file',
                    'max:10240',
                ],
            ]
        );


        if ($validator->fails()) {

            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }


        $storedFiles = [];


        try {

            DB::beginTransaction();


            /*
             * Determine status.
             */
            $isDraft = $request->boolean('is_draft');

            $status = $isDraft
                ? 'draft'
                : 'submitted';


            /*
             * Create grievance.
             */
            $grievance = new StudentGrievance();

            $grievance->grievance_code =
                $this->generateGrievanceCode();

            $grievance->title =
                trim($request->input('title'));

            $grievance->description =
                trim($request->input('description'));

            $grievance->student_id =
                $student->id;

            $grievance->category_id =
                $request->input('category_id');

            $grievance->class_id =
                $request->input('class_id');

            $grievance->priority =
                $request->input('priority');

            $grievance->status =
                $status;

            $grievance->is_confidential =
                $request->boolean(
                    'is_confidential',
                    true
                );

            $grievance->is_anonymous =
                $request->boolean(
                    'is_anonymous',
                    false
                );


            /*
             * Anonymous grievances should remain
             * confidential automatically.
             */
            if ($grievance->is_anonymous) {
                $grievance->is_confidential = true;
            }


            /*
             * Submission date only exists once the
             * grievance is actually submitted.
             */
            if ($status === 'submitted') {
                $grievance->submission_date = now();
            }


            /*
             * Single attachment.
             */
            if ($request->hasFile('attachment')) {

                $path = $this->storeGrievanceFile(
                    $request->file('attachment')
                );

                $grievance->attachment = $path;

                $storedFiles[] = $path;
            }


            /*
             * Multiple attachments.
             */
            if ($request->hasFile('attachments')) {

                $attachments = [];

                foreach (
                    $request->file('attachments')
                    as $file
                ) {

                    if (!$file->isValid()) {
                        continue;
                    }

                    $path =
                        $this->storeGrievanceFile($file);

                    $attachments[] = $path;
                    $storedFiles[] = $path;
                }


                if (!empty($attachments)) {
                    $grievance->attachments = $attachments;
                }
            }


            /*
             * Save grievance.
             */
            $grievance->save();


            /*
             * Record history directly through the
             * relationship.
             *
             * This requires StudentGrievanceHistory
             * to exist. See the model below.
             */
            $this->recordHistory(
                $grievance,
                $status === 'draft'
                    ? 'created'
                    : 'submitted',
                $status === 'draft'
                    ? 'Grievance saved as draft.'
                    : 'Grievance submitted by student.',
                null,
                [
                    'status' => $status,
                ],
                'student',
                $student->id
            );


            DB::commit();


            $message = $status === 'draft'
                ? 'Grievance saved as draft successfully.'
                : 'Grievance submitted successfully. You will be notified of the progress.';


            return redirect()
                ->route(
                    'student-grievance.show',
                    $grievance->id
                )
                ->with('success', $message);


        } catch (\Throwable $e) {

            DB::rollBack();


            /*
             * Delete files that were already stored
             * if the database transaction failed.
             */
            foreach ($storedFiles as $file) {

                try {
                    Storage::disk('public')->delete($file);
                } catch (\Throwable $fileException) {
                    Log::warning(
                        'Failed to clean up grievance attachment.',
                        [
                            'file' => $file,
                            'error' => $fileException->getMessage(),
                        ]
                    );
                }
            }


            Log::error(
                'Student grievance submission failed.',
                [
                    'student_id' => $student->id,
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            );


            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Failed to submit grievance. Please try again.'
                );
        }
    }


    /**
     * ============================================================
     * SHOW
     * ============================================================
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
            'escalations.toStaff',
        ])->findOrFail($id);


        /*
         * Student can only view their own grievance.
         */
        $student = $this->student();

        if ($student) {

            if ((int) $grievance->student_id !== (int) $student->id) {
                abort(
                    403,
                    'You do not have permission to view this grievance.'
                );
            }
        }


        /*
         * Staff/admin can access the full record.
         */
        $staff = $this->isStudent()
            ? collect()
            : Staff::orderBy('first_name')->get();


        $categories =
            StudentGrievanceCategory::active()->get();


        /*
         * Only provide students to staff/admin views.
         */
        $students = $this->isStudent()
            ? collect()
            : Student::orderBy('first_name')->get();


        return view(
            'student-grievance.show',
            compact(
                'grievance',
                'staff',
                'categories',
                'students'
            )
        );
    }


    /**
     * ============================================================
     * EDIT
     * ============================================================
     */
    public function edit($id)
    {
        $grievance =
            StudentGrievance::findOrFail($id);


        if (!$grievance->canEdit()) {

            return redirect()
                ->route(
                    'student-grievance.show',
                    $id
                )
                ->with(
                    'error',
                    'This grievance cannot be edited because it is already being processed.'
                );
        }


        /*
         * Student ownership check.
         */
        $student = $this->student();

        if ($student) {

            if ((int) $grievance->student_id !== (int) $student->id) {

                abort(
                    403,
                    'You do not have permission to edit this grievance.'
                );
            }
        }


        $categories =
            StudentGrievanceCategory::active()->get();


        $classes =
            StudentClass::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get();


        return view(
            'student-grievance.edit',
            compact(
                'grievance',
                'categories',
                'classes'
            )
        );
    }


    /**
     * ============================================================
     * UPDATE
     * ============================================================
     */
    public function update(
        Request $request,
        $id
    ) {
        $grievance =
            StudentGrievance::findOrFail($id);


        if (!$grievance->canEdit()) {

            return redirect()
                ->route(
                    'student-grievance.show',
                    $id
                )
                ->with(
                    'error',
                    'This grievance cannot be edited.'
                );
        }


        /*
         * Student ownership.
         */
        $student = $this->student();

        if ($student) {

            if ((int) $grievance->student_id !== (int) $student->id) {

                abort(
                    403,
                    'You do not have permission to update this grievance.'
                );
            }
        }


        $validator = Validator::make(
            $request->all(),
            [
                'title' => 'required|string|max:255',

                'description' => 'required|string',

                'category_id' =>
                    'nullable|exists:students_grievance_categories,id',

                'class_id' =>
                    'nullable|exists:student_classes,id',

                'priority' =>
                    'required|in:low,medium,high,urgent',

                'is_confidential' =>
                    'nullable|boolean',

                'is_anonymous' =>
                    'nullable|boolean',

                'attachment' =>
                    'nullable|file|max:10240',

                'attachments' =>
                    'nullable|array',

                'attachments.*' =>
                    'nullable|file|max:10240',
            ]
        );


        if ($validator->fails()) {

            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }


        $newFiles = [];
        $filesToDeleteAfterCommit = [];


        try {

            DB::beginTransaction();


            $oldValues = $grievance->toArray();


            $grievance->title =
                trim($request->input('title'));

            $grievance->description =
                trim($request->input('description'));

            $grievance->category_id =
                $request->input('category_id');

            $grievance->class_id =
                $request->input('class_id');

            $grievance->priority =
                $request->input('priority');

            $grievance->is_confidential =
                $request->boolean(
                    'is_confidential',
                    true
                );

            $grievance->is_anonymous =
                $request->boolean(
                    'is_anonymous',
                    false
                );


            if ($grievance->is_anonymous) {
                $grievance->is_confidential = true;
            }


            /*
             * New single attachment.
             */
            if ($request->hasFile('attachment')) {

                $newPath =
                    $this->storeGrievanceFile(
                        $request->file('attachment')
                    );

                $newFiles[] = $newPath;

                if ($grievance->attachment) {
                    $filesToDeleteAfterCommit[] =
                        $grievance->attachment;
                }

                $grievance->attachment =
                    $newPath;
            }


            /*
             * Replace multiple attachments.
             */
            if ($request->hasFile('attachments')) {

                $newAttachments = [];

                foreach (
                    $request->file('attachments')
                    as $file
                ) {

                    if (!$file->isValid()) {
                        continue;
                    }

                    $path =
                        $this->storeGrievanceFile($file);

                    $newAttachments[] = $path;
                    $newFiles[] = $path;
                }


                if (!empty($newAttachments)) {

                    if (is_array($grievance->attachments)) {

                        foreach (
                            $grievance->attachments
                            as $oldFile
                        ) {
                            $filesToDeleteAfterCommit[] =
                                $oldFile;
                        }
                    }

                    $grievance->attachments =
                        $newAttachments;
                }
            }


            $grievance->save();


            $newValues =
                $grievance->fresh()->toArray();


            $this->recordHistory(
                $grievance,
                'updated',
                'Grievance information updated by student.',
                $oldValues,
                $newValues,
                'student',
                $student?->id
            );


            DB::commit();


            /*
             * Delete old files only after successful
             * database commit.
             */
            foreach (
                $filesToDeleteAfterCommit
                as $oldFile
            ) {

                if ($oldFile) {
                    Storage::disk('public')->delete($oldFile);
                }
            }


            return redirect()
                ->route(
                    'student-grievance.show',
                    $grievance->id
                )
                ->with(
                    'success',
                    'Grievance updated successfully.'
                );


        } catch (\Throwable $e) {

            DB::rollBack();


            foreach ($newFiles as $file) {

                try {
                    Storage::disk('public')->delete($file);
                } catch (\Throwable $cleanupException) {
                    Log::warning(
                        'Failed to clean up uploaded grievance file.',
                        [
                            'file' => $file,
                            'error' =>
                                $cleanupException->getMessage(),
                        ]
                    );
                }
            }


            Log::error(
                'Student grievance update failed.',
                [
                    'grievance_id' => $id,
                    'student_id' => $student?->id,
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            );


            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Failed to update grievance. Please try again.'
                );
        }
    }


    /**
     * ============================================================
     * DESTROY
     * ============================================================
     */
    public function destroy($id)
    {
        $grievance =
            StudentGrievance::findOrFail($id);


        if (!$grievance->canDelete()) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    'This grievance cannot be deleted.'
                );
        }


        /*
         * Students can only delete their own grievance.
         */
        $student = $this->student();

        if ($student) {

            if ((int) $grievance->student_id !== (int) $student->id) {

                abort(
                    403,
                    'You do not have permission to delete this grievance.'
                );
            }
        }


        try {

            DB::beginTransaction();


            $files = [];


            if ($grievance->attachment) {
                $files[] = $grievance->attachment;
            }


            if (is_array($grievance->attachments)) {

                foreach (
                    $grievance->attachments
                    as $attachment
                ) {
                    $files[] = $attachment;
                }
            }


            $grievance->delete();


            DB::commit();


            foreach ($files as $file) {

                if ($file) {
                    Storage::disk('public')->delete($file);
                }
            }


            return redirect()
                ->route('student-grievance.index')
                ->with(
                    'success',
                    'Grievance deleted successfully.'
                );


        } catch (\Throwable $e) {

            DB::rollBack();


            Log::error(
                'Student grievance deletion failed.',
                [
                    'grievance_id' => $id,
                    'error' => $e->getMessage(),
                ]
            );


            return redirect()
                ->back()
                ->with(
                    'error',
                    'Failed to delete grievance. Please try again.'
                );
        }
    }


    /**
     * ============================================================
     * ADD COMMENT
     * ============================================================
     */
    public function addComment(
        Request $request,
        $id
    ) {
        $grievance =
            StudentGrievance::findOrFail($id);


        $student = $this->student();


        /*
         * Student can only comment on their own grievance.
         */
        if ($student) {

            if ((int) $grievance->student_id !== (int) $student->id) {

                abort(
                    403,
                    'You do not have permission to comment on this grievance.'
                );
            }
        }


        $validator = Validator::make(
            $request->all(),
            [
                'comment' =>
                    'required|string|max:10000',

                'is_internal' =>
                    'nullable|boolean',

                'attachment' =>
                    'nullable|file|max:10240',
            ]
        );


        if ($validator->fails()) {

            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }


        try {

            DB::beginTransaction();


            $comment =
                new StudentGrievanceComment();


            $comment->grievance_id =
                $grievance->id;

            $comment->comment =
                trim($request->input('comment'));


            /*
             * Students cannot create internal
             * staff-only comments.
             */
            if ($student) {

                $comment->student_id =
                    $student->id;

                $comment->is_internal = false;

                $performerType = 'student';
                $performedBy = $student->id;

            } else {

                $user = auth('web')->user();

                if (!$user) {
                    throw new \RuntimeException(
                        'Staff authentication session not found.'
                    );
                }

                $comment->staff_id =
                    $user->staff_id ?? $user->id;

                $comment->is_internal =
                    $request->boolean('is_internal');

                $performerType = 'staff';
                $performedBy =
                    $user->staff_id ?? $user->id;
            }


            /*
             * Comment attachment.
             */
            if ($request->hasFile('attachment')) {

                $comment->attachment =
                    $this->storeCommentFile(
                        $request->file('attachment')
                    );
            }


            $comment->save();


            $this->recordHistory(
                $grievance,
                'commented',
                $student
                    ? 'Comment added by student.'
                    : 'Comment added by staff.',
                null,
                [
                    'comment_id' => $comment->id,
                ],
                $performerType,
                $performedBy
            );


            DB::commit();


            return redirect()
                ->route(
                    'student-grievance.show',
                    $grievance->id
                )
                ->with(
                    'success',
                    'Comment added successfully.'
                );


        } catch (\Throwable $e) {

            DB::rollBack();


            Log::error(
                'Student grievance comment failed.',
                [
                    'grievance_id' => $id,
                    'error' => $e->getMessage(),
                ]
            );


            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Failed to add comment. Please try again.'
                );
        }
    }


    /**
     * ============================================================
     * ASSIGN
     * ============================================================
     */
    public function assign(
        Request $request,
        $id
    ) {
        $this->requireStaff();


        $grievance =
            StudentGrievance::findOrFail($id);


        if (
            in_array(
                $grievance->status,
                ['closed', 'resolved']
            )
        ) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Cannot assign a closed or resolved grievance.'
                );
        }


        $validator = Validator::make(
            $request->all(),
            [
                'assigned_to' =>
                    'required|exists:staff,id',

                'remarks' =>
                    'nullable|string|max:5000',
            ]
        );


        if ($validator->fails()) {

            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }


        try {

            DB::beginTransaction();


            $oldAssignedTo =
                $grievance->assigned_to;


            $grievance->assigned_to =
                $request->assigned_to;

            $grievance->status =
                'under_review';

            if (!$grievance->review_date) {
                $grievance->review_date = now();
            }

            $grievance->remarks =
                $request->input('remarks');


            $grievance->save();


            $assignedStaff =
                Staff::find(
                    $request->assigned_to
                );


            $this->recordHistory(
                $grievance,
                'assigned',
                'Grievance assigned to staff member.',
                [
                    'assigned_to' => $oldAssignedTo,
                ],
                [
                    'assigned_to' =>
                        $request->assigned_to,
                ],
                'staff',
                $this->staffId()
            );


            DB::commit();


            return redirect()
                ->route(
                    'student-grievance.show',
                    $grievance->id
                )
                ->with(
                    'success',
                    'Grievance assigned successfully.'
                );


        } catch (\Throwable $e) {

            DB::rollBack();


            Log::error(
                'Student grievance assignment failed.',
                [
                    'grievance_id' => $id,
                    'error' => $e->getMessage(),
                ]
            );


            return redirect()
                ->back()
                ->with(
                    'error',
                    'Failed to assign grievance.'
                );
        }
    }


    /**
     * ============================================================
     * ESCALATE
     * ============================================================
     */
    public function escalate(
        Request $request,
        $id
    ) {
        $this->requireStaff();


        $grievance =
            StudentGrievance::findOrFail($id);


        $validator = Validator::make(
            $request->all(),
            [
                'to_staff_id' =>
                    'required|exists:staff,id',

                'reason' =>
                    'required|string|max:5000',

                'level' =>
                    'required|in:level_1,level_2,level_3,level_4',
            ]
        );


        if ($validator->fails()) {

            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }


        try {

            DB::beginTransaction();


            $oldAssignedTo =
                $grievance->assigned_to;


            $escalation =
                $grievance->escalations()->create([
                    'from_staff_id' =>
                        $oldAssignedTo,

                    'to_staff_id' =>
                        $request->to_staff_id,

                    'reason' =>
                        $request->reason,

                    'level' =>
                        $request->level,

                    'escalation_date' =>
                        now(),

                    'response_deadline' =>
                        now()->addDays(7),

                    'status' =>
                        'pending',
                ]);


            $grievance->assigned_to =
                $request->to_staff_id;


            $grievance->save();


            $toStaff =
                Staff::find(
                    $request->to_staff_id
                );


            $staffName =
                $toStaff?->full_name
                ?? 'staff member';


            $this->recordHistory(
                $grievance,
                'escalated',
                "Grievance escalated to {$staffName}.",
                [
                    'assigned_to' =>
                        $oldAssignedTo,
                ],
                [
                    'assigned_to' =>
                        $request->to_staff_id,

                    'escalation_id' =>
                        $escalation->id,
                ],
                'staff',
                $this->staffId()
            );


            DB::commit();


            return redirect()
                ->route(
                    'student-grievance.show',
                    $grievance->id
                )
                ->with(
                    'success',
                    'Grievance escalated successfully.'
                );


        } catch (\Throwable $e) {

            DB::rollBack();


            Log::error(
                'Student grievance escalation failed.',
                [
                    'grievance_id' => $id,
                    'error' => $e->getMessage(),
                ]
            );


            return redirect()
                ->back()
                ->with(
                    'error',
                    'Failed to escalate grievance.'
                );
        }
    }


    /**
     * ============================================================
     * APPEAL
     * ============================================================
     */
    public function appeal($id)
    {
        $grievance =
            StudentGrievance::findOrFail($id);


        $student = $this->student();


        if (!$student) {
            abort(403, 'Student authentication required.');
        }


        if (
            (int) $grievance->student_id !==
            (int) $student->id
        ) {
            abort(
                403,
                'You do not have permission to appeal this grievance.'
            );
        }


        if (!$grievance->canAppeal()) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    'This grievance cannot be appealed.'
                );
        }


        try {

            DB::beginTransaction();


            $oldStatus =
                $grievance->status;


            $grievance->status =
                'appealed';


            $grievance->save();


            $this->recordHistory(
                $grievance,
                'appealed',
                'Grievance appealed by student.',
                [
                    'status' => $oldStatus,
                ],
                [
                    'status' => 'appealed',
                ],
                'student',
                $student->id
            );


            DB::commit();


            return redirect()
                ->route(
                    'student-grievance.show',
                    $grievance->id
                )
                ->with(
                    'success',
                    'Grievance appealed successfully. It will be reviewed again.'
                );


        } catch (\Throwable $e) {

            DB::rollBack();


            Log::error(
                'Student grievance appeal failed.',
                [
                    'grievance_id' => $id,
                    'student_id' => $student->id,
                    'error' => $e->getMessage(),
                ]
            );


            return redirect()
                ->back()
                ->with(
                    'error',
                    'Failed to appeal grievance.'
                );
        }
    }


    /**
     * ============================================================
     * UPDATE STATUS
     * ============================================================
     */
    public function updateStatus(
        Request $request,
        $id
    ) {
        $this->requireStaff();


        $grievance =
            StudentGrievance::findOrFail($id);


        if ($grievance->status === 'closed') {

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Cannot update a closed grievance.'
                );
        }


        $validator = Validator::make(
            $request->all(),
            [
                'status' => [
                    'required',
                    'in:submitted,under_review,investigation,resolution_proposed,resolved,closed,rejected',
                ],

                'remarks' =>
                    'nullable|string|max:5000',

                'staff_response' =>
                    'nullable|string|max:10000',
            ]
        );


        if ($validator->fails()) {

            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }


        try {

            DB::beginTransaction();


            $oldStatus =
                $grievance->status;


            $newStatus =
                $request->status;


            $grievance->status =
                $newStatus;


            if ($newStatus === 'under_review') {
                $grievance->review_date =
                    $grievance->review_date ?? now();
            }


            if ($newStatus === 'resolved') {
                $grievance->resolution_date =
                    $grievance->resolution_date ?? now();
            }


            if ($newStatus === 'closed') {
                $grievance->closure_date =
                    $grievance->closure_date ?? now();
            }


            if ($request->filled('remarks')) {
                $grievance->remarks =
                    $request->remarks;
            }


            if ($request->filled('staff_response')) {
                $grievance->staff_response =
                    $request->staff_response;
            }


            $grievance->save();


            $this->recordHistory(
                $grievance,
                'status_changed',
                'Grievance status updated by staff.',
                [
                    'status' => $oldStatus,
                ],
                [
                    'status' => $newStatus,
                ],
                'staff',
                $this->staffId()
            );


            DB::commit();


            return redirect()
                ->route(
                    'student-grievance.show',
                    $grievance->id
                )
                ->with(
                    'success',
                    'Grievance status updated successfully.'
                );


        } catch (\Throwable $e) {

            DB::rollBack();


            Log::error(
                'Student grievance status update failed.',
                [
                    'grievance_id' => $id,
                    'error' => $e->getMessage(),
                ]
            );


            return redirect()
                ->back()
                ->with(
                    'error',
                    'Failed to update grievance status.'
                );
        }
    }


    /**
     * ============================================================
     * STATISTICS
     * ============================================================
     */
    public function statistics()
    {
        $this->requireStaff();


        $stats = [
            'total' =>
                StudentGrievance::count(),

            'pending' =>
                StudentGrievance::pending()->count(),

            'resolved' =>
                StudentGrievance::whereIn(
                    'status',
                    [
                        'resolved',
                        'closed',
                    ]
                )->count(),

            'by_status' =>
                StudentGrievance::selectRaw(
                    'status, COUNT(*) as count'
                )
                ->groupBy('status')
                ->get(),

            'by_priority' =>
                StudentGrievance::selectRaw(
                    'priority, COUNT(*) as count'
                )
                ->groupBy('priority')
                ->get(),

            'by_category' =>
                StudentGrievance::with('category')
                ->selectRaw(
                    'category_id, COUNT(*) as count'
                )
                ->groupBy('category_id')
                ->get(),

            'recent' =>
                StudentGrievance::with([
                    'student',
                    'category',
                ])
                ->latest()
                ->limit(10)
                ->get(),
        ];


        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }


    /**
     * ============================================================
     * AUTHENTICATION HELPERS
     * ============================================================
     */

    /**
     * Get authenticated student.
     */
    private function student()
    {
        return auth('student')->user();
    }


    /**
     * Determine whether the current user is
     * authenticated through the student guard.
     */
    private function isStudent(): bool
    {
        return auth('student')->check();
    }


    /**
     * Require staff/admin authentication.
     */
    private function requireStaff()
    {
        if (!auth('web')->check()) {
            abort(403, 'Staff authentication required.');
        }

        return auth('web')->user();
    }


    /**
     * Get authenticated staff ID.
     */
    private function staffId()
    {
        $user = auth('web')->user();

        if (!$user) {
            return null;
        }

        return $user->staff_id ?? $user->id;
    }


    /**
     * ============================================================
     * FILE HELPERS
     * ============================================================
     */

    /**
     * Store grievance attachment.
     */
    private function storeGrievanceFile($file): string
    {
        if (!$file || !$file->isValid()) {
            throw new \RuntimeException(
                'Invalid grievance attachment.'
            );
        }


        $extension =
            strtolower(
                $file->getClientOriginalExtension()
            );


        $allowed =
            [
                'pdf',
                'doc',
                'docx',
                'jpg',
                'jpeg',
                'png',
            ];


        if (!in_array($extension, $allowed, true)) {

            throw new \RuntimeException(
                'Unsupported attachment type.'
            );
        }


        $fileName =
            now()->format('YmdHis') .
            '_' .
            Str::random(12) .
            '.' .
            $extension;


        return $file->storeAs(
            'student-grievances/attachments',
            $fileName,
            'public'
        );
    }


    /**
     * Store comment attachment.
     */
    private function storeCommentFile($file): string
    {
        if (!$file || !$file->isValid()) {
            throw new \RuntimeException(
                'Invalid comment attachment.'
            );
        }


        $extension =
            strtolower(
                $file->getClientOriginalExtension()
            );


        $allowed =
            [
                'pdf',
                'doc',
                'docx',
                'jpg',
                'jpeg',
                'png',
            ];


        if (!in_array($extension, $allowed, true)) {

            throw new \RuntimeException(
                'Unsupported attachment type.'
            );
        }


        $fileName =
            now()->format('YmdHis') .
            '_' .
            Str::random(12) .
            '.' .
            $extension;


        return $file->storeAs(
            'student-grievances/comments',
            $fileName,
            'public'
        );
    }


    /**
     * ============================================================
     * HISTORY
     * ============================================================
     *
     * Centralized history creation.
     *
     * This prevents every controller method from having
     * to know how the history record is constructed.
     */
    private function recordHistory(
        StudentGrievance $grievance,
        string $action,
        string $description,
        $oldValues = null,
        $newValues = null,
        string $performerType = 'staff',
        $performedBy = null
    ) {
        return $grievance->histories()->create([
            'action' =>
                $action,

            'description' =>
                $description,

            'old_values' =>
                $oldValues,

            'new_values' =>
                $newValues,

            'performed_by' =>
                $performedBy,

            'performer_type' =>
                $performerType,
        ]);
    }


    /**
     * ============================================================
     * GRIEVANCE CODE
     * ============================================================
     */
    private function generateGrievanceCode(): string
    {
        do {

            $code =
                'SGRV-' .
                now()->format('Ymd') .
                '-' .
                strtoupper(
                    Str::random(6)
                );

        } while (
            StudentGrievance::where(
                'grievance_code',
                $code
            )->exists()
        );


        return $code;
    }
}
