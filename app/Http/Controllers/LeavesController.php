<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class LeavesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:web');
    }

    /**
     * Display a listing of leaves
     */
    public function index()
    {
        $leaves = Leave::orderBy('created_at', 'desc')->get();
        return view('leaves.index', compact('leaves'));
    }

    /**
     * Show the form for creating a new leave
     */
    public function create()
    {
        return view('leaves.create');
    }

    /**
     * Store a newly created leave
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'leave_type' => 'required|string',
            'reason' => 'nullable|string',
            'date_commencement' => 'required|date',
            'date_resumption' => 'required|date|after:date_commencement',
            'days_applied_for' => 'required|integer|min:1',
            'date_of_application' => 'required|date',
            'date_last_leave' => 'nullable|date',
            'days_entitled' => 'nullable|integer|min:0',
            'days_already_utilized' => 'nullable|integer|min:0',
            'signature' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = $validator->validated();
            $data['status'] = 'draft';
            $data['created_by'] = Auth::id();

            $leave = Leave::create($data);

            Log::info('Leave application created', [
                'leave_id' => $leave->id,
                'created_by' => Auth::id()
            ]);

            return redirect()
                ->route('leaves.index')
                ->with('success', 'Leave application created successfully!');

        } catch (\Exception $e) {
            Log::error('Error creating leave: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create leave application: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified leave (watch method)
     */
    public function watch($id)
    {
        $leave = Leave::findOrFail($id);
        return view('leaves.watch', compact('leave'));
    }

    /**
     * Display the specified leave (show method - alternative)
     */
    public function show($id)
    {
        $leave = Leave::findOrFail($id);
        return view('leaves.show', compact('leave'));
    }

    /**
     * Show the form for editing the specified leave
     */
    public function edit($id)
    {
        $leave = Leave::findOrFail($id);
        
        // Check if leave can be edited (only draft or pending)
        if (!in_array($leave->status, ['draft', 'pending'])) {
            return redirect()
                ->route('leaves.index')
                ->with('error', 'This leave application cannot be edited.');
        }

        return view('leaves.edit', compact('leave'));
    }

    /**
     * Update the specified leave
     */
    public function update(Request $request, $id)
    {
        $leave = Leave::findOrFail($id);

        // Check if leave can be updated
        if (!in_array($leave->status, ['draft', 'pending'])) {
            return redirect()
                ->route('leaves.index')
                ->with('error', 'This leave application cannot be updated.');
        }

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'leave_type' => 'required|string',
            'reason' => 'nullable|string',
            'date_commencement' => 'required|date',
            'date_resumption' => 'required|date|after:date_commencement',
            'days_applied_for' => 'required|integer|min:1',
            'date_of_application' => 'required|date',
            'date_last_leave' => 'nullable|date',
            'days_entitled' => 'nullable|integer|min:0',
            'days_already_utilized' => 'nullable|integer|min:0',
            'signature' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = $validator->validated();
            $leave->update($data);

            Log::info('Leave application updated', [
                'leave_id' => $leave->id,
                'updated_by' => Auth::id()
            ]);

            return redirect()
                ->route('leaves.show', $leave->id)
                ->with('success', 'Leave application updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating leave: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update leave application: ' . $e->getMessage());
        }
    }

    /**
     * Approve leave
     */
    public function approve($id)
    {
        try {
            $leave = Leave::findOrFail($id);

            if ($leave->status !== 'pending') {
                return redirect()
                    ->back()
                    ->with('error', 'This leave application cannot be approved.');
            }

            $leave->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => Auth::id(),
            ]);

            Log::info('Leave application approved', [
                'leave_id' => $leave->id,
                'approved_by' => Auth::id()
            ]);

            return redirect()
                ->route('leaves.show', $leave->id)
                ->with('success', 'Leave application approved successfully!');

        } catch (\Exception $e) {
            Log::error('Error approving leave: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Failed to approve leave application: ' . $e->getMessage());
        }
    }

    /**
     * Reject leave
     */
    public function reject($id)
    {
        try {
            $leave = Leave::findOrFail($id);

            if ($leave->status !== 'pending') {
                return redirect()
                    ->back()
                    ->with('error', 'This leave application cannot be rejected.');
            }

            $leave->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejected_by' => Auth::id(),
            ]);

            Log::info('Leave application rejected', [
                'leave_id' => $leave->id,
                'rejected_by' => Auth::id()
            ]);

            return redirect()
                ->route('leaves.show', $leave->id)
                ->with('success', 'Leave application rejected successfully!');

        } catch (\Exception $e) {
            Log::error('Error rejecting leave: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Failed to reject leave application: ' . $e->getMessage());
        }
    }

    /**
     * Cancel leave
     */
    public function cancel($id)
    {
        try {
            $leave = Leave::findOrFail($id);

            if (!in_array($leave->status, ['draft', 'pending'])) {
                return redirect()
                    ->back()
                    ->with('error', 'This leave application cannot be cancelled.');
            }

            $leave->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => Auth::id(),
            ]);

            Log::info('Leave application cancelled', [
                'leave_id' => $leave->id,
                'cancelled_by' => Auth::id()
            ]);

            return redirect()
                ->route('leaves.index')
                ->with('success', 'Leave application cancelled successfully!');

        } catch (\Exception $e) {
            Log::error('Error cancelling leave: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Failed to cancel leave application: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified leave
     */
    public function destroy($id)
    {
        try {
            $leave = Leave::findOrFail($id);

            // Only allow deletion of draft or cancelled leaves
            if (!in_array($leave->status, ['draft', 'cancelled'])) {
                return redirect()
                    ->back()
                    ->with('error', 'This leave application cannot be deleted.');
            }

            $leave->delete();

            Log::info('Leave application deleted', [
                'leave_id' => $leave->id,
                'deleted_by' => Auth::id()
            ]);

            return redirect()
                ->route('leaves.index')
                ->with('success', 'Leave application deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Error deleting leave: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Failed to delete leave application: ' . $e->getMessage());
        }
    }
            // Add this method to LessonNoteController
    protected function updateStatus(LessonNote $lessonNote, $status)
    {
        $lessonNote->status = $status;
        $lessonNote->save();
        
        // Add to comments
        $comments = $lessonNote->comments ?? [];
        $comments[] = [
            'type' => 'status_change',
            'comment' => "Status changed to: " . ucfirst($status),
            'commented_by' => auth()->id(),
            'commented_at' => now()->toDateTimeString(),
            'status' => $status
        ];
        $lessonNote->comments = $comments;
        $lessonNote->save();
    }
   
    public function submit($id)
    {
        $leave = Leave::findOrFail($id);
    
        // Only draft leaves can be submitted
        if ($leave->status !== 'draft') {
            return redirect()
                ->route('leaves.watch', $leave->id)
                ->with(
                    'error',
                    'Only draft leave applications can be submitted for approval.'
                );
        }
    
        $leave->status = 'pending';
        $leave->submitted_at = now();
    
        if (auth()->check()) {
            $leave->updated_by = auth()->id();
        }
    
        $leave->save();
    
        return redirect()
            ->route('leaves.watch', $leave->id)
            ->with(
                'success',
                'Leave application submitted successfully for approval.'
            );
    }
}