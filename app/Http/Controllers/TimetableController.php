<?php

namespace App\Http\Controllers;

use App\Models\Timetable;
use App\Models\AcademicYear;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TimetableController extends Controller
{
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

    public function store(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required',
            'student_class_id' => 'required',
            'title' => 'required|string|max:255',
            'file' => 'required|mimes:pdf,jpg,jpeg,png,xlsx,xls|max:10240',
        ]);

        $file = $request->file('file');

        $path = $file->store(
            'timetables',
            'public'
        );

        Timetable::create([
            'academic_year_id' => $request->academic_year_id,
            'student_class_id' => $request->student_class_id,
            'uploaded_by' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'status' => 'active',
        ]);

        return redirect()
            ->route('timetables.index')
            ->with('success', 'Timetable uploaded successfully.');
    }

    public function show(Timetable $timetable)
    {
        return view(
            'timetables.show',
            compact('timetable')
        );
    }

    public function destroy(Timetable $timetable)
    {
        Storage::disk('public')
            ->delete($timetable->file_path);

        $timetable->delete();

        return back()
            ->with('success', 'Timetable deleted.');
    }

    public function download(Timetable $timetable)
    {
        return Storage::disk('public')
            ->download(
                $timetable->file_path,
                $timetable->file_name
            );
    }
}