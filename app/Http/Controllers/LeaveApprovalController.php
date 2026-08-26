<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LeaveApprovalController extends Controller
{
    /**
     * Display leave applications awaiting approval.
     */
    public function index(Request $request)
    {
        $query = Leave::query()
            ->orderByRaw("
                CASE
                    WHEN status = 'pending' THEN 1
                    WHEN status = 'approved' THEN 2
                    WHEN status = 'rejected' THEN 3
                    ELSE 4
                END
            ")
            ->orderByDesc('id');

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%")
                    ->orWhere('leave_type', 'like', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        /*
        |--------------------------------------------------------------------------
        | LEAVE TYPE
        |--------------------------------------------------------------------------
        */

        if ($request->filled('leave_type')) {
            $query->where(
                'leave_type',
                $request->leave_type
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DATE FILTER
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date_from')) {
            $query->whereDate(
                'date_commencement',
                '>=',
                $request->date_from
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'date_commencement',
                '<=',
                $request->date_to
            );
        }

        $leaves = $query
            ->paginate(15)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | SUMMARY
        |--------------------------------------------------------------------------
        */

        $pendingCount = Leave::where(
            'status',
            'pending'
        )->count();

        $approvedCount = Leave::where(
            'status',
            'approved'
        )->count();

        $rejectedCount = Leave::where(
            'status',
            'rejected'
        )->count();

        /*
        |--------------------------------------------------------------------------
        | LEAVE TYPES
        |--------------------------------------------------------------------------
        */

        $leaveTypes = Leave::query()
            ->whereNotNull('leave_type')
            ->where('leave_type', '!=', '')
            ->distinct()
            ->orderBy('leave_type')
            ->pluck('leave_type');

        return view(
            'leave-approvals.index',
            compact(
                'leaves',
                'pendingCount',
                'approvedCount',
                'rejectedCount',
                'leaveTypes'
            )
        );
    }


    /**
     * Display one leave application for detailed review.
     */
    public function show(Leave $leave)
    {
        return view(
            'leave-approvals.show',
            compact('leave')
        );
    }


    /**
     * Approve a leave application.
     *
     * The boss may approve the full requested number
     * or specify a smaller number of days granted.
     */
    public function approve(
        Request $request,
        Leave $leave
    ) {
        if ($leave->status === 'approved') {
            return back()->with(
                'error',
                'This leave application has already been approved.'
            );
        }

        if ($leave->status === 'rejected') {
            return back()->with(
                'error',
                'A rejected leave application cannot be approved.'
            );
        }

        $validated = $request->validate([
            'days_granted' => [
                'required',
                'integer',
                'min:1',
            ],

            'recommendation' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'administrator_signature' => [
                'required',
                'string',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | DAYS VALIDATION
        |--------------------------------------------------------------------------
        */

        $requestedDays = (int) (
            $leave->days_applied_for ?? 0
        );

        $daysGranted = (int) $validated['days_granted'];

        if (
            $requestedDays > 0 &&
            $daysGranted > $requestedDays
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'days_granted' =>
                        "The approved days cannot exceed the {$requestedDays} days requested."
                ]);
        }

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | APPROVER NAME
            |--------------------------------------------------------------------------
            */

            $administratorName =
                Auth::user()?->name
                ?? Auth::user()?->full_name
                ?? 'Administrator';

            /*
            |--------------------------------------------------------------------------
            | UPDATE LEAVE
            |--------------------------------------------------------------------------
            */

            $leave->update([
                'status' =>
                    'approved',

                'days_granted' =>
                    $daysGranted,

                'recommendation' =>
                    $validated['recommendation'] ?? null,

                'administrator_name' =>
                    $administratorName,

                'administrator_signature' =>
                    $validated['administrator_signature'],

                'administrator_date' =>
                    now()->toDateString(),
            ]);

            DB::commit();

            return redirect()
                ->route(
                    'leave-approvals.show',
                    $leave
                )
                ->with(
                    'success',
                    'Leave application approved successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to approve the leave application.'
                );
        }
    }


    /**
     * Reject a leave application.
     */
    public function reject(
        Request $request,
        Leave $leave
    ) {
        if ($leave->status === 'approved') {
            return back()->with(
                'error',
                'An approved leave application cannot be rejected.'
            );
        }

        if ($leave->status === 'rejected') {
            return back()->with(
                'error',
                'This leave application has already been rejected.'
            );
        }

        $validated = $request->validate([
            'recommendation' => [
                'required',
                'string',
                'max:5000',
            ],

            'administrator_signature' => [
                'required',
                'string',
            ],
        ]);

        DB::beginTransaction();

        try {

            $administratorName =
                Auth::user()?->name
                ?? Auth::user()?->full_name
                ?? 'Administrator';

            $leave->update([
                'status' =>
                    'rejected',

                'recommendation' =>
                    $validated['recommendation'],

                'administrator_name' =>
                    $administratorName,

                'administrator_signature' =>
                    $validated['administrator_signature'],

                'administrator_date' =>
                    now()->toDateString(),

                'days_granted' =>
                    0,
            ]);

            DB::commit();

            return redirect()
                ->route(
                    'leave-approvals.show',
                    $leave
                )
                ->with(
                    'success',
                    'Leave application rejected successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to reject the leave application.'
                );
        }
    }


    /**
     * Modify the requested number of days and approve.
     *
     * This is intentionally separate from approve() so the
     * boss can clearly indicate that the requested period
     * was reduced.
     */
    public function modifyAndApprove(
        Request $request,
        Leave $leave
    ) {
        if ($leave->status === 'approved') {
            return back()->with(
                'error',
                'This leave application has already been approved.'
            );
        }

        if ($leave->status === 'rejected') {
            return back()->with(
                'error',
                'A rejected leave application cannot be modified and approved.'
            );
        }

        $validated = $request->validate([
            'days_granted' => [
                'required',
                'integer',
                'min:1',
            ],

            'recommendation' => [
                'required',
                'string',
                'max:5000',
            ],

            'administrator_signature' => [
                'required',
                'string',
            ],
        ]);

        $requestedDays = (int) (
            $leave->days_applied_for ?? 0
        );

        $daysGranted = (int) $validated['days_granted'];

        if ($requestedDays > 0) {

            if ($daysGranted >= $requestedDays) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'days_granted' =>
                            "For Modify & Approve, the approved days must be less than the {$requestedDays} days requested."
                    ]);
            }
        }

        DB::beginTransaction();

        try {

            $administratorName =
                Auth::user()?->name
                ?? Auth::user()?->full_name
                ?? 'Administrator';

            $leave->update([
                'status' =>
                    'approved',

                'days_granted' =>
                    $daysGranted,

                'recommendation' =>
                    $validated['recommendation'],

                'administrator_name' =>
                    $administratorName,

                'administrator_signature' =>
                    $validated['administrator_signature'],

                'administrator_date' =>
                    now()->toDateString(),
            ]);

            DB::commit();

            return redirect()
                ->route(
                    'leave-approvals.show',
                    $leave
                )
                ->with(
                    'success',
                    "Leave approved with {$daysGranted} day(s) granted instead of {$requestedDays} day(s) requested."
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to modify and approve the leave application.'
                );
        }
    }
}