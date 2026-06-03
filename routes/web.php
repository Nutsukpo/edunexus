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
use App\Http\Controllers\StudentClassAssignmentController;
use App\Http\Controllers\AttendanceSettingController;
use App\Http\Controllers\StaffAttendanceController;
use App\Http\Controllers\FeeCategoryController;
use App\Http\Controllers\SchoolFeeStructureController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\StudentProgressionController;

Route::redirect('/', '/login');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.submit');

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Staff Routes
|--------------------------------------------------------------------------
*/

Route::resource('staff', StaffController::class);

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Logout Route
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');


// Users Resource Routes
Route::resource('users', UserController::class);

// Toggle User Status
Route::patch(
    'users/{user}/toggle-status',
    [UserController::class, 'toggleStatus']
)->name('users.toggle-status');



/*
    |--------------------------------------------------------------------------
    | Departments Module
    |--------------------------------------------------------------------------
*/
    Route::resource('departments', DepartmentController::class);
/*
    |--------------------------------------------------------------------------
    | Academic -year Module
    |--------------------------------------------------------------------------
*/
    Route::resource('academic-years', AcademicYearController::class);

/*


*/
Route::resource('billing', BillingController::class);

/*

    |--------------------------------------------------------------------------
    | Academic -terms Module
    |--------------------------------------------------------------------------
*/

    Route::resource('terms', TermController::class);

/*
    |--------------------------------------------------------------------------
    | Student_classes Module
    |--------------------------------------------------------------------------
*/
    Route::resource('student-classes', StudentClassController::class);

/*
    |--------------------------------------------------------------------------
    | Student_ Module
    |--------------------------------------------------------------------------
*/
    Route::resource('students', StudentController::class);

/*
    |--------------------------------------------------------------------------
    | Student_ Module
    |--------------------------------------------------------------------------
*/
    Route::resource('enrollments', EnrollmentController::class);

    Route::delete(
        '/classes/{class}/students/{student}',
        [StudentClassAssignmentController::class, 'destroy']
    )->name('classes.students.remove');
    

/*
    |--------------------------------------------------------------------------
    | Subject_ Module
    |--------------------------------------------------------------------------
*/
    Route::resource('subjects', SubjectController::class);
    Route::post('/classes/{class}/subjects', [StudentClassController::class, 'attachSubject'])
    ->name('classes.subject.attach');

    Route::delete('/classes/{class}/subjects/{subject}', [StudentClassController::class, 'detachSubject'])
    ->name('classes.subject.detach');

/*
    |--------------------------------------------------------------------------
    | staff/subject_ Module
    |--------------------------------------------------------------------------
*/


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
    | Attendance_ Module
    |--------------------------------------------------------------------------
*/

    Route::resource(
        'attendance-sessions',
        AttendanceSessionController::class
    );

    Route::get(
        '/get-class-students/{classId}',
        [StudentController::class, 'getClassStudents']
    )->name('class.students');

    Route::get('/get-class-students/{classId}', [StudentController::class, 'getClassStudents']);


    Route::get(
        '/attendance/class/{classId}/students',
        [AttendanceSessionController::class, 'getStudents']
    )->name('attendance.class.students');



    Route::resource(
        'student-class-assignments',
        StudentClassAssignmentController::class
    );


        // In routes/web.php
    Route::get('/student-classes/{studentClass}/attendance-data', [StudentClassController::class, 'getAttendanceData'])
        ->name('student-classes.attendance-data');

    // Existing take attendance route
    Route::get('/attendance/create/{studentClassId}', [AttendanceSessionController::class, 'createForClass'])
        ->name('attendance.create-for-class');


      

    // Route for taking attendance for a specific class
    Route::get('/attendance/create-for-class/{studentClassId}', [AttendanceSessionController::class, 'createForClass'])
        ->name('attendance.create-for-class');

    Route::get('/attendance/check-exists', [AttendanceSessionController::class, 'checkExists'])
    ->name('attendance.check-exists');


    Route::get(
        '/student-classes/{id}/attendance-data',
        [StudentClassController::class, 'attendanceData']
    );

    Route::resource(
        'student-class-assignments',
        StudentClassAssignmentController::class
    );

    Route::get('/student-classes/{class}/attendance-data', [StudentClassController::class, 'getAttendanceData'])
    ->name('student-classes.attendance-data');

    Route::resource('student-class-assignments', StudentClassAssignmentController::class);


    Route::get('/classes/{class}/attendance-dashboard', 
    [StudentClassController::class, 'attendanceDashboard']
    )->name('classes.attendance.dashboard');




    Route::resource('staff-attendance', StaffAttendanceController::class);

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
        'staff-attendance/live-map',
        [StaffAttendanceController::class, 'liveMap']
    )->name('staff-attendance.live-map');


    Route::resource(
        'attendance-settings',
        AttendanceSettingController::class
    );


    Route::resource('staffattendance', StaffAttendanceController::class);

    Route::get('staffattendance-dashboard', [StaffAttendanceController::class, 'dashboard']);
    Route::get('staffattendance-history/{staff}', [StaffAttendanceController::class, 'history']);
    Route::get('staffattendance-report', [StaffAttendanceController::class, 'report']);
    Route::get('staffattendance-live-map', [StaffAttendanceController::class, 'liveMap']);

    Route::post('/staff/clock-in', [StaffAttendanceController::class, 'clockIn'])
    ->name('staffattendance.clock-in');

    Route::post('/staff/clock-out', [StaffAttendanceController::class, 'clockOut'])
    ->name('staffattendance.clock-out');

    Route::get('/staff-attendance/live-locations', [StaffAttendanceController::class, 'liveLocations'])
    ->name('staffattendance.live-locations');

    //*Fee Category

    Route::resource('fee-categories', FeeCategoryController::class);
    // School Fee Structures Routes
    Route::prefix('school-fee-structures')->name('school-fee-structures.')->group(function () {
    Route::get('/', [SchoolFeeStructureController::class, 'index'])->name('index');
    Route::get('/create', [SchoolFeeStructureController::class, 'create'])->name('create');
    Route::post('/', [SchoolFeeStructureController::class, 'store'])->name('store');
    Route::get('/{schoolFeeStructure}', [SchoolFeeStructureController::class, 'show'])->name('show');
    Route::get('/{schoolFeeStructure}/edit', [SchoolFeeStructureController::class, 'edit'])->name('edit');
    Route::put('/{schoolFeeStructure}', [SchoolFeeStructureController::class, 'update'])->name('update');
    Route::delete('/{schoolFeeStructure}', [SchoolFeeStructureController::class, 'destroy'])->name('destroy');


        // School Fee Structures Routes
    Route::prefix('school-fee-structures')->name('school-fee-structures.')->group(function () {
    // School fee related routes only
    Route::resource('fee-structures', SchoolFeeStructureController::class);
});

// Student Progressions Routes (separate, not nested)
    Route::prefix('student-progressions')->name('student-progressions.')->group(function () {
    Route::get('/', [StudentProgressionController::class, 'index'])->name('index');
    Route::post('/process', [StudentProgressionController::class, 'process'])->name('process');
});
        
});
    

});
