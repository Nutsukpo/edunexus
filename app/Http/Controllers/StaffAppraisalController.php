<?php

namespace App\Http\Controllers;

use App\Models\StaffAppraisal;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StaffAppraisalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:web');
    }

    /**
     * Display a listing of staff appraisals
     */
    public function index(Request $request)
    {
        $query = StaffAppraisal::with(['staff', 'academicYear', 'term', 'reviewer']);

        // Filter by staff
        if ($request->has('staff_id') && $request->staff_id) {
            $query->where('staff_id', $request->staff_id);
        }

        // Filter by academic year
        if ($request->has('academic_year_id') && $request->academic_year_id) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // Filter by term
        if ($request->has('term_id') && $request->term_id) {
            $query->where('term_id', $request->term_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('file_name', 'LIKE', "%{$search}%")
                  ->orWhereHas('staff', function ($sub) use ($search) {
                      $sub->where('first_name', 'LIKE', "%{$search}%")
                          ->orWhere('last_name', 'LIKE', "%{$search}%")
                          ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Sort
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $query->orderBy($sort, $order);

        $appraisals = $query->paginate(15);

        // Get filter data
        $staffMembers = Staff::all();
        $academicYears = AcademicYear::all();
        $terms = Term::all();

        // Statistics
        $stats = [
            'total' => StaffAppraisal::count(),
            'submitted' => StaffAppraisal::where('status', 'submitted')->count(),
            'reviewed' => StaffAppraisal::where('status', 'reviewed')->count(),
            'approved' => StaffAppraisal::where('status', 'approved')->count(),
            'rejected' => StaffAppraisal::where('status', 'rejected')->count(),
            'drafts' => StaffAppraisal::where('status', 'draft')->count(),
        ];

        return view('staff-appraisals.index', compact('appraisals', 'staffMembers', 'academicYears', 'terms', 'stats'));
    }

    /**
     * Show the form for creating a new staff appraisal
     */
    public function create()
    {
        $academicYears = AcademicYear::where('is_active', true)->get();
        $terms = Term::where('is_active', true)->get();
        $staffMembers = Staff::all(); // Get all staff for dropdown
        
        return view('staff-appraisals.create', compact('academicYears', 'terms', 'staffMembers'));
    }

    /**
     * Store a newly created staff appraisal
     */
    public function store(Request $request)
    {
        Log::info('Staff Appraisal Store Request:', $request->all());

        // Check if file was uploaded
        if (!$request->hasFile('file')) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Please select a file to upload.');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'staff_id' => 'required|exists:staff,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:20480',
            'status' => 'nullable|in:draft,submitted',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed:', $validator->errors()->toArray());
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = $validator->validated();

            // Handle file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                
                // Validate file size
                if ($file->getSize() > 20480000) { // 20MB
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'File size exceeds the maximum limit of 20MB.');
                }
                
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('staff-appraisals/' . date('Y/m'), $filename, 'public');
                
                if (!$path) {
                    throw new \Exception('Failed to store file.');
                }
                
                $data['file_path'] = $path;
                $data['file_name'] = $file->getClientOriginalName();
                $data['file_type'] = $file->getClientOriginalExtension();
                $data['file_size'] = $file->getSize();
                $data['file_mime'] = $file->getMimeType();
            }

            // Set creator (using the selected staff)
            $data['created_by'] = $data['staff_id'];
            $data['submission_date'] = now()->format('Y-m-d');
            $data['status'] = $request->status ?? 'draft';

            Log::info('Data before create:', $data);

            // Create the appraisal
            $appraisal = StaffAppraisal::create($data);

            Log::info('Appraisal created successfully:', ['id' => $appraisal->id]);

            $message = $data['status'] === 'submitted' 
                ? 'Appraisal submitted successfully!' 
                : 'Appraisal saved as draft successfully!';

            return redirect()
                ->route('staff-appraisals.index')
                ->with('success', $message);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Database error: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Error creating staff appraisal: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to upload appraisal: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified staff appraisal
     */
    public function show($id)
    {
        $appraisal = StaffAppraisal::with([
            'staff',
            'academicYear',
            'term',
            'reviewer',
            'creator'
        ])->findOrFail($id);

        return view('staff-appraisals.show', compact('appraisal'));
    }

    /**
     * Show the form for editing the specified staff appraisal
     */
    public function edit($id)
    {
        $appraisal = StaffAppraisal::findOrFail($id);
        $staffMembers = Staff::all();
        $academicYears = AcademicYear::all();
        $terms = Term::all();

        return view('staff-appraisals.edit', compact('appraisal', 'staffMembers', 'academicYears', 'terms'));
    }

    /**
     * Update the specified staff appraisal
     */
    public function update(Request $request, $id)
    {
        $appraisal = StaffAppraisal::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'staff_id' => 'required|exists:staff,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:20480',
            'status' => 'nullable|in:draft,submitted',
            'remove_file' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = $request->all();

            // Handle file upload
            if ($request->hasFile('file')) {
                // Delete old file
                if ($appraisal->file_path) {
                    $this->deleteFile($appraisal->file_path);
                }
                
                $file = $request->file('file');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('staff-appraisals/' . date('Y/m'), $filename, 'public');
                
                $data['file_path'] = $path;
                $data['file_name'] = $file->getClientOriginalName();
                $data['file_type'] = $file->getClientOriginalExtension();
                $data['file_size'] = $file->getSize();
                $data['file_mime'] = $file->getMimeType();
            }

            // Remove file
            if ($request->has('remove_file') && $request->remove_file == 1) {
                $this->deleteFile($appraisal->file_path);
                $data['file_path'] = null;
                $data['file_name'] = null;
                $data['file_type'] = null;
                $data['file_size'] = null;
                $data['file_mime'] = null;
            }

            // If status is being changed to submitted, set submission date
            if (isset($data['status']) && $data['status'] === 'submitted' && $appraisal->status !== 'submitted') {
                $data['submission_date'] = now()->format('Y-m-d');
            }

            $appraisal->update($data);

            return redirect()
                ->route('staff-appraisals.show', $appraisal->id)
                ->with('success', 'Appraisal updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating staff appraisal: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update appraisal: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified staff appraisal
     */
    public function destroy($id)
    {
        try {
            $appraisal = StaffAppraisal::findOrFail($id);

            // Delete file
            if ($appraisal->file_path) {
                $this->deleteFile($appraisal->file_path);
            }

            $appraisal->delete();

            Log::info('Staff appraisal deleted', [
                'id' => $appraisal->id,
                'file_name' => $appraisal->file_name,
                'deleted_by' => Auth::id()
            ]);

            return redirect()
                ->route('staff-appraisals.index')
                ->with('success', 'Appraisal deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Error deleting staff appraisal: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Failed to delete appraisal: ' . $e->getMessage());
        }
    }

    /**
     * Download the appraisal file
     */
    public function download($id)
    {
        $appraisal = StaffAppraisal::findOrFail($id);

        if (!$appraisal->file_path) {
            abort(404, 'File not found.');
        }

        if (!Storage::disk('public')->exists($appraisal->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download(
            $appraisal->file_path,
            $appraisal->file_name,
            [
                'Content-Type' => $appraisal->file_mime ?? 'application/octet-stream',
            ]
        );
    }

    /**
     * Stream/view the file
     */
    public function view($id)
    {
        $appraisal = StaffAppraisal::findOrFail($id);

        if (!$appraisal->file_path) {
            abort(404, 'File not found.');
        }

        if (!Storage::disk('public')->exists($appraisal->file_path)) {
            abort(404, 'File not found.');
        }

        $file = Storage::disk('public')->get($appraisal->file_path);
        $mime = $appraisal->file_mime ?? Storage::disk('public')->mimeType($appraisal->file_path);
        
        return response($file, 200)
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', 'inline; filename="' . $appraisal->file_name . '"');
    }

    /**
     * Toggle status (submit/unsubmit)
     */
    public function toggleStatus($id)
    {
        try {
            $appraisal = StaffAppraisal::findOrFail($id);

            if ($appraisal->status === 'submitted') {
                $appraisal->update(['status' => 'draft']);
                $message = 'Appraisal moved to draft successfully!';
            } else {
                $appraisal->submit();
                $message = 'Appraisal submitted successfully!';
            }

            return redirect()
                ->route('staff-appraisals.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Error toggling status: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Failed to toggle status: ' . $e->getMessage());
        }
    }

    /**
     * Review appraisal
     */
    public function review(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reviewer_comments' => 'nullable|string',
            'status' => 'required|in:reviewed,approved,rejected',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        try {
            $appraisal = StaffAppraisal::findOrFail($id);

            // Get the reviewer (current user's staff or selected)
            $reviewer = Staff::where('email', Auth::user()->email)->first();
            
            if (!$reviewer) {
                return redirect()->back()->with('error', 'Reviewer staff record not found.');
            }

            if ($request->status === 'approved') {
                $appraisal->approve($reviewer->id, $request->reviewer_comments);
                $message = 'Appraisal approved successfully!';
            } elseif ($request->status === 'rejected') {
                $appraisal->reject($reviewer->id, $request->reviewer_comments);
                $message = 'Appraisal rejected successfully!';
            } else {
                $appraisal->review($reviewer->id, $request->reviewer_comments);
                $message = 'Appraisal reviewed successfully!';
            }

            Log::info('Appraisal reviewed', [
                'appraisal_id' => $appraisal->id,
                'status' => $request->status,
                'reviewed_by' => Auth::id()
            ]);

            return redirect()
                ->route('staff-appraisals.show', $appraisal->id)
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Error reviewing appraisal: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Failed to review appraisal: ' . $e->getMessage());
        }
    }

    /**
     * Delete file from storage
     */
    private function deleteFile($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            Log::info('File deleted: ' . $path);
            return true;
        }
        return false;
    }
}