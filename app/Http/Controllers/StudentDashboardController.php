<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\AttendanceRecord;
use App\Models\StudentClassAssignment;
use App\Models\StudentInvoice;
use App\Models\StudentResult;
use App\Models\Term;
use App\Models\TimetablePdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class StudentDashboardController extends Controller
{
    /**
     * Student Dashboard
     */
    public function index()
    {
        $student = Auth::guard('student')->user();

        // Current Class Assignment - Use is_current instead of is_active
        $assignment = StudentClassAssignment::with('studentClass')
                        ->where('student_id', $student->id)
                        ->where('is_current', true)
                        ->first();

        // Academic Year & Term - Check if columns exist first
        $academicYear = null;
        $term = null;
        
        // Check if is_active column exists in academic_years table
        if (Schema::hasColumn('academic_years', 'is_active')) {
            $academicYear = AcademicYear::where('is_active', true)->first();
        } else {
            $academicYear = AcademicYear::latest()->first();
        }
      

        // Attendance
        $attendance = AttendanceRecord::where('student_id', $student->id);
        $present = (clone $attendance)->where('status', 'present')->count();
        $absent = (clone $attendance)->where('status', 'absent')->count();
        $late = (clone $attendance)->where('status', 'late')->count();
        $excused = (clone $attendance)->where('status', 'excused')->count();
        $totalAttendance = (clone $attendance)->count();
        $attendanceRate = $totalAttendance > 0 ? round(($present / $totalAttendance) * 100) : 0;

        // Fee Balance
        $feeBalance = StudentInvoice::where('student_id', $student->id)->sum('balance');

        // Subjects Count
        $subjects = $assignment ? $assignment->studentClass->subjects()->count() : 0;

        // Recent Results
        $recentResults = StudentResult::where('student_id', $student->id)
                            ->latest()
                            ->take(5)
                            ->get();

        // Get announcements
        $announcements = Announcement::published()
            ->where(function($query) {
                $query->where('audience', 'all')
                      ->orWhere('audience', 'students');
            })
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        $featuredAnnouncement = Announcement::published()
            ->featured()
            ->where(function($query) {
                $query->where('audience', 'all')
                      ->orWhere('audience', 'students');
            })
            ->first();

        return view('students.dashboard', compact(
            'student',
            'assignment',
            'academicYear',
            'term',
            'attendanceRate',
            'feeBalance',
            'subjects',
            'recentResults',
            'announcements',
            'featuredAnnouncement',
            'present',
            'absent',
            'late',
            'excused',
            'totalAttendance'
        ));
    }

    /**
     * Student Timetable - Display PDF Timetable
     */
    public function timetable()
    {
        $student = Auth::guard('student')->user();
        
        // Get the student's class ID
        $classId = $student->class_id ?? null;
        
        if (!$classId) {
            return view('students.timetable', [
                'student' => $student,
                'timetable' => null,
                'availableTimetables' => collect(),
                'classId' => null,
                'academicYear' => null,
                'term' => null,
                'message' => 'You are not assigned to any class yet. Please contact your administrator.'
            ]);
        }
        
        // Get current academic year and term safely
        $academicYear = null;
        $academicYearId = null;
        
        // Check if is_active column exists
        if (Schema::hasColumn('academic_years', 'is_active')) {
            $academicYear = AcademicYear::where('is_active', true)->first();
        } else {
            $academicYear = AcademicYear::latest()->first();
        }
        $academicYearId = $academicYear ? $academicYear->id : null;
        
        $term = null;
        $termId = null;
        
        // Check if is_active column exists in terms
        if (Schema::hasColumn('terms', 'is_active')) {
            $term = Term::where('is_active', true)->where('is_current', true)->first();
        } else {
            $term = Term::where('is_current', true)->first();
        }
        $termId = $term ? $term->id : null;
        
        // Get the timetable PDF for the student's class
        $timetable = null;
        
        try {
            // Check if TimetablePdf model exists
            if (class_exists('App\Models\TimetablePdf')) {
                $query = TimetablePdf::where('class_id', $classId);
                
                // Check if is_active column exists in timetable_pdfs table
                if (Schema::hasColumn('timetable_pdfs', 'is_active')) {
                    $query->where('is_active', true);
                }
                
                if ($academicYearId) {
                    $query->where('academic_year_id', $academicYearId);
                }
                
                if ($termId) {
                    $query->where('term_id', $termId);
                }
                
                $timetable = $query->first();
                
                // If no timetable found for current term, try to get any active timetable for the class
                if (!$timetable) {
                    $fallbackQuery = TimetablePdf::where('class_id', $classId);
                    if (Schema::hasColumn('timetable_pdfs', 'is_active')) {
                        $fallbackQuery->where('is_active', true);
                    }
                    $timetable = $fallbackQuery->first();
                }
            }
        } catch (\Exception $e) {
            // If there's an error, try a simpler query
            try {
                $query = TimetablePdf::where('class_id', $classId);
                if (Schema::hasColumn('timetable_pdfs', 'is_active')) {
                    $query->where('is_active', true);
                }
                $timetable = $query->first();
            } catch (\Exception $ex) {
                $timetable = null;
            }
        }
        
        // Get all available timetables for this class (for dropdown/switch)
        $availableTimetables = collect();
        try {
            if (class_exists('App\Models\TimetablePdf')) {
                $query = TimetablePdf::where('class_id', $classId);
                if (Schema::hasColumn('timetable_pdfs', 'is_active')) {
                    $query->where('is_active', true);
                }
                $availableTimetables = $query->orderBy('created_at', 'desc')->get();
            }
        } catch (\Exception $e) {
            $availableTimetables = collect();
        }
        
        return view('students.timetable', compact(
            'student',
            'timetable',
            'availableTimetables',
            'classId',
            'academicYear',
            'term'
        ));
    }

    /**
     * View a specific timetable PDF via AJAX
     */
    public function viewTimetable($id)
    {
        try {
            $student = Auth::guard('student')->user();
            $timetable = TimetablePdf::with(['class', 'academicYear', 'term'])
                ->findOrFail($id);
            
            // Verify the student belongs to the class
            if ($timetable->class_id != $student->class_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this timetable.'
                ], 403);
            }
            
            return response()->json([
                'success' => true,
                'timetable' => $timetable,
                'url' => $timetable->full_url ?? null,
                'file_name' => $timetable->file_name,
                'formatted_size' => $timetable->formatted_file_size ?? 'N/A',
                'is_current' => $timetable->isCurrent()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load timetable: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download the timetable PDF
     */
    public function downloadTimetable($id)
    {
        try {
            $student = Auth::guard('student')->user();
            $timetable = TimetablePdf::findOrFail($id);
            
            // Verify permission
            if ($timetable->class_id != $student->class_id) {
                abort(403, 'You do not have permission to download this timetable.');
            }
            
            $filePath = storage_path('app/public/' . $timetable->file_path);
            
            if (!file_exists($filePath)) {
                abort(404, 'Timetable file not found.');
            }
            
            return response()->download($filePath, $timetable->file_name);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to download timetable: ' . $e->getMessage());
        }
    }

    /**
     * Stream the PDF for viewing inline
     */
    public function streamTimetable($id)
    {
        try {
            $student = Auth::guard('student')->user();
            $timetable = TimetablePdf::findOrFail($id);
            
            // Verify permission
            if ($timetable->class_id != $student->class_id) {
                abort(403, 'You do not have permission to view this timetable.');
            }
            
            $filePath = storage_path('app/public/' . $timetable->file_path);
            
            if (!file_exists($filePath)) {
                abort(404, 'Timetable file not found.');
            }
            
            return response()->file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $timetable->file_name . '"'
            ]);
        } catch (\Exception $e) {
            abort(404, 'Timetable not found: ' . $e->getMessage());
        }
    }

    /**
     * Get timetable information for AJAX
     */
    public function getTimetableInfo($id)
    {
        try {
            $student = Auth::guard('student')->user();
            $timetable = TimetablePdf::findOrFail($id);
            
            if ($timetable->class_id != $student->class_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            return response()->json([
                'success' => true,
                'id' => $timetable->id,
                'file_name' => $timetable->file_name,
                'file_size' => $timetable->formatted_file_size ?? 'N/A',
                'upload_date' => $timetable->created_at ? $timetable->created_at->format('M d, Y') : 'N/A',
                'description' => $timetable->description,
                'is_current' => $timetable->isCurrent(),
                'effective_from' => $timetable->effective_from ? $timetable->effective_from->format('M d, Y') : null,
                'effective_to' => $timetable->effective_to ? $timetable->effective_to->format('M d, Y') : null,
                'class_name' => $timetable->class->name ?? 'N/A',
                'term_name' => $timetable->term->name ?? 'N/A',
                'academic_year' => $timetable->academicYear->name ?? 'N/A'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get timetable info: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Switch between different timetable versions
     */
    public function switchTimetable(Request $request)
    {
        try {
            $student = Auth::guard('student')->user();
            $timetableId = $request->timetable_id;
            
            $timetable = TimetablePdf::findOrFail($timetableId);
            
            // Verify permission
            if ($timetable->class_id != $student->class_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this timetable.'
                ], 403);
            }
            
            return response()->json([
                'success' => true,
                'timetable' => $timetable,
                'url' => $timetable->full_url ?? null,
                'file_name' => $timetable->file_name
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to switch timetable: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Student Profile
     */
    public function profile()
    {
        $student = Auth::guard('student')->user();
        return view('students.profile', compact('student'));
    }

    /**
     * Student Attendance
     */
    public function attendance()
    {
        $student = Auth::guard('student')->user();
        
        $attendanceRecords = AttendanceRecord::where('student_id', $student->id)
                            ->latest()
                            ->paginate(20);
        
        $summary = [
            'present' => AttendanceRecord::where('student_id', $student->id)->where('status', 'present')->count(),
            'absent' => AttendanceRecord::where('student_id', $student->id)->where('status', 'absent')->count(),
            'late' => AttendanceRecord::where('student_id', $student->id)->where('status', 'late')->count(),
            'excused' => AttendanceRecord::where('student_id', $student->id)->where('status', 'excused')->count(),
        ];
        
        return view('students.attendance', compact('student', 'attendanceRecords', 'summary'));
    }

    /**
     * Student Results
     */
    public function results()
    {
        $student = Auth::guard('student')->user();
        
        $results = StudentResult::where('student_id', $student->id)
                    ->with(['subject', 'term', 'academicYear'])
                    ->latest()
                    ->paginate(20);
        
        $summary = [
            'average_score' => StudentResult::where('student_id', $student->id)->avg('total_score') ?? 0,
            'highest_score' => StudentResult::where('student_id', $student->id)->max('total_score') ?? 0,
            'lowest_score' => StudentResult::where('student_id', $student->id)->min('total_score') ?? 0,
            'total_subjects' => StudentResult::where('student_id', $student->id)
                ->distinct('subject_id')
                ->count('subject_id'),
        ];
        
        return view('students.results', compact('student', 'results', 'summary'));
    }

    /**
     * Student Academic History
     */
    public function academicHistory()
    {
        $student = Auth::guard('student')->user();
        
        $history = StudentResult::where('student_id', $student->id)
                    ->with(['subject', 'term', 'academicYear'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(20);
        
        return view('students.academic-history', compact('student', 'history'));
    }

    /**
     * Student Class History
     */
    public function classHistory()
    {
        $student = Auth::guard('student')->user();
        
        $classHistory = StudentClassAssignment::with(['studentClass', 'academicYear'])
                        ->where('student_id', $student->id)
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);
        
        // Check which columns exist
        $hasEndDate = Schema::hasColumn('student_class_assignments', 'end_date');
        $hasStartDate = Schema::hasColumn('student_class_assignments', 'start_date');
        $hasAcademicYear = Schema::hasColumn('student_class_assignments', 'academic_year_id');
        $hasIsCurrent = Schema::hasColumn('student_class_assignments', 'is_current');
        
        $totalClasses = StudentClassAssignment::where('student_id', $student->id)->count();
        
        $currentClasses = 0;
        if ($hasIsCurrent) {
            $currentClasses = StudentClassAssignment::where('student_id', $student->id)
                                ->where('is_current', true)
                                ->count();
        }
        
        $completedClasses = 0;
        if ($hasEndDate) {
            $completedClasses = StudentClassAssignment::where('student_id', $student->id)
                                ->whereNotNull('end_date')
                                ->where('end_date', '<', now())
                                ->count();
        }
        
        $totalYears = 0;
        if ($hasAcademicYear) {
            $totalYears = StudentClassAssignment::where('student_id', $student->id)
                            ->distinct('academic_year_id')
                            ->count('academic_year_id');
        }
        
        $summary = [
            'total_classes' => $totalClasses,
            'current_class' => $currentClasses,
            'completed_classes' => $completedClasses,
            'total_years' => $totalYears,
            'has_dates' => $hasStartDate && $hasEndDate,
        ];
        
        return view('students.class-history', compact('student', 'classHistory', 'summary'));
    }

    /**
     * Student Fees
     */
    public function fees()
    {
        $student = Auth::guard('student')->user();
        
        $invoices = StudentInvoice::where('student_id', $student->id)
                    ->latest()
                    ->paginate(20);
        
        $summary = [
            'total_amount' => StudentInvoice::where('student_id', $student->id)->sum('amount'),
            'total_paid' => StudentInvoice::where('student_id', $student->id)->sum('paid'),
            'total_balance' => StudentInvoice::where('student_id', $student->id)->sum('balance'),
            'total_invoices' => StudentInvoice::where('student_id', $student->id)->count(),
            'paid_invoices' => StudentInvoice::where('student_id', $student->id)->where('balance', 0)->count(),
        ];
        
        return view('students.fees', compact('student', 'invoices', 'summary'));
    }

    /**
     * Student Settings
     */
    public function settings()
    {
        $student = Auth::guard('student')->user();
        return view('students.settings', compact('student'));
    }

    /**
     * Update Student Profile
     */
    public function updateProfile(Request $request)
    {
        $student = Auth::guard('student')->user();
        
        $validated = $request->validate([
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:students,email,' . $student->id,
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('student_photos', 'public');
            $validated['photo'] = $path;
        }
        
        $student->update($validated);
        
        return redirect()->route('students.profile')
                ->with('success', 'Profile updated successfully!');
    }

    /**
     * Get class history API
     */
    public function getClassHistoryApi()
    {
        $student = Auth::guard('student')->user();
        
        $history = StudentClassAssignment::with(['studentClass', 'academicYear'])
                    ->where('student_id', $student->id)
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($assignment) {
                        return [
                            'class_name' => $assignment->studentClass->name ?? 'N/A',
                            'academic_year' => $assignment->academicYear->name ?? 'N/A',
                            'status' => $assignment->is_current ? 'Current' : 'Completed',
                            'start_date' => $assignment->start_date ? $assignment->start_date->format('M d, Y') : 'N/A',
                            'end_date' => $assignment->end_date ? $assignment->end_date->format('M d, Y') : 'N/A',
                        ];
                    });
        
        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Get class performance API
     */
    public function getClassPerformanceApi()
    {
        $student = Auth::guard('student')->user();
        
        $assignments = StudentClassAssignment::with(['studentClass'])
                        ->where('student_id', $student->id)
                        ->get();
        
        $performance = [];
        
        foreach ($assignments as $assignment) {
            $results = StudentResult::where('student_id', $student->id)
                        ->where('student_class_id', $assignment->student_class_id)
                        ->get();
            
            $performance[] = [
                'class_name' => $assignment->studentClass->name ?? 'N/A',
                'average_score' => $results->avg('total_score') ?? 0,
                'total_subjects' => $assignment->studentClass->subjects()->count() ?? 0,
                'status' => $assignment->is_current ? 'Current' : 'Completed',
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => $performance,
        ]);
    }
}