<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Staff;
use App\Models\StudentClass;

class DashboardController extends Controller
{
    public function index()
{
    /*
    |--------------------------------------------------------------------------
    | TOTAL COUNTS
    |--------------------------------------------------------------------------
    */

    $totalStudents = Student::count();

    $totalStaff = Staff::count();

    $totalClasses = StudentClass::count();

    $activeClasses = StudentClass::count();

    /*
    |--------------------------------------------------------------------------
    | STUDENT COLLECTION
    |--------------------------------------------------------------------------
    */

    $students = Student::all();

    /*
    |--------------------------------------------------------------------------
    | GENDER COUNTS
    |--------------------------------------------------------------------------
    */

    $maleCount = Student::where('gender', 'Male')->count();

    $femaleCount = Student::where('gender', 'Female')->count();

    /*
    |--------------------------------------------------------------------------
    | MONTHLY STATS
    |--------------------------------------------------------------------------
    */

    $studentsThisMonth = Student::whereMonth(
        'created_at',
        now()->month
    )->count();

    $staffThisMonth = Staff::whereMonth(
        'created_at',
        now()->month
    )->count();

    /*
    |--------------------------------------------------------------------------
    | RETURN VIEW
    |--------------------------------------------------------------------------
    */

    return view('dashboard.index', compact(

        'totalStudents',
        'totalStaff',
        'totalClasses',
        'activeClasses',

        'students',

        'maleCount',
        'femaleCount',

        'studentsThisMonth',
        'staffThisMonth'

    ));
}
}