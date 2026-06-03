<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\TermController;
use App\Http\Controllers\StudentClassController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\AttendanceSessionController;
use App\Http\Controllers\AttendanceSettingController;
use App\Http\Controllers\StaffAttendanceController;
use App\Http\Controllers\StudentClassAssignmentController;
use App\Http\Controllers\FeeCategoryController;
use App\Http\Controllers\SchoolFeeStructureController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StudentProgressionController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\SubjectResultController;
use App\Http\Controllers\BroadsheetController;
use App\Http\Controllers\TimetableController;

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/login');

Route::get('/login', fn () => view('auth.login'))
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.submit');

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    /*
    |--------------------------------------------------------------------------
    | Administration
    |--------------------------------------------------------------------------
    */

    Route::resource('users', UserController::class);

    Route::patch(
        'users/{user}/toggle-status',
        [UserController::class, 'toggleStatus']
    )->name('users.toggle-status');

    Route::resource('departments', DepartmentController::class);

    /*
    |--------------------------------------------------------------------------
    | Academic Setup
    |--------------------------------------------------------------------------
    */

    Route::resource('academic-years', AcademicYearController::class);
    Route::resource('terms', TermController::class);
    Route::resource('subjects', SubjectController::class);
    Route::resource('student-classes', StudentClassController::class);

      
    /*
    |--------------------------------------------------------------------------
    |Staff
    |--------------------------------------------------------------------------
    */
    Route::resource('staff', StaffController::class);

    /*
    |--------------------------------------------------------------------------
    | Students
    |--------------------------------------------------------------------------
    */

    Route::resource('students', StudentController::class);
    Route::resource('enrollments', EnrollmentController::class);

    Route::resource(
        'student-class-assignments',
        StudentClassAssignmentController::class
    );

    Route::delete(
        '/classes/{class}/students/{student}',
        [StudentClassAssignmentController::class, 'destroy']
    )->name('classes.students.remove');

    /*
    |--------------------------------------------------------------------------
    | Class Management
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/classes/{class}/subjects',
        [StudentClassController::class, 'attachSubject']
    )->name('classes.subject.attach');

    Route::delete(
        '/classes/{class}/subjects/{subject}',
        [StudentClassController::class, 'detachSubject']
    )->name('classes.subject.detach');

    Route::post(
        '/student-classes/{studentClass}/assign-subject-teacher',
        [StudentClassController::class, 'assignSubjectTeacher']
    )->name('student-classes.assign-subject-teacher');

    Route::delete(
        '/student-classes/{studentClass}/remove-subject-teacher/{subject}',
        [StudentClassController::class, 'removeSubjectTeacher']
    )->name('student-classes.remove-subject-teacher');

    Route::post(
        '/student-classes/{studentClass}/assign-prefect',
        [StudentClassController::class, 'assignPrefect']
    )->name('student-classes.assign-prefect');

    /*
    |--------------------------------------------------------------------------
    | Attendance
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'attendance-sessions',
        AttendanceSessionController::class
    );

    Route::resource(
        'attendance-settings',
        AttendanceSettingController::class
    );

    Route::get(
        '/attendance/create-for-class/{studentClassId}',
        [AttendanceSessionController::class, 'createForClass']
    )->name('attendance.create-for-class');

    Route::get(
        '/attendance/check-exists',
        [AttendanceSessionController::class, 'checkExists']
    )->name('attendance.check-exists');

    Route::get(
        '/attendance/class/{classId}/students',
        [AttendanceSessionController::class, 'getStudents']
    )->name('attendance.class.students');

    Route::get(
        '/student-classes/{class}/attendance-data',
        [StudentClassController::class, 'getAttendanceData']
    )->name('student-classes.attendance-data');

    Route::get(
        '/classes/{class}/attendance-dashboard',
        [StudentClassController::class, 'attendanceDashboard']
    )->name('classes.attendance.dashboard');

    /*
    |--------------------------------------------------------------------------
    | Staff Attendance
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'staff-attendance',
        StaffAttendanceController::class
    );

    Route::get(
        'staff-attendance-dashboard',
        [StaffAttendanceController::class, 'dashboard']
    )->name('staff-attendance.dashboard');

    Route::post(
        'staff-attendance/gps-clock-in',
        [StaffAttendanceController::class, 'gpsClockIn']
    )->name('staff-attendance.gps-clock-in');

    Route::post(
        'staff-attendance/gps-clock-out',
        [StaffAttendanceController::class, 'gpsClockOut']
    )->name('staff-attendance.gps-clock-out');

    Route::get(
        'staffattendance-live-map',
        [StaffAttendanceController::class, 'liveMap']
    )->name('staff-attendance.live-map');

    /*
    |--------------------------------------------------------------------------
    | Fee Setup
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'fee-categories',
        FeeCategoryController::class
    );

    Route::resource(
        'school-fee-structures',
        SchoolFeeStructureController::class
    );

    /*
    |--------------------------------------------------------------------------
    | Billing & Invoicing
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'billing',
        BillingController::class
    );

    Route::get(
        'billing/{billing}/pdf',
        [BillingController::class, 'pdf']
    )->name('billing.pdf');

    Route::get(
        'billing/{billing}/view-pdf',
        [BillingController::class, 'viewPdf']
    )->name('billing.view-pdf');

    /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'payments',
        PaymentController::class
    );

    Route::get(
        'payments/create/{invoice}',
        [PaymentController::class, 'create']
    )->name('payments.create');

    /*
    |--------------------------------------------------------------------------
    | Student Progression
    |--------------------------------------------------------------------------
    */
    

    Route::prefix('student-progressions')
        ->name('student-progressions.')
        ->group(function () {

            Route::get(
                '/',
                [StudentProgressionController::class, 'index']
            )->name('index');

            Route::post(
                '/process',
                [StudentProgressionController::class, 'process']
            )->name('process');
    });

        // Add this route for bulk promotion
        Route::post('/student-progressions/bulk-promote', [StudentProgressionController::class, 'bulkPromote'])->name('student-progressions.bulk-promote');

        Route::prefix('scores')->group(function () {

            Route::get('/', [ScoreController::class,'index'])
                ->name('scores.index');
        
            Route::post('/load-students', [ScoreController::class,'loadStudents'])
                ->name('scores.load-students');
        
            Route::post('/save', [ScoreController::class,'save'])
                ->name('scores.save');
        
        });

        Route::get('/subject-results',
        [SubjectResultController::class, 'index']
        )->name('subject-results.index');

        Route::get('/broadsheet', [BroadsheetController::class, 'index'])
            ->name('broadsheet.index');
        
        Route::post('/broadsheet/generate', [BroadsheetController::class, 'generate'])
            ->name('broadsheet.generate');
        Route::post(
            '/broadsheet/pdf',
            [BroadsheetController::class,'pdf']
            )->name('broadsheet.pdf');
        Route::post('/broadsheet/ajax', [BroadsheetController::class, 'ajaxLoad'])->name('broadsheet.ajax');

        Route::resource(
            'timetables',
            TimetableController::class
        );
        
        Route::get(
            'timetables/{timetable}/download',
            [TimetableController::class, 'download']
        )->name('timetables.download');
       
    
  
});