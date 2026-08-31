<?php

namespace App\Http\Controllers;

use App\Models\Timetable;
use App\Models\AcademicYear;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TimetableController extends Controller
{
    /**
     * Display all timetables.
     */
    public function index()
    {
        $timetables = Timetable::with([
            'academicYear',
            'studentClass'
        ])
        ->latest()
        ->paginate(20);

        return view('timetables.index', compact('timetables'));
    }

    /**
     * Show upload form.
     */
    public function create()
    {
        $academicYears = AcademicYear::all();
        $classes = StudentClass::all();

        return view(
            'timetables.create',
            compact(
                'academicYears',
                'classes'
            )
        );
    }

    /**
     * Store a new timetable.
     */
    public function store(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'student_class_id' => 'required|exists:student_classes,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,xlsx,xls|max:10240',
        ]);

        $file = $request->file('file');

        /*
        |--------------------------------------------------------------------------
        | Store file on the public disk
        |--------------------------------------------------------------------------
        */
        $path = $file->store('timetables', 'public');

        if (!$path) {
            return back()
                ->withInput()
                ->with('error', 'The timetable file could not be uploaded.');
        }

        /*
        |--------------------------------------------------------------------------
        | Save timetable record
        |--------------------------------------------------------------------------
        */
        Timetable::create([
            'academic_year_id' => $request->academic_year_id,
            'student_class_id' => $request->student_class_id,
            'uploaded_by' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => strtolower($file->getClientOriginalExtension()),
            'file_size' => $file->getSize(),
            'status' => 'active',
        ]);

        return redirect()
            ->route('timetables.index')
            ->with('success', 'Timetable uploaded successfully.');
    }

    /**
     * Display timetable details.
     */
    public function show(Timetable $timetable)
    {
        $timetable->load([
            'academicYear',
            'studentClass'
        ]);

        return view(
            'timetables.show',
            compact('timetable')
        );
    }

    /**
     * Preview timetable file in browser.
     */
    public function preview(Timetable $timetable)
    {
        if (!$timetable->file_path) {
            abort(404, 'Timetable file path is missing.');
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($timetable->file_path)) {
            abort(404, 'Timetable file does not exist.');
        }

        $path = $disk->path($timetable->file_path);

        return response()->file($path);
    }

    /**
     * Download timetable file.
     */
    public function download(Timetable $timetable)
    {
        if (!$timetable->file_path) {
            abort(404, 'Timetable file path is missing.');
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($timetable->file_path)) {
            abort(404, 'Timetable file does not exist.');
        }

        return $disk->download(
            $timetable->file_path,
            $timetable->file_name
        );
    }

    /**
     * Delete timetable.
     */
    public function destroy(Timetable $timetable)
    {
        if ($timetable->file_path) {
            Storage::disk('public')->delete(
                $timetable->file_path
            );
        }

        $timetable->delete();

        return redirect()
            ->route('timetables.index')
            ->with('success', 'Timetable deleted successfully.');
    }
}