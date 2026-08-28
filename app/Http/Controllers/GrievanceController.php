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
use Illuminate\Support\Str;

class GrievanceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Get Staff Record For Authenticated User
    |--------------------------------------------------------------------------
    |
    | EDUNEXUS currently links User and Staff records using email.
    |
    */

    protected function authenticatedStaff()
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'You must be authenticated to perform this action.');
        }

        $staff = $user->staff;

        if (!$staff) {
            abort(
                403,
                'Your user account is not linked to a staff record. Please contact the system administrator.'
            );
        }

        return $staff;
    }


    /*
    |--------------------------------------------------------------------------
    | Check Whether User Has Administrative Grievance Access
    |--------------------------------------------------------------------------
    */

    protected function canManageGrievances(): bool
    {
        return auth()->user()->hasAnyRole([
            'Super Admin',
            'Administrator',
            'MIS',
            'Power User',
            'Accountant',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Check Whether User Is Staff
    |--------------------------------------------------------------------------
    */

    protected function isStaffUser(): bool
    {
        return auth()->user()->hasAnyRole([
            'Teaching Staff',
            'Non-Teaching Staff',
        ]);
    }


    /**
     * Display a listing of grievances.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Grievance::with([
            'staff',
            'category',
            'assignedTo'
        ]);

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }


        /*
        |--------------------------------------------------------------------------
        | Staff Users
        |--------------------------------------------------------------------------
        |
        | Teaching and Non-Teaching Staff should only see grievances
        | belonging to their own Staff record.
        |
        */

        if ($this->isStaffUser()) {

            $staff = $user->staff;

            if (!$staff) {
                abort(
                    403,
                    'Your user account is not linked to a staff record.'
                );
            }

            $query->where('staff_id', $staff->id);
        }


        /*
        |--------------------------------------------------------------------------
        | Management / Administrative Users
        |--------------------------------------------------------------------------
        |
        | Management users can see all grievances.
        | When assigned_to_me is selected, only show grievances assigned
        | to the authenticated user's Staff record.
        |
        */

        if ($this->canManageGrievances()) {

            if ($request->boolean('assigned_to_me')) {

                $staff = $user->staff;

                if (!$staff) {
                    abort(
                        403,
                        'Your user account is not linked to a staff record.'
                    );
                }

                $query->where('assigned_to', $staff->id);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'title',
                    'LIKE',
                    "%{$search}%"
                )

                ->orWhere(
                    'grievance_code',
                    'LIKE',
                    "%{$search}%"
                )

                ->orWhere(
                    'description',
                    'LIKE',
                    "%{$search}%"
                );
            });
        }


        /*
        |--------------------------------------------------------------------------
        | Results
        |--------------------------------------------------------------------------
        */

        $grievances = $query
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $categories = GrievanceCategory::active()->get();

        $statuses = [
            'draft',
            'submitted',
            'under_review',
            'investigation',
            'resolution_proposed',
            'resolved',
            'closed',
            'rejected',
            'appealed'
        ];

        $priorities = [
            'low',
            'medium',
            'high',
            'urgent'
        ];

        return view(
            'grievance.index',
            compact(
                'grievances',
                'categories',
                'statuses',
                'priorities'
            )
        );
    }


    /**
     * Show the form for creating a new grievance.
     */
    public function create()
    {
        $categories = GrievanceCategory::active()->get();

        $staff = Staff::all();

        $departments = Department::all();

        return view(
            'grievance.create',
            compact(
                'categories',
                'staff',
                'departments'
            )
        );
    }


    /**
     * Store a newly created grievance.
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'title' => 'required|string|max:255',

                'description' => 'required|string',

                'category_id' => 'nullable|exists:grievance_categories,id',

                'priority' => 'required|in:low,medium,high,urgent',

                'is_confidential' => 'nullable|boolean',

                'is_anonymous' => 'nullable|boolean',

                'attachment' => 'nullable|file|max:10240',

                'attachments.*' => 'nullable|file|max:10240',
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


            /*
            |--------------------------------------------------------------------------
            | Get Staff From Authenticated User
            |--------------------------------------------------------------------------
            */

            $staff = $this->authenticatedStaff();

            $staffId = $staff->id;


            /*
            |--------------------------------------------------------------------------
            | Create Grievance
            |--------------------------------------------------------------------------
            */

            $grievance = new Grievance();

            $grievance->grievance_code =
                $grievance->generateGrievanceCode();

            $grievance->title =
                $request->title;

            $grievance->description =
                $request->description;

            $grievance->staff_id =
                $staffId;

            $grievance->category_id =
                $request->category_id;

            $grievance->priority =
                $request->priority;

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
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            if ($request->boolean('is_draft')) {

                $grievance->status = 'draft';

            } else {

                $grievance->status = 'submitted';

                $grievance->submission_date = now();
            }


            /*
            |--------------------------------------------------------------------------
            | Single Attachment
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('attachment')) {

                $file =
                    $request->file('attachment');

                $fileName =
                    time()
                    . '_'
                    . Str::slug(
                        pathinfo(
                            $file->getClientOriginalName(),
                            PATHINFO_FILENAME
                        )
                    )
                    . '.'
                    . $file->getClientOriginalExtension();

                $path =
                    $file->storeAs(
                        'grievances/attachments',
                        $fileName,
                        'public'
                    );

                $grievance->attachment =
                    $path;
            }


            /*
            |--------------------------------------------------------------------------
            | Multiple Attachments
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('attachments')) {

                $attachments = [];

                foreach (
                    $request->file('attachments')
                    as $file
                ) {

                    $fileName =
                        time()
                        . '_'
                        . Str::slug(
                            pathinfo(
                                $file->getClientOriginalName(),
                                PATHINFO_FILENAME
                            )
                        )
                        . '.'
                        . $file->getClientOriginalExtension();

                    $path =
                        $file->storeAs(
                            'grievances/attachments',
                            $fileName,
                            'public'
                        );

                    $attachments[] =
                        $path;
                }

                $grievance->attachments =
                    $attachments;
            }


            /*
            |--------------------------------------------------------------------------
            | Save
            |--------------------------------------------------------------------------
            */

            $grievance->save();


            /*
            |--------------------------------------------------------------------------
            | History
            |--------------------------------------------------------------------------
            */

            $action =
                $grievance->status === 'draft'
                    ? 'created'
                    : 'submitted';

            $description =
                $grievance->status === 'draft'
                    ? 'Grievance saved as draft'
                    : 'Grievance submitted by staff';


            $grievance->addHistory(
                $action,
                $description,
                null,
                [
                    'status' =>
                        $grievance->status
                ]
            );


            DB::commit();


            $message =
                $grievance->status === 'draft'
                    ? 'Grievance saved as draft successfully!'
                    : 'Grievance submitted successfully! You will be notified of the progress.';


            return redirect()
                ->route(
                    'grievance.show',
                    $grievance->id
                )
                ->with(
                    'success',
                    $message
                );


        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Failed to submit grievance: '
                    . $e->getMessage()
                )
                ->withInput();
        }
    }


    /**
     * Display the specified grievance.
     */
    public function show($id)
    {
        $grievance =
            Grievance::with([
                'staff',
                'category',
                'assignedTo',
                'department',
                'comments.staff',
                'histories.performedBy',
                'escalations.fromStaff',
                'escalations.toStaff'
            ])
            ->findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | Staff Access Control
        |--------------------------------------------------------------------------
        */

        if ($this->isStaffUser()) {

            $staff =
                $this->authenticatedStaff();

            if (
                (int) $grievance->staff_id
                !==
                (int) $staff->id
            ) {

                abort(
                    403,
                    'You do not have permission to view this grievance.'
                );
            }
        }


        $staff =
            Staff::all();

        $categories =
            GrievanceCategory::active()->get();


        return view(
            'grievance.show',
            compact(
                'grievance',
                'staff',
                'categories'
            )
        );
    }


    /**
     * Show the form for editing the specified grievance.
     */
    public function edit($id)
    {
        $grievance =
            Grievance::findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | Check Whether Grievance Can Be Edited
        |--------------------------------------------------------------------------
        */

        if (!$grievance->canEdit()) {

            return redirect()
                ->route(
                    'grievance.show',
                    $id
                )
                ->with(
                    'error',
                    'This grievance cannot be edited as it is already being processed.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Staff Access Control
        |--------------------------------------------------------------------------
        */

        if ($this->isStaffUser()) {

            $staff =
                $this->authenticatedStaff();

            if (
                (int) $grievance->staff_id
                !==
                (int) $staff->id
            ) {

                abort(
                    403,
                    'You do not have permission to edit this grievance.'
                );
            }
        }


        $categories =
            GrievanceCategory::active()->get();

        $staff =
            Staff::all();

        $departments =
            Department::all();


        return view(
            'grievance.edit',
            compact(
                'grievance',
                'categories',
                'staff',
                'departments'
            )
        );
    }


    /**
     * Update the specified grievance.
     */
    public function update(
        Request $request,
        $id
    ) {

        $grievance =
            Grievance::findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | Can Edit
        |--------------------------------------------------------------------------
        */

        if (!$grievance->canEdit()) {

            return redirect()
                ->route(
                    'grievance.show',
                    $id
                )
                ->with(
                    'error',
                    'This grievance cannot be edited.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Staff Access Control
        |--------------------------------------------------------------------------
        */

        if ($this->isStaffUser()) {

            $staff =
                $this->authenticatedStaff();

            if (
                (int) $grievance->staff_id
                !==
                (int) $staff->id
            ) {

                abort(
                    403,
                    'You do not have permission to edit this grievance.'
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validator = Validator::make(
            $request->all(),
            [
                'title' =>
                    'required|string|max:255',

                'description' =>
                    'required|string',

                'category_id' =>
                    'nullable|exists:grievance_categories,id',

                'priority' =>
                    'required|in:low,medium,high,urgent',

                'is_confidential' =>
                    'nullable|boolean',

                'is_anonymous' =>
                    'nullable|boolean',

                'attachment' =>
                    'nullable|file|max:10240',

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


        try {

            DB::beginTransaction();


            $oldValues =
                $grievance->toArray();


            /*
            |--------------------------------------------------------------------------
            | Single Attachment
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('attachment')) {

                if ($grievance->attachment) {

                    Storage::disk('public')
                        ->delete(
                            $grievance->attachment
                        );
                }


                $file =
                    $request->file('attachment');

                $fileName =
                    time()
                    . '_'
                    . Str::slug(
                        pathinfo(
                            $file->getClientOriginalName(),
                            PATHINFO_FILENAME
                        )
                    )
                    . '.'
                    . $file->getClientOriginalExtension();


                $path =
                    $file->storeAs(
                        'grievances/attachments',
                        $fileName,
                        'public'
                    );


                $grievance->attachment =
                    $path;
            }


            /*
            |--------------------------------------------------------------------------
            | Multiple Attachments
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('attachments')) {

                if ($grievance->attachments) {

                    foreach (
                        $grievance->attachments
                        as $oldAttachment
                    ) {

                        Storage::disk('public')
                            ->delete(
                                $oldAttachment
                            );
                    }
                }


                $attachments = [];


                foreach (
                    $request->file('attachments')
                    as $file
                ) {

                    $fileName =
                        time()
                        . '_'
                        . Str::slug(
                            pathinfo(
                                $file->getClientOriginalName(),
                                PATHINFO_FILENAME
                            )
                        )
                        . '.'
                        . $file->getClientOriginalExtension();


                    $path =
                        $file->storeAs(
                            'grievances/attachments',
                            $fileName,
                            'public'
                        );


                    $attachments[] =
                        $path;
                }


                $grievance->attachments =
                    $attachments;
            }


            /*
            |--------------------------------------------------------------------------
            | Update Main Fields
            |--------------------------------------------------------------------------
            */

            $grievance->title =
                $request->title;

            $grievance->description =
                $request->description;

            $grievance->category_id =
                $request->category_id;

            $grievance->priority =
                $request->priority;

            $grievance->is_confidential =
                $request->boolean(
                    'is_confidential'
                );

            $grievance->is_anonymous =
                $request->boolean(
                    'is_anonymous'
                );


            $grievance->save();


            /*
            |--------------------------------------------------------------------------
            | History
            |--------------------------------------------------------------------------
            */

            $newValues =
                $grievance->toArray();


            $grievance->addHistory(
                'updated',
                'Grievance updated',
                $oldValues,
                $newValues
            );


            DB::commit();


            return redirect()
                ->route(
                    'grievance.show',
                    $grievance->id
                )
                ->with(
                    'success',
                    'Grievance updated successfully!'
                );


        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Failed to update grievance: '
                    . $e->getMessage()
                )
                ->withInput();
        }
    }


    /**
     * Remove the specified grievance.
     */
    public function destroy($id)
    {
        $grievance =
            Grievance::findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | Staff Access Control
        |--------------------------------------------------------------------------
        */

        if ($this->isStaffUser()) {

            $staff =
                $this->authenticatedStaff();

            if (
                (int) $grievance->staff_id
                !==
                (int) $staff->id
            ) {

                abort(
                    403,
                    'You do not have permission to delete this grievance.'
                );
            }
        }


        if (!$grievance->canDelete()) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    'This grievance cannot be deleted.'
                );
        }


        try {

            DB::beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | Delete Single Attachment
            |--------------------------------------------------------------------------
            */

            if ($grievance->attachment) {

                Storage::disk('public')
                    ->delete(
                        $grievance->attachment
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Delete Multiple Attachments
            |--------------------------------------------------------------------------
            */

            if ($grievance->attachments) {

                foreach (
                    $grievance->attachments
                    as $attachment
                ) {

                    Storage::disk('public')
                        ->delete(
                            $attachment
                        );
                }
            }


            $grievance->delete();


            DB::commit();


            return redirect()
                ->route(
                    'grievance.index'
                )
                ->with(
                    'success',
                    'Grievance deleted successfully!'
                );


        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Failed to delete grievance: '
                    . $e->getMessage()
                );
        }
    }


    /**
     * Assign grievance to a staff member.
     */
    public function assign(
        Request $request,
        $id
    ) {

        $grievance =
            Grievance::findOrFail($id);


        if (
            $grievance->status === 'closed'
            ||
            $grievance->status === 'resolved'
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

                'department_id' =>
                    'nullable|exists:departments,id',

                'remarks' =>
                    'nullable|string',
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

            $grievance->department_id =
                $request->department_id;

            $grievance->status =
                'under_review';

            $grievance->review_date =
                now();

            $grievance->remarks =
                $request->remarks;


            $grievance->save();


            /*
            |--------------------------------------------------------------------------
            | History
            |--------------------------------------------------------------------------
            */

            $assignedStaff =
                Staff::find(
                    $request->assigned_to
                );


            $grievance->addHistory(
                'assigned',
                "Grievance assigned to {$assignedStaff->full_name}",
                [
                    'assigned_to' =>
                        $oldAssignedTo
                ],
                [
                    'assigned_to' =>
                        $request->assigned_to
                ]
            );


            DB::commit();


            return redirect()
                ->route(
                    'grievance.show',
                    $grievance->id
                )
                ->with(
                    'success',
                    'Grievance assigned successfully!'
                );


        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Failed to assign grievance: '
                    . $e->getMessage()
                );
        }
    }


    /**
     * Update grievance status.
     */
    public function updateStatus(
        Request $request,
        $id
    ) {

        $grievance =
            Grievance::findOrFail($id);


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
                'status' =>
                    'required|in:under_review,investigation,resolution_proposed,resolved,closed,rejected',

                'remarks' =>
                    'nullable|string',

                'resolution' =>
                    'nullable|string',
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

            $grievance->remarks =
                $request->remarks;


            /*
            |--------------------------------------------------------------------------
            | Resolution
            |--------------------------------------------------------------------------
            */

            if ($newStatus === 'resolved') {

                $grievance->resolution_date =
                    now();
            }


            /*
            |--------------------------------------------------------------------------
            | Closure
            |--------------------------------------------------------------------------
            */

            if ($newStatus === 'closed') {

                $grievance->closure_date =
                    now();
            }


            /*
            |--------------------------------------------------------------------------
            | Rejection / Appeal Deadline
            |--------------------------------------------------------------------------
            */

            if ($newStatus === 'rejected') {

                $grievance->appeal_deadline =
                    now()->addDays(14);
            }


            /*
            |--------------------------------------------------------------------------
            | Resolution Details
            |--------------------------------------------------------------------------
            */

            if (
                $request->filled('resolution')
            ) {

                $additionalDetails =
                    $grievance->additional_details ?? [];


                $additionalDetails['resolution'] =
                    $request->resolution;


                $grievance->additional_details =
                    $additionalDetails;
            }


            $grievance->save();


            /*
            |--------------------------------------------------------------------------
            | History
            |--------------------------------------------------------------------------
            */

            $grievance->addHistory(
                'updated',
                "Status changed from {$oldStatus} to {$newStatus}",
                [
                    'status' =>
                        $oldStatus
                ],
                [
                    'status' =>
                        $newStatus
                ]
            );


            DB::commit();


            return redirect()
                ->route(
                    'grievance.show',
                    $grievance->id
                )
                ->with(
                    'success',
                    'Grievance status updated to '
                    . ucwords(
                        str_replace(
                            '_',
                            ' ',
                            $newStatus
                        )
                    )
                    . '!'
                );


        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Failed to update status: '
                    . $e->getMessage()
                );
        }
    }


    /**
     * Add comment to grievance.
     */
    public function addComment(
        Request $request,
        $id
    ) {

        $grievance =
            Grievance::findOrFail($id);


        $validator = Validator::make(
            $request->all(),
            [
                'comment' =>
                    'required|string',

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


            /*
            |--------------------------------------------------------------------------
            | Get Authenticated Staff
            |--------------------------------------------------------------------------
            */

            $staff =
                $this->authenticatedStaff();


            /*
            |--------------------------------------------------------------------------
            | Create Comment
            |--------------------------------------------------------------------------
            */

            $comment =
                new GrievanceComment();


            $comment->grievance_id =
                $grievance->id;

            $comment->staff_id =
                $staff->id;

            $comment->comment =
                $request->comment;

            $comment->is_internal =
                $request->boolean(
                    'is_internal'
                );


            /*
            |--------------------------------------------------------------------------
            | Attachment
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('attachment')) {

                $file =
                    $request->file('attachment');


                $fileName =
                    time()
                    . '_'
                    . Str::slug(
                        pathinfo(
                            $file->getClientOriginalName(),
                            PATHINFO_FILENAME
                        )
                    )
                    . '.'
                    . $file->getClientOriginalExtension();


                $path =
                    $file->storeAs(
                        'grievances/comments',
                        $fileName,
                        'public'
                    );


                $comment->attachment =
                    $path;
            }


            $comment->save();


            /*
            |--------------------------------------------------------------------------
            | History
            |--------------------------------------------------------------------------
            */

            $grievance->addHistory(
                'commented',
                'Comment added by ' . $staff->full_name,
                null,
                [
                    'comment' =>
                        $request->comment
                ]
            );


            DB::commit();


            return redirect()
                ->route(
                    'grievance.show',
                    $grievance->id
                )
                ->with(
                    'success',
                    'Comment added successfully!'
                );


        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Failed to add comment: '
                    . $e->getMessage()
                );
        }
    }


    /**
     * Escalate grievance.
     */
    public function escalate(
        Request $request,
        $id
    ) {

        $grievance =
            Grievance::findOrFail($id);


        $validator = Validator::make(
            $request->all(),
            [
                'to_staff_id' =>
                    'required|exists:staff,id',

                'reason' =>
                    'required|string',

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


            /*
            |--------------------------------------------------------------------------
            | Escalate
            |--------------------------------------------------------------------------
            */

            $escalation =
                $grievance->escalate(
                    $request->to_staff_id,
                    $request->reason,
                    $request->level
                );


            /*
            |--------------------------------------------------------------------------
            | Update Assignment
            |--------------------------------------------------------------------------
            */

            $grievance->assigned_to =
                $request->to_staff_id;

            $grievance->save();


            /*
            |--------------------------------------------------------------------------
            | History
            |--------------------------------------------------------------------------
            */

            $toStaff =
                Staff::find(
                    $request->to_staff_id
                );


            $grievance->addHistory(
                'updated',
                "Grievance escalated to {$toStaff->full_name} (Level: "
                . ucwords(
                    str_replace(
                        '_',
                        ' ',
                        $request->level
                    )
                )
                . ")",
                null,
                [
                    'escalation' =>
                        $escalation->toArray()
                ]
            );


            DB::commit();


            return redirect()
                ->route(
                    'grievance.show',
                    $grievance->id
                )
                ->with(
                    'success',
                    'Grievance escalated successfully!'
                );


        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Failed to escalate grievance: '
                    . $e->getMessage()
                );
        }
    }


    /**
     * Appeal a rejected grievance.
     */
    public function appeal($id)
    {
        $grievance =
            Grievance::findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | Ensure Grievance Can Be Appealed
        |--------------------------------------------------------------------------
        */

        if (!$grievance->canAppeal()) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    'This grievance cannot be appealed.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Staff Ownership Check
        |--------------------------------------------------------------------------
        */

        if ($this->isStaffUser()) {

            $staff =
                $this->authenticatedStaff();

            if (
                (int) $grievance->staff_id
                !==
                (int) $staff->id
            ) {

                abort(
                    403,
                    'You do not have permission to appeal this grievance.'
                );
            }
        }


        try {

            DB::beginTransaction();


            $grievance->status =
                'appealed';

            $grievance->save();


            /*
            |--------------------------------------------------------------------------
            | History
            |--------------------------------------------------------------------------
            */

            $grievance->addHistory(
                'appealed',
                'Grievance appealed by staff',
                [
                    'status' =>
                        'rejected'
                ],
                [
                    'status' =>
                        'appealed'
                ]
            );


            DB::commit();


            return redirect()
                ->route(
                    'grievance.show',
                    $grievance->id
                )
                ->with(
                    'success',
                    'Grievance appealed successfully! It will be reviewed again.'
                );


        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Failed to appeal grievance: '
                    . $e->getMessage()
                );
        }
    }


    /**
     * Get statistics for dashboard.
     */
    public function statistics()
    {
        $stats = [
            'total' =>
                Grievance::count(),

            'pending' =>
                Grievance::pending()->count(),

            'resolved' =>
                Grievance::resolved()->count(),

            'by_status' =>
                Grievance::selectRaw(
                    'status, count(*) as count'
                )
                ->groupBy('status')
                ->get(),

            'by_priority' =>
                Grievance::selectRaw(
                    'priority, count(*) as count'
                )
                ->groupBy('priority')
                ->get(),

            'by_category' =>
                Grievance::with('category')
                ->selectRaw(
                    'category_id, count(*) as count'
                )
                ->groupBy('category_id')
                ->get(),

            'recent' =>
                Grievance::with([
                    'staff',
                    'category'
                ])
                ->orderBy(
                    'created_at',
                    'desc'
                )
                ->limit(10)
                ->get(),
        ];


        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}