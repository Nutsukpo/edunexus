<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\BillSheet;
use App\Models\StudentClass;
use App\Models\StudentClassAssignment;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BillSheetApprovalController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | BILL SHEET APPROVAL CENTER
    |--------------------------------------------------------------------------
    |
    | A Bill Sheet is connected to a student through:
    |
    | BillSheet
    |     ↓
    | student_class_assignment_id
    |     ↓
    | StudentClassAssignment
    |     ↓
    | Student
    |     ↓
    | StudentClass
    |
    | IMPORTANT:
    | Draft and pending Bill Sheets are both eligible for approval.
    | Already approved Bill Sheets can NEVER be approved again.
    |
    */

    /**
     * Bill Sheet statuses that may be approved or rejected.
     *
     * "approved" and "published" are deliberately excluded.
     */
    private const APPROVABLE_STATUSES = [
        'draft',
        'pending',
    ];

    /*
    |--------------------------------------------------------------------------
    | APPROVAL CENTER
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $classes = StudentClass::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::query()
            ->orderByDesc('id')
            ->get();

        $terms = Term::query()
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | EMPTY DEFAULTS
        |--------------------------------------------------------------------------
        */

        $billSheets = collect();

        $summary = [
            'total'          => 0,
            'pending'        => 0,
            'draft'          => 0,
            'approvable'     => 0,
            'approved'       => 0,
            'rejected'       => 0,
            'published'      => 0,
            'pending_amount' => 0,
            'total_amount'   => 0,
        ];

        /*
        |--------------------------------------------------------------------------
        | LOAD BILL SHEETS ONLY WHEN FILTERS ARE COMPLETE
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('student_class_id') &&
            $request->filled('academic_year_id') &&
            $request->filled('term_id')
        ) {
            $validated = $request->validate([
                'student_class_id' => [
                    'required',
                    'integer',
                    'exists:student_classes,id',
                ],

                'academic_year_id' => [
                    'required',
                    'integer',
                    'exists:academic_years,id',
                ],

                'term_id' => [
                    'required',
                    'integer',
                    'exists:terms,id',
                ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | CURRENT STUDENT CLASS ASSIGNMENTS
            |--------------------------------------------------------------------------
            */

            $assignmentIds = StudentClassAssignment::query()
                ->where('student_class_id', $validated['student_class_id'])
                ->where('academic_year_id', $validated['academic_year_id'])
                ->where('status', 'active')
                ->where('is_current', true)
                ->pluck('id');

            /*
            |--------------------------------------------------------------------------
            | BASE BILL SHEET QUERY
            |--------------------------------------------------------------------------
            */

            $query = BillSheet::query()
                ->with([
                    'studentClassAssignment.student',
                    'studentClassAssignment.studentClass',
                    'studentClassAssignment.academicYear',
                    'academicYear',
                    'term',
                    'items.feeCategory',
                    'generatedBy',
                    'approvedBy',
                ])
                ->whereIn('student_class_assignment_id', $assignmentIds)
                ->where('academic_year_id', $validated['academic_year_id'])
                ->where('term_id', $validated['term_id'])
                ->orderByRaw("
                    CASE
                        WHEN status = 'draft' THEN 1
                        WHEN status = 'pending' THEN 2
                        WHEN status = 'rejected' THEN 3
                        WHEN status = 'approved' THEN 4
                        WHEN status = 'published' THEN 5
                        ELSE 6
                    END
                ")
                ->orderBy('id');

            /*
            |--------------------------------------------------------------------------
            | SUMMARY
            |--------------------------------------------------------------------------
            */

            $summaryQuery = clone $query;

            $summary['total'] = (clone $summaryQuery)->count();

            $summary['draft'] = (clone $summaryQuery)
                ->where('status', 'draft')
                ->count();

            $summary['pending'] = (clone $summaryQuery)
                ->where('status', 'pending')
                ->count();

            /*
             * This is the important number shown to the cashier/approver.
             * It includes BOTH draft and pending.
             */
            $summary['approvable'] = (clone $summaryQuery)
                ->whereIn('status', self::APPROVABLE_STATUSES)
                ->count();

            $summary['approved'] = (clone $summaryQuery)
                ->where('status', 'approved')
                ->count();

            $summary['rejected'] = (clone $summaryQuery)
                ->where('status', 'rejected')
                ->count();

            $summary['published'] = (clone $summaryQuery)
                ->where('status', 'published')
                ->count();

            $summary['pending_amount'] = (clone $summaryQuery)
                ->whereIn('status', self::APPROVABLE_STATUSES)
                ->sum('net_amount');

            $summary['total_amount'] = (clone $summaryQuery)
                ->sum('net_amount');

            /*
            |--------------------------------------------------------------------------
            | PAGINATION
            |--------------------------------------------------------------------------
            */

            $billSheets = $query
                ->paginate(50)
                ->withQueryString();
        }

        return view(
            'bill-sheet-approvals.index',
            compact(
                'classes',
                'academicYears',
                'terms',
                'billSheets',
                'summary'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | APPROVE ALL
    |--------------------------------------------------------------------------
    |
    | This method is intentionally separate from bulkApprove().
    | It allows the "Approve All" button to approve every draft/pending
    | Bill Sheet in the currently selected class/year/term without checkboxes.
    |
    */

    public function approveAll(Request $request)
    {
        $validated = $request->validate([
            'student_class_id' => [
                'required',
                'integer',
                'exists:student_classes,id',
            ],

            'academic_year_id' => [
                'required',
                'integer',
                'exists:academic_years,id',
            ],

            'term_id' => [
                'required',
                'integer',
                'exists:terms,id',
            ],
        ]);

        DB::beginTransaction();

        try {
            $billSheets = $this->getApprovalQuery(
                $validated['student_class_id'],
                $validated['academic_year_id'],
                $validated['term_id']
            )
                ->whereIn('status', self::APPROVABLE_STATUSES)
                ->lockForUpdate()
                ->get();

            $approvedCount = 0;
            $skippedCount = 0;

            foreach ($billSheets as $billSheet) {
                if (!$billSheet->items()->exists()) {
                    $skippedCount++;
                    continue;
                }

                $this->approveBillSheet(
                    $billSheet,
                    'bulk_all'
                );

                $approvedCount++;
            }

            DB::commit();

            return redirect()
                ->route('bill-sheet-approvals.index', [
                    'student_class_id' => $validated['student_class_id'],
                    'academic_year_id' => $validated['academic_year_id'],
                    'term_id' => $validated['term_id'],
                ])
                ->with(
                    'success',
                    $this->approvalMessage(
                        'approved',
                        $approvedCount,
                        $skippedCount
                    )
                );
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to approve the Bill Sheets. No changes were committed.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | REJECT ALL
    |--------------------------------------------------------------------------
    */

    public function rejectAll(Request $request)
    {
        $validated = $request->validate([
            'student_class_id' => [
                'required',
                'integer',
                'exists:student_classes,id',
            ],

            'academic_year_id' => [
                'required',
                'integer',
                'exists:academic_years,id',
            ],

            'term_id' => [
                'required',
                'integer',
                'exists:terms,id',
            ],

            'rejection_reason' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        DB::beginTransaction();

        try {
            $billSheets = $this->getApprovalQuery(
                $validated['student_class_id'],
                $validated['academic_year_id'],
                $validated['term_id']
            )
                ->whereIn('status', self::APPROVABLE_STATUSES)
                ->lockForUpdate()
                ->get();

            $rejectedCount = 0;

            foreach ($billSheets as $billSheet) {
                $this->rejectBillSheet(
                    $billSheet,
                    $validated['rejection_reason'],
                    'bulk_all'
                );

                $rejectedCount++;
            }

            DB::commit();

            return redirect()
                ->route('bill-sheet-approvals.index', [
                    'student_class_id' => $validated['student_class_id'],
                    'academic_year_id' => $validated['academic_year_id'],
                    'term_id' => $validated['term_id'],
                ])
                ->with(
                    'success',
                    $rejectedCount
                        . ' Bill Sheet'
                        . ($rejectedCount === 1 ? '' : 's')
                        . ' rejected successfully.'
                );
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to reject the Bill Sheets. No changes were committed.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | BULK APPROVE SELECTED
    |--------------------------------------------------------------------------
    |
    | Supports the older checkbox-based Blade as well.
    | Draft AND pending are accepted.
    |
    */

    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'bill_sheet_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'bill_sheet_ids.*' => [
                'integer',
                'exists:bill_sheets,id',
            ],

            'student_class_id' => [
                'nullable',
                'integer',
                'exists:student_classes,id',
            ],

            'academic_year_id' => [
                'nullable',
                'integer',
                'exists:academic_years,id',
            ],

            'term_id' => [
                'nullable',
                'integer',
                'exists:terms,id',
            ],
        ]);

        $ids = collect($validated['bill_sheet_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        DB::beginTransaction();

        try {
            $query = BillSheet::query()
                ->whereIn('id', $ids)
                ->whereIn('status', self::APPROVABLE_STATUSES);

            $this->applyOptionalFilters(
                $query,
                $validated
            );

            $billSheets = $query
                ->lockForUpdate()
                ->get();

            $approvedCount = 0;
            $skippedCount = $ids->count() - $billSheets->count();

            foreach ($billSheets as $billSheet) {
                if (!$billSheet->items()->exists()) {
                    $skippedCount++;
                    continue;
                }

                $this->approveBillSheet(
                    $billSheet,
                    'bulk_selected'
                );

                $approvedCount++;
            }

            DB::commit();

            return redirect()
                ->route('bill-sheet-approvals.index', [
                    'student_class_id' => $validated['student_class_id'] ?? null,
                    'academic_year_id' => $validated['academic_year_id'] ?? null,
                    'term_id' => $validated['term_id'] ?? null,
                ])
                ->with(
                    'success',
                    $this->approvalMessage(
                        'approved',
                        $approvedCount,
                        $skippedCount
                    )
                );
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Bulk approval failed. No Bill Sheet changes were committed.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | BULK REJECT SELECTED
    |--------------------------------------------------------------------------
    */

    public function bulkReject(Request $request)
    {
        $validated = $request->validate([
            'bill_sheet_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'bill_sheet_ids.*' => [
                'integer',
                'exists:bill_sheets,id',
            ],

            'rejection_reason' => [
                'required',
                'string',
                'max:1000',
            ],

            'student_class_id' => [
                'nullable',
                'integer',
                'exists:student_classes,id',
            ],

            'academic_year_id' => [
                'nullable',
                'integer',
                'exists:academic_years,id',
            ],

            'term_id' => [
                'nullable',
                'integer',
                'exists:terms,id',
            ],
        ]);

        $ids = collect($validated['bill_sheet_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        DB::beginTransaction();

        try {
            $query = BillSheet::query()
                ->whereIn('id', $ids)
                ->whereIn('status', self::APPROVABLE_STATUSES);

            $this->applyOptionalFilters(
                $query,
                $validated
            );

            $billSheets = $query
                ->lockForUpdate()
                ->get();

            $rejectedCount = 0;
            $skippedCount = $ids->count() - $billSheets->count();

            foreach ($billSheets as $billSheet) {
                $this->rejectBillSheet(
                    $billSheet,
                    $validated['rejection_reason'],
                    'bulk_selected'
                );

                $rejectedCount++;
            }

            DB::commit();

            return redirect()
                ->route('bill-sheet-approvals.index', [
                    'student_class_id' => $validated['student_class_id'] ?? null,
                    'academic_year_id' => $validated['academic_year_id'] ?? null,
                    'term_id' => $validated['term_id'] ?? null,
                ])
                ->with(
                    'success',
                    $this->approvalMessage(
                        'rejected',
                        $rejectedCount,
                        $skippedCount
                    )
                );
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Bulk rejection failed. No Bill Sheet changes were committed.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | APPROVE ONE BILL SHEET
    |--------------------------------------------------------------------------
    */

    public function approve(BillSheet $billSheet)
    {
        DB::beginTransaction();

        try {
            $billSheet = BillSheet::query()
                ->whereKey($billSheet->id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
             * SECURITY / BUSINESS RULE:
             * Only draft and pending may be approved.
             */
            if (!in_array(
                $billSheet->status,
                self::APPROVABLE_STATUSES,
                true
            )) {
                DB::rollBack();

                return back()->with(
                    'error',
                    'This Bill Sheet cannot be approved because its current status is "' .
                    ($billSheet->status ?? 'unknown') .
                    '".'
                );
            }

            if (!$billSheet->items()->exists()) {
                DB::rollBack();

                return back()->with(
                    'error',
                    'This Bill Sheet cannot be approved because it has no Bill Sheet items.'
                );
            }

            $this->approveBillSheet(
                $billSheet,
                'single'
            );

            DB::commit();

            return back()->with(
                'success',
                'Bill Sheet approved successfully.'
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return back()->with(
                'error',
                'Unable to approve the Bill Sheet.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | REJECT ONE BILL SHEET
    |--------------------------------------------------------------------------
    */

    public function reject(
        Request $request,
        BillSheet $billSheet
    ) {
        $validated = $request->validate([
            'rejection_reason' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        DB::beginTransaction();

        try {
            $billSheet = BillSheet::query()
                ->whereKey($billSheet->id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
             * Draft AND pending may be rejected.
             * Approved/published cannot be rejected from this workflow.
             */
            if (!in_array(
                $billSheet->status,
                self::APPROVABLE_STATUSES,
                true
            )) {
                DB::rollBack();

                return back()->with(
                    'error',
                    'This Bill Sheet cannot be rejected because its current status is "' .
                    ($billSheet->status ?? 'unknown') .
                    '".'
                );
            }

            $this->rejectBillSheet(
                $billSheet,
                $validated['rejection_reason'],
                'single'
            );

            DB::commit();

            return back()->with(
                'success',
                'Bill Sheet rejected successfully.'
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return back()->with(
                'error',
                'Unable to reject the Bill Sheet.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | INTERNAL APPROVAL QUERY
    |--------------------------------------------------------------------------
    */

    private function getApprovalQuery(
        int $studentClassId,
        int $academicYearId,
        int $termId
    ) {
        $assignmentIds = StudentClassAssignment::query()
            ->where('student_class_id', $studentClassId)
            ->where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->where('is_current', true)
            ->pluck('id');

        return BillSheet::query()
            ->whereIn(
                'student_class_assignment_id',
                $assignmentIds
            )
            ->where(
                'academic_year_id',
                $academicYearId
            )
            ->where(
                'term_id',
                $termId
            );
    }

    /*
    |--------------------------------------------------------------------------
    | OPTIONAL FILTERS FOR SELECTED BILL SHEETS
    |--------------------------------------------------------------------------
    */

    private function applyOptionalFilters(
        $query,
        array $validated
    ): void {
        if (!empty($validated['academic_year_id'])) {
            $query->where(
                'academic_year_id',
                $validated['academic_year_id']
            );
        }

        if (!empty($validated['term_id'])) {
            $query->where(
                'term_id',
                $validated['term_id']
            );
        }

        if (!empty($validated['student_class_id'])) {
            $query->whereHas(
                'studentClassAssignment',
                function ($q) use ($validated) {
                    $q->where(
                        'student_class_id',
                        $validated['student_class_id']
                    );
                }
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | INTERNAL APPROVAL OPERATION
    |--------------------------------------------------------------------------
    */

    private function approveBillSheet(
        BillSheet $billSheet,
        string $method
    ): void {
        $metadata = is_array($billSheet->metadata)
            ? $billSheet->metadata
            : [];

        $metadata['approval'] = [
            'approved_by' => Auth::id(),
            'approved_at' => now()->toDateTimeString(),
            'method'      => $method,
            'previous_status' => $billSheet->status,
        ];

        /*
         * Keep the historical information as well.
         */
        $metadata['approved_by'] = Auth::id();
        $metadata['approved_at'] = now()->toDateTimeString();
        $metadata['approval_method'] = $method;

        $billSheet->update([
            'status'      => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'metadata'    => $metadata,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | INTERNAL REJECTION OPERATION
    |--------------------------------------------------------------------------
    */

    private function rejectBillSheet(
        BillSheet $billSheet,
        string $reason,
        string $method
    ): void {
        $metadata = is_array($billSheet->metadata)
            ? $billSheet->metadata
            : [];

        $metadata['rejection'] = [
            'rejected_by' => Auth::id(),
            'rejected_at' => now()->toDateTimeString(),
            'reason'      => $reason,
            'method'      => $method,
            'previous_status' => $billSheet->status,
        ];

        /*
         * Keep simple top-level metadata keys for compatibility
         * with existing Blade files.
         */
        $metadata['rejection_reason'] = $reason;
        $metadata['rejected_by'] = Auth::id();
        $metadata['rejected_at'] = now()->toDateTimeString();
        $metadata['rejection_method'] = $method;

        $billSheet->update([
            'status'      => 'rejected',
            'approved_by' => null,
            'approved_at' => null,
            'metadata'    => $metadata,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SUCCESS MESSAGE
    |--------------------------------------------------------------------------
    */

    private function approvalMessage(
        string $action,
        int $processed,
        int $skipped
    ): string {
        $message =
            $processed .
            ' Bill Sheet' .
            ($processed === 1 ? '' : 's') .
            ' ' .
            $action .
            ' successfully.';

        if ($skipped > 0) {
            $message .=
                ' ' .
                $skipped .
                ' Bill Sheet' .
                ($skipped === 1 ? '' : 's') .
                ' skipped because they were already approved, published, not eligible, or had no Bill Sheet items.';
        }

        return $message;
    }
}
