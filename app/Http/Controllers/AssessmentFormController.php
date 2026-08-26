<?php

namespace App\Http\Controllers;

use App\Models\AssessmentForm;
use App\Models\Staff;
use App\Models\StudentClass;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AssessmentFormController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:web');
    }

    /**
     * Display a listing of assessment forms
     */
    public function index(Request $request)
    {
        $query = AssessmentForm::with(['staff', 'studentClass', 'academicYear', 'term', 'subject']);

        // Filter by staff
        if ($request->has('staff_id') && $request->staff_id) {
            $query->where('staff_id', $request->staff_id);
        }

        // Filter by class
        if ($request->has('student_class_id') && $request->student_class_id) {
            $query->where('student_class_id', $request->student_class_id);
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

        // Filter by assessment type
        if ($request->has('assessment_type') && $request->assessment_type) {
            $query->where('assessment_type', $request->assessment_type);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('file_name', 'LIKE', "%{$search}%");
            });
        }

        // Filter by current user if not admin
       

        // Sort
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $query->orderBy($sort, $order);

        $assessmentForms = $query->paginate(15);

        // Get filter data
        $staff = Staff::all();
        $classes = StudentClass::all();
        $academicYears = AcademicYear::all();
        $terms = Term::all();
        $subjects = Subject::all();

        // Statistics
        $stats = [
            'total' => AssessmentForm::count(),
            'published' => AssessmentForm::published()->count(),
            'drafts' => AssessmentForm::draft()->count(),
            'archived' => AssessmentForm::archived()->count(),
            
        ];

        return view('assessment-forms.index', compact('assessmentForms', 'staff', 'classes', 'academicYears', 'terms', 'subjects', 'stats'));
    }

    /**
     * Show the form for creating a new assessment form
     */
    public function create()
    {
        $staff = Staff::all();
        $classes = StudentClass::all();
        $academicYears = AcademicYear::where('is_active', true)->get();
        $terms = Term::where('is_active', true)->get();
        $subjects = Subject::all();

        return view('assessment-forms.create', compact('staff', 'classes', 'academicYears', 'terms', 'subjects'));
    }

    /**
     * Store a newly created assessment form
     */
    /**
 * Store a newly created assessment form
 */
public function store(Request $request)
{
    Log::info('Assessment Form Store Request:', $request->all());

    $validator = Validator::make($request->all(), [
        'title' => 'nullable|string|max:255',
        'description' => 'nullable|string',
        'staff_id' => 'required|exists:staff,id',
        'student_class_id' => 'required|exists:student_classes,id',
        'academic_year_id' => 'required|exists:academic_years,id',
        'term_id' => 'required|exists:terms,id',
        'subject_id' => 'nullable|exists:subjects,id',
        'assessment_date' => 'required|date',
        'due_date' => 'nullable|date|after_or_equal:assessment_date',
        'assessment_type' => 'required|in:quiz,test,exam,assignment,project',
        'file' => 'required|file|mimes:pdf,jpg,jpeg,png,gif,doc,docx|max:20480',
        'status' => 'nullable|in:draft,published',
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
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('assessment-forms/' . date('Y/m'), $filename, 'public');
            
            $data['file_path'] = $path;
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_type'] = $file->getClientOriginalExtension();
            $data['file_size'] = $file->getSize();
            $data['file_mime'] = $file->getMimeType();
        }

        // Set creator - FIX: Use the staff_id from the form directly
        // This ensures we use the selected staff member
        $data['created_by'] = $data['staff_id']; // Use the selected staff ID
        $data['status'] = $request->status ?? 'draft';

        Log::info('Data before create:', $data);

        $assessmentForm = AssessmentForm::create($data);

        return redirect()
            ->route('assessment-forms.index')
            ->with('success', 'Assessment form uploaded successfully!');

    } catch (\Exception $e) {
        Log::error('Error creating assessment form: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'Failed to upload assessment form: ' . $e->getMessage());
    }
}

    /**
     * Display the specified assessment form
     */
    public function show($id)
    {
        $assessmentForm = AssessmentForm::with([
            'staff', 
            'studentClass', 
            'academicYear', 
            'term', 
            'subject',
            'creator'
        ])->findOrFail($id);

        // Increment views
        $assessmentForm->incrementViews();

        return view('assessment-forms.show', compact('assessmentForm'));
    }

    /**
     * Show the form for editing the specified assessment form
     */
    public function edit($id)
    {
        $assessmentForm = AssessmentForm::findOrFail($id);
        

        $staff = Staff::all();
        $classes = StudentClass::all();
        $academicYears = AcademicYear::all();
        $terms = Term::all();
        $subjects = Subject::all();

        return view('assessment-forms.edit', compact('assessmentForm', 'staff', 'classes', 'academicYears', 'terms', 'subjects'));
    }

    /**
     * Update the specified assessment form
     */
    public function update(Request $request, $id)
    {
        $assessmentForm = AssessmentForm::findOrFail($id);
    
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'staff_id' => 'required|exists:staff,id',
            'student_class_id' => 'required|exists:student_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'assessment_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:assessment_date',
            'assessment_type' => 'required|in:quiz,test,exam,assignment,project',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,doc,docx|max:20480',
            'status' => 'nullable|in:draft,published,archived',
            'remove_file' => 'nullable|boolean',
        ]);
    
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
    
        try {
    
            $data = $validator->validated();
    
            if ($request->hasFile('file')) {
    
                if ($assessmentForm->file_path) {
                    $this->deleteFile($assessmentForm->file_path);
                }
    
                $file = $request->file('file');
    
                $filename = time().'_'.Str::slug(pathinfo(
                    $file->getClientOriginalName(),
                    PATHINFO_FILENAME
                )).'.'.$file->getClientOriginalExtension();
    
                $path = $file->storeAs(
                    'assessment-forms/'.date('Y/m'),
                    $filename,
                    'public'
                );
    
                $data['file_path'] = $path;
                $data['file_name'] = $file->getClientOriginalName();
                $data['file_type'] = $file->getClientOriginalExtension();
                $data['file_size'] = $file->getSize();
                $data['file_mime'] = $file->getMimeType();
            }
    
            if ($request->boolean('remove_file')) {
    
                $this->deleteFile($assessmentForm->file_path);
    
                $data['file_path'] = null;
                $data['file_name'] = null;
                $data['file_type'] = null;
                $data['file_size'] = null;
                $data['file_mime'] = null;
            }
    
            // Only if updated_by exists
            // $data['updated_by'] = Auth::id();
    
            $assessmentForm->update($data);
    
            return redirect()
                ->route('assessment-forms.show', $assessmentForm)
                ->with('success', 'Assessment form updated successfully.');
    
        } catch (\Exception $e) {
    
            dd($e);
    
        }
        return view('assessment-forms.edit', compact('assessmentForm', 'staff', 'classes', 'academicYears', 'terms', 'subjects'));
    }

    /**
     * Remove the specified assessment form
     */
    public function destroy($id)
    {
        try {
            $assessmentForm = AssessmentForm::findOrFail($id);
            $this->checkAuthorization($assessmentForm);

            // Delete file
            if ($assessmentForm->file_path) {
                $this->deleteFile($assessmentForm->file_path);
            }

            $assessmentForm->delete();

            Log::info('Assessment form deleted', [
                'id' => $assessmentForm->id,
                'file_name' => $assessmentForm->file_name,
                'deleted_by' => Auth::id()
            ]);

            return redirect()
                ->route('assessment-forms.index')
                ->with('success', 'Assessment form deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Error deleting assessment form: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Failed to delete assessment form: ' . $e->getMessage());
        }
    }

    /**
     * Download the assessment form file
     */
    public function download($id)
    {
        $assessmentForm = AssessmentForm::findOrFail($id);

        if (!$assessmentForm->file_path) {
            abort(404, 'File not found.');
        }

        if (!Storage::disk('public')->exists($assessmentForm->file_path)) {
            abort(404, 'File not found.');
        }

        $assessmentForm->incrementDownloads();

        return Storage::disk('public')->download(
            $assessmentForm->file_path,
            $assessmentForm->file_name,
            [
                'Content-Type' => $assessmentForm->file_mime ?? 'application/octet-stream',
            ]
        );
    }

    /**
     * Stream/view the file
     */
    public function view($id)
    {
        $assessmentForm = AssessmentForm::findOrFail($id);

        if (!$assessmentForm->file_path) {
            abort(404, 'File not found.');
        }

        if (!Storage::disk('public')->exists($assessmentForm->file_path)) {
            abort(404, 'File not found.');
        }

        $file = Storage::disk('public')->get($assessmentForm->file_path);
        $mime = $assessmentForm->file_mime ?? Storage::disk('public')->mimeType($assessmentForm->file_path);
        
        return response($file, 200)
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', 'inline; filename="' . $assessmentForm->file_name . '"');
    }

    /**
     * Toggle status (publish/unpublish)
     */
    public function toggleStatus($id)
    {
        try {
            $assessmentForm = AssessmentForm::findOrFail($id);
            $this->checkAuthorization($assessmentForm);

            if ($assessmentForm->status === 'published') {
                $assessmentForm->archive();
                $message = 'Assessment form archived successfully!';
            } else {
                $assessmentForm->publish();
                $message = 'Assessment form published successfully!';
            }

            return redirect()
                ->route('assessment-forms.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Error toggling status: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Failed to toggle status: ' . $e->getMessage());
        }
    }

    /**
     * Check authorization
     */


    /**
     * Delete file from storage
     */
    private function deleteFile($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            return true;
        }
        return false;
    }
}