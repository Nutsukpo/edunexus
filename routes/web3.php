<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
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
use App\Http\Controllers\PayrollGenerationController;
use App\Http\Controllers\StudentProgressionController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\SubjectResultController;
use App\Http\Controllers\BroadsheetController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\ReportCardController;
use App\Http\Controllers\GraduationController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\LessonNoteController;
use App\Http\Controllers\AssessmentFormController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\LeavesController;
use App\Http\Controllers\StaffAppraisalController;
use App\Http\Controllers\DiscussionController;
use App\Http\Controllers\PayrollPeriodController;
use App\Http\Controllers\GrievanceController;
use App\Http\Controllers\StudentGrievanceController;
use App\Http\Controllers\FeeStructureController;
use App\Http\Controllers\AdminPaymentController;
use App\Http\Controllers\StudentFeeController;
use App\Http\Controllers\SalaryStructureController;
use App\Http\Controllers\BillSheetController;
use App\Http\Controllers\PayslipController;










/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/
Route::redirect('/', '/login');
Route::get('/health', function () {
    return response()->json(['status' => 'OK', 'timestamp' => now()]);
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATION ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/login/admin', [AuthController::class, 'showAdminLoginForm'])->name('admin.login');

/*
|--------------------------------------------------------------------------
| STUDENT PASSWORD CHANGE ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:student'])->group(function () {
    Route::get('/student/change-password', [AuthController::class, 'showPasswordChangeForm'])
        ->name('student.password.change.form');
    Route::post('/student/change-password', [AuthController::class, 'updatePassword'])
        ->name('student.password.change');
    Route::get('/student/dashboard', [StudentDashboardController::class, 'index'])->name('students.dashboard');
});

/*
|--------------------------------------------------------------------------
| ADMIN / STAFF ROUTES (Protected by auth:web)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:web'])->group(function () {

    // ============================================
    // DASHBOARD
    // ============================================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/attendance-data', [DashboardController::class, 'getAttendanceData'])->name('attendance-data');
        Route::get('/attendance-summary', [DashboardController::class, 'getAttendanceSummary'])->name('attendance.summary');
        Route::get('/class-attendance', [DashboardController::class, 'getClassAttendance'])->name('class-attendance');
    });

    // ============================================
    // ADMINISTRATION
    // ============================================
    Route::resource('users', UserController::class);
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::resource('departments', DepartmentController::class);

    // ============================================
    // ACADEMIC SETUP
    // ============================================
    Route::resource('academic-years', AcademicYearController::class);
    Route::resource('terms', TermController::class);
    Route::resource('subjects', SubjectController::class);
    Route::resource('student-classes', StudentClassController::class);

    // ============================================
    // STAFF
    // ============================================
    Route::resource('staff', StaffController::class);

    // ============================================
    // STUDENTS
    // ============================================
    Route::resource('students', StudentController::class);
    Route::resource('enrollments', EnrollmentController::class);
    Route::resource('student-class-assignments', StudentClassAssignmentController::class);
    Route::delete('/classes/{class}/students/{student}', [StudentClassAssignmentController::class, 'destroy'])
        ->name('classes.students.remove');

    ////Salary-structures
    Route::resource('salary-structures',SalaryStructureController::class );
    ///PayrollPeriodController
    Route::resource('payroll-periods',PayrollPeriodController::class);

    Route::post('payroll-periods/{payrollPeriod}/add-staff',[PayrollGenerationController::class,'addStaff'])->name('payroll.addStaff');
    Route::post('/payroll-periods/{id}/assign-staff', [PayrollPeriodController::class, 'assignStaff'])->name('payroll.assign-staff');
    Route::get('/payroll-periods/{id}/assign-staff', [PayrollPeriodController::class, 'assignStaffForm'])
    ->name('payroll.assign-staff-form');

            // Option A: Use the route name your view is expecting
    Route::get('/payroll-periods/{id}/assign-staff', [PayrollPeriodController::class, 'assignStaffForm'])->name('payroll.assign-staff.form');
    Route::post('/payroll-periods/{id}/assign-staff', [PayrollPeriodController::class, 'assignStaff'])->name('payroll.assign-staff.store');

    Route::resource('payroll-periods', PayrollPeriodController::class);

    // Custom routes for assigning staff - using names that match your view
    Route::get('/payroll-periods/{id}/assign-staff', [PayrollPeriodController::class, 'assignStaffForm'])->name('payroll.assign-staff');
    Route::post('/payroll-periods/{id}/assign-staff', [PayrollPeriodController::class, 'assignStaff'])->name('payroll.assign-staff.store');

                // Remove single staff from payroll period
    Route::delete('/payroll-periods/{id}/remove-staff', [PayrollPeriodController::class, 'removeStaff'])
    ->name('payroll-periods.remove-staff');

    // Or if you want a bulk removal route
    Route::delete('/payroll-periods/{id}/remove-staff-bulk', [PayrollPeriodController::class, 'removeStaffBulk'])
    ->name('payroll-periods.remove-staff-bulk');

    Route::delete('payroll-periods/{id}/remove-staff', [PayrollPeriodController::class, 'removeStaff'])
    ->name('payroll-periods.remove-staff');



        // ============================================
    // ANNOUNCEMENT ROUTES
    // ============================================
    // Main announcement resource routes (admin)
    Route::resource('announcements', AnnouncementController::class);
    
    // Additional announcement routes
    Route::post('/announcements/bulk-delete', [AnnouncementController::class, 'bulkDelete'])
        ->name('announcements.bulk-delete');
    Route::post('/announcements/bulk-expire', [AnnouncementController::class, 'bulkExpire'])
        ->name('announcements.bulk-expire');
    Route::patch('/announcements/{announcement}/toggle-status', [AnnouncementController::class, 'toggleStatus'])
        ->name('announcements.toggle-status');
    Route::patch('/announcements/{announcement}/toggle-featured', [AnnouncementController::class, 'toggleFeatured'])
        ->name('announcements.toggle-featured');
    Route::put('/announcements/{announcement}/expire', [AnnouncementController::class, 'expire'])
        ->name('announcements.expire');
    Route::put('/announcements/{announcement}/restore', [AnnouncementController::class, 'restore'])
        ->name('announcements.restore');

    // Public announcement routes (accessible without login)
    Route::get('/announcements/public', [AnnouncementController::class, 'publicIndex'])
        ->name('announcements.public');
    Route::get('/announcements', [AnnouncementController::class, 'publicIndex'])
        ->name('announcements.public');

        // Student Announcement Routes
    Route::middleware(['auth:student'])->prefix('student')->name('students.')->group(function () {
        Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('/announcements/{id}', [AnnouncementController::class, 'show'])->name('announcements.show');
        Route::post('/announcements/{id}/read', [AnnouncementController::class, 'markAsRead'])->name('announcements.read');
        Route::post('/announcements/mark-all-read', [AnnouncementController::class, 'markAllAsRead'])->name('announcements.mark-all-read');
        Route::get('/announcements/unread-count', [AnnouncementController::class, 'getUnreadCount'])->name('announcements.unread-count');
    });
    
  

       // Announcement Routes
       Route::resource('announcements', AnnouncementController::class);
       Route::post('/announcements/bulk-delete', [AnnouncementController::class, 'bulkDelete'])
           ->name('announcements.bulk-delete');
       Route::patch('/announcements/{announcement}/toggle-status', [AnnouncementController::class, 'toggleStatus'])
           ->name('announcements.toggle-status');
       Route::patch('/announcements/{announcement}/toggle-featured', [AnnouncementController::class, 'toggleFeatured'])
           ->name('announcements.toggle-featured');

       // Public Announcement Routes
       Route::get('/announcements/public', [AnnouncementController::class, 'publicIndex'])
           ->name('announcements.public');
       Route::get('/api/announcements', [AnnouncementController::class, 'getAnnouncements'])
           ->name('announcements.api');



    
    // API route for AJAX calls
    Route::get('/api/announcements', [AnnouncementController::class, 'getAnnouncements'])
        ->name('announcements.api');


    /*
    |--------------------------------------------------------------------------
    | Billing Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:web'])->prefix('admin/billing')->name('billing.')->group(function () {
        // Main CRUD
        Route::get('/', [BillingController::class, 'index'])->name('index');
        Route::get('/create', [BillingController::class, 'create'])->name('create');
        Route::post('/', [BillingController::class, 'store'])->name('store');
        Route::get('/{id}', [BillingController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [BillingController::class, 'edit'])->name('edit');
        Route::put('/{id}', [BillingController::class, 'update'])->name('update');
        Route::delete('/{id}', [BillingController::class, 'destroy'])->name('destroy');
        
        // Bill Actions
        Route::post('/{id}/send', [BillingController::class, 'send'])->name('send');
        Route::post('/{id}/approve', [BillingController::class, 'approve'])->name('approve');
        Route::post('/{id}/cancel', [BillingController::class, 'cancel'])->name('cancel');
        Route::post('/{id}/mark-viewed', [BillingController::class, 'markAsViewed'])->name('mark-viewed');
        
        // Recurring Billing
        Route::post('/{id}/generate-recurring', [BillingController::class, 'generateRecurring'])->name('generate-recurring');
        
        // PDF Routes
        Route::get('/{id}/download-pdf', [BillingController::class, 'downloadPdf'])->name('download-pdf');
        Route::get('/{id}/view-pdf', [BillingController::class, 'viewPdf'])->name('view-pdf');
        
        // API Routes
        Route::get('/get-student-data/{id}', [BillingController::class, 'getStudentData'])->name('get-student-data');
        Route::get('/get-fee-items', [BillingController::class, 'getFeeItems'])->name('get-fee-items');
        
        // Bulk Operations
        Route::post('/bulk-generate', [BillingController::class, 'bulkGenerate'])->name('bulk-generate');
    });



    
    Route::prefix('admin')->group(function () {
        // Billsheet routes with auth middleware
        Route::middleware(['auth'])->group(function () {
            Route::get('/billsheet', [App\Http\Controllers\BillsheetController::class, 'index'])->name('billsheet.index');
            Route::get('/billsheet/create', [App\Http\Controllers\BillsheetController::class, 'create'])->name('billsheet.create');
            Route::post('/billsheet', [App\Http\Controllers\BillsheetController::class, 'store'])->name('billsheet.store');
            Route::get('/billsheet/bulk-create', [App\Http\Controllers\BillsheetController::class, 'bulkCreate'])->name('billsheet.bulk-create');
            Route::post('/billsheet/bulk-generate', [App\Http\Controllers\BillsheetController::class, 'bulkGenerate'])->name('billsheet.bulk-generate');
            Route::get('/billsheet/student-data/{id}', [App\Http\Controllers\BillsheetController::class, 'getStudentData'])->name('billsheet.student-data');
            Route::get('/billsheet/{id}', [App\Http\Controllers\BillsheetController::class, 'show'])->name('billsheet.show');
            Route::get('/billsheet/{id}/edit', [App\Http\Controllers\BillsheetController::class, 'edit'])->name('billsheet.edit');
            Route::put('/billsheet/{id}', [App\Http\Controllers\BillsheetController::class, 'update'])->name('billsheet.update');
            Route::delete('/billsheet/{id}', [App\Http\Controllers\BillsheetController::class, 'destroy'])->name('billsheet.destroy');
            Route::post('/billsheet/{id}/send', [App\Http\Controllers\BillsheetController::class, 'send'])->name('billsheet.send');
            Route::post('/billsheet/{id}/approve', [App\Http\Controllers\BillsheetController::class, 'approve'])->name('billsheet.approve');
            Route::post('/billsheet/{id}/cancel', [App\Http\Controllers\BillsheetController::class, 'cancel'])->name('billsheet.cancel');
            Route::get('/billsheet/{id}/pdf', [App\Http\Controllers\BillsheetController::class, 'downloadPdf'])->name('billsheet.pdf');
            Route::get('/billsheet/{id}/view-pdf', [App\Http\Controllers\BillsheetController::class, 'viewPdf'])->name('billsheet.view-pdf');
            Route::get('/billsheet/{id}/generate-recurring', [App\Http\Controllers\BillsheetController::class, 'generateRecurring'])->name('billsheet.generate-recurring');
        });

        // School Fee Structures routes
        Route::resource('school-fee-structures', App\Http\Controllers\SchoolFeeStructureController::class);
        Route::post('/school-fee-structures/{id}/toggle', [App\Http\Controllers\SchoolFeeStructureController::class, 'toggleStatus'])->name('school-fee-structures.toggle');
        Route::post('/school-fee-structures/{id}/toggle-optional', [App\Http\Controllers\SchoolFeeStructureController::class, 'toggleOptional'])->name('school-fee-structures.toggle-optional');
    });



  




              // In routes/web.php
    Route::patch('payroll-periods/{payrollPeriod}/start-processing', [PayrollPeriodController::class, 'startProcessing'])
    ->name('payroll-periods.start-processing');

    Route::patch('payroll-periods/{payrollPeriod}/process', [PayrollPeriodController::class, 'process'])
    ->name('payroll-periods.process');

    Route::delete('payroll-periods/{payrollPeriod}/remove-staff/{staff}', [PayrollPeriodController::class, 'removeStaff'])
    ->name('payroll-periods.remove-staff');

    Route::delete('payroll-periods/{payrollPeriod}/remove-all-staff', [PayrollPeriodController::class, 'removeAllStaff'])
    ->name('payroll-periods.remove-all-staff');

                // Payroll Period Export Routes
    Route::get('payroll-periods/{payrollPeriod}/export-excel', [PayrollPeriodController::class, 'exportExcel'])
    ->name('payroll-periods.export-excel');

    Route::get('payroll-periods/{payrollPeriod}/export-pdf', [PayrollPeriodController::class, 'exportPdf'])
    ->name('payroll-periods.export-pdf');

    Route::get('payroll-periods/{payrollPeriod}/export-word', [PayrollPeriodController::class, 'exportWord'])
    ->name('payroll-periods.export-word');

    Route::get('payroll-periods/{payrollPeriod}/export-all', [PayrollPeriodController::class, 'exportAll'])
    ->name('payroll-periods.export-all');

    // ============================================
    // CLASS MANAGEMENT
    // ============================================
    Route::prefix('classes')->name('classes.')->group(function () {
        Route::post('/{class}/subjects', [StudentClassController::class, 'attachSubject'])->name('subject.attach');
        Route::delete('/{class}/subjects/{subject}', [StudentClassController::class, 'detachSubject'])->name('subject.detach');
    });

    Route::prefix('student-classes')->name('student-classes.')->group(function () {
        Route::post('/{studentClass}/assign-subject-teacher', [StudentClassController::class, 'assignSubjectTeacher'])
            ->name('assign-subject-teacher');
        Route::delete('/{studentClass}/remove-subject-teacher/{subject}', [StudentClassController::class, 'removeSubjectTeacher'])
            ->name('remove-subject-teacher');
        Route::post('/{studentClass}/assign-prefect', [StudentClassController::class, 'assignPrefect'])
            ->name('assign-prefect');
        Route::get('/{class}/attendance-data', [StudentClassController::class, 'getAttendanceData'])
            ->name('attendance-data');
    });

    Route::get('/classes/{class}/attendance-dashboard', [StudentClassController::class, 'attendanceDashboard'])
        ->name('classes.attendance.dashboard');

    // ============================================
    // ATTENDANCE
    // ============================================
    Route::resource('attendance-sessions', AttendanceSessionController::class);
    Route::resource('attendance-settings', AttendanceSettingController::class);

    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/create-for-class/{studentClassId}', [AttendanceSessionController::class, 'createForClass'])
            ->name('create-for-class');
        Route::get('/check-exists', [AttendanceSessionController::class, 'checkExists'])
            ->name('check-exists');
        Route::get('/class/{classId}/students', [AttendanceSessionController::class, 'getStudents'])
            ->name('class.students');
        Route::get('/session/ajax', [AttendanceSessionController::class, 'ajax'])
            ->name('session.ajax');
        Route::get('/{classId}/load', [AttendanceSessionController::class, 'loadAttendance'])
            ->name('session.load');
        Route::post('/ajax/{classId}', [AttendanceSessionController::class, 'getAttendanceData'])
            ->name('ajax');
        Route::post('/store/{classId}', [AttendanceSessionController::class, 'storeForClass'])
            ->name('store.class');
    });

    // ============================================
    // STAFF ATTENDANCE
    // ============================================
    Route::resource('staff-attendance', StaffAttendanceController::class);

    Route::get('staff-attendance-dashboard', [StaffAttendanceController::class, 'dashboard'])
        ->name('staff-attendance.dashboard');

    Route::post('staff-attendance/gps-clock-in', [StaffAttendanceController::class, 'gpsClockIn'])
        ->name('staff-attendance.gps-clock-in');

    Route::post('staff-attendance/gps-clock-out', [StaffAttendanceController::class, 'gpsClockOut'])
        ->name('staff-attendance.gps-clock-out');

    Route::get('staffattendance-live-map', [StaffAttendanceController::class, 'liveMap'])
        ->name('staff-attendance.live-map');

    // ============================================
    // FEE SETUP
    // ============================================
    Route::resource('fee-categories', FeeCategoryController::class);
    Route::resource('school-fee-structures', SchoolFeeStructureController::class);


        /*
    |--------------------------------------------------------------------------
    | Fee Management Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:web'])->prefix('fee-structures')->name('fee-structures.')->group(function () {
        Route::get('/', [FeeStructureController::class, 'index'])->name('index');
        Route::get('/create', [FeeStructureController::class, 'create'])->name('create');
        Route::post('/', [FeeStructureController::class, 'store'])->name('store');
        Route::get('/{id}', [FeeStructureController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [FeeStructureController::class, 'edit'])->name('edit');
        Route::put('/{id}', [FeeStructureController::class, 'update'])->name('update');
        Route::delete('/{id}', [FeeStructureController::class, 'destroy'])->name('destroy');
    });

  

    /*
    |--------------------------------------------------------------------------
    | Student Fee Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:student'])->prefix('student/fees')->name('student.fees.')->group(function () {
        Route::get('/', [StudentFeeController::class, 'index'])->name('index');
        Route::get('/{id}', [StudentFeeController::class, 'show'])->name('show');
        Route::post('/{id}/pay', [StudentFeeController::class, 'makePayment'])->name('pay');
        Route::get('/payments/history', [StudentFeeController::class, 'paymentHistory'])->name('payments');
        Route::get('/receipt/{id}/download', [StudentFeeController::class, 'downloadReceipt'])->name('receipt.download');
    });


    // ============================================
    // STUDENT PROGRESSION
    // ============================================
    Route::prefix('student-progressions')->name('student-progressions.')->group(function () {
        Route::get('/', [StudentProgressionController::class, 'index'])->name('index');
        Route::post('/process', [StudentProgressionController::class, 'process'])->name('process');
        Route::post('/bulk-promote', [StudentProgressionController::class, 'bulkPromote'])->name('bulk-promote');
    });

    Route::middleware(['auth'])->group(function () {
        // Payslip routes - ORDER MATTERS! Put specific routes before wildcard routes
        Route::get('/payslips', [PayslipController::class, 'index'])->name('payslips.index');
        Route::get('/payslips/create', [PayslipController::class, 'create'])->name('payslips.create');
        Route::post('/payslips', [PayslipController::class, 'store'])->name('payslips.store');
        
        // IMPORTANT: This route must be defined BEFORE the {payslip} route
        Route::get('/payslips/staff-salary-data', [PayslipController::class, 'getStaffSalaryData'])->name('payslips.staff-salary-data');
        Route::get('/payslips/staff-salary-structures', [PayslipController::class, 'getStaffSalaryStructures'])->name('payslips.staff-salary-structures');
        
        Route::get('/payslips/{payslip}', [PayslipController::class, 'show'])->name('payslips.show');
        Route::delete('/payslips/{payslip}', [PayslipController::class, 'destroy'])->name('payslips.destroy');
        Route::get('/payslips/{payslip}/export-pdf', [PayslipController::class, 'exportPdf'])->name('payslips.export-pdf');
        Route::post('/payslips/bulk-generate', [PayslipController::class, 'bulkGenerate'])->name('payslips.bulk-generate');
        
        // Staff payslips
        Route::get('/staff/{staff}/payslips', [PayslipController::class, 'staffPayslips'])->name('staff.payslips');
        Route::get('/payslips/filter', [PayslipController::class, 'filter'])->name('payslips.filter');
        Route::get('payslips/{payslip}/pdf', [PayslipController::class, 'exportPdf'])->name('payslips.pdf'); // Add this line
    });

            // Add this route
    Route::get('payroll-periods/{payrollPeriod}/export', [PayrollPeriodController::class, 'export'])
    ->name('payroll-periods.export');

    // ============================================
    // SCORES & RESULTS
    // ============================================
    Route::prefix('scores')->name('scores.')->group(function () {
        Route::get('/', [ScoreController::class, 'index'])->name('index');
        Route::match(['get', 'post'], '/load-students', [ScoreController::class, 'loadStudents'])->name('load-students');
        Route::post('/save', [ScoreController::class, 'save'])->name('save');
    });

    Route::get('/subject-results', [SubjectResultController::class, 'index'])->name('subject-results.index');

    // ============================================
    // BROADSHEET
    // ============================================
    Route::prefix('broadsheet')->name('broadsheet.')->group(function () {
        Route::get('/', [BroadsheetController::class, 'index'])->name('index');
        Route::post('/generate', [BroadsheetController::class, 'generate'])->name('generate');
        Route::post('/pdf', [BroadsheetController::class, 'pdf'])->name('pdf');
        Route::post('/ajax', [BroadsheetController::class, 'ajaxLoad'])->name('ajax');
    });

    // ============================================
    // TIMETABLES
    // ============================================
    Route::resource('timetables', TimetableController::class);
    Route::get('/timetables/{timetable}/download', [TimetableController::class, 'download'])->name('timetables.download');

    // ============================================
    // REPORT CARDS
    // ============================================
    Route::prefix('report-cards')->name('report-cards.')->group(function () {
        Route::get('/', [ReportCardController::class, 'index'])->name('index');
        Route::post('/show', [ReportCardController::class, 'show'])->name('show');
        Route::get('/get-students-by-class', [ReportCardController::class, 'getStudentsByClass'])->name('get-students-by-class');
        Route::get('/{student}', [ReportCardController::class, 'reportCard'])->name('show-single');
    });
    Route::get('/get-students-by-class', [ReportCardController::class, 'getStudentsByClass'])->name('get.students.by.class');

    // ============================================
    // GRADUATED STUDENTS
    // ============================================
    Route::prefix('graduated-students')->name('graduates.')->group(function () {
        Route::get('/', [GraduationController::class, 'index'])->name('index');
        Route::get('/export', [GraduationController::class, 'export'])->name('export');
        Route::get('/print', [GraduationController::class, 'printView'])->name('print');
        Route::get('/{id}', [GraduationController::class, 'show'])->name('show');
        Route::get('/{id}/certificate', [GraduationController::class, 'certificate'])->name('certificate');
        Route::delete('/{id}', [GraduationController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/restore', [GraduationController::class, 'restore'])->name('restore');
    });

    // Keep original route for backward compatibility
    Route::get('/graduated-students', [GraduationController::class, 'index'])->name('graduated-students.index');


   
    // ============================================
    // LESSON NOTES
    // ============================================
    Route::prefix('lesson-notes')->name('lesson-notes.')->group(function () {
        Route::get('/', [LessonNoteController::class, 'index'])->name('index');
        Route::get('/create', [LessonNoteController::class, 'create'])->name('create');
        Route::post('/', [LessonNoteController::class, 'store'])->name('store');
        Route::get('/{id}', [LessonNoteController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [LessonNoteController::class, 'edit'])->name('edit');
        Route::put('/{id}', [LessonNoteController::class, 'update'])->name('update');
        Route::delete('/{id}', [LessonNoteController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/clone', [LessonNoteController::class, 'clone'])->name('clone');
        Route::post('/{id}/comments', [LessonNoteController::class, 'storeComment'])->name('comments.store');
    });
    Route::get('/lesson-notes/{id}/download', [LessonNoteController::class, 'download'])->name('lesson-notes.download');
    Route::get('/lesson-notes/{lessonNote}/download-attachment/{file}', [LessonNoteController::class, 'downloadAttachment'])
        ->name('lesson-notes.download-attachment');

    // ============================================
    // ASSESSMENT FORMS
    // ============================================
    Route::prefix('assessment-forms')->name('assessment-forms.')->group(function () {
        Route::get('/', [AssessmentFormController::class, 'index'])->name('index');
        Route::get('/create', [AssessmentFormController::class, 'create'])->name('create');
        Route::post('/', [AssessmentFormController::class, 'store'])->name('store');
        Route::get('/{id}', [AssessmentFormController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [AssessmentFormController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AssessmentFormController::class, 'update'])->name('update');
        Route::delete('/{id}', [AssessmentFormController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/download', [AssessmentFormController::class, 'download'])->name('download');
        Route::get('/{id}/view', [AssessmentFormController::class, 'view'])->name('view');
        Route::post('/{id}/toggle-status', [AssessmentFormController::class, 'toggleStatus'])->name('toggle-status');
    });

    // ============================================
    // ASSETS
    // ============================================
    Route::prefix('assets')->name('assets.')->group(function () {
        Route::get('/', [AssetController::class, 'index'])->name('index');
        Route::get('/create', [AssetController::class, 'create'])->name('create');
        Route::post('/', [AssetController::class, 'store'])->name('store');
        Route::get('/{id}', [AssetController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [AssetController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AssetController::class, 'update'])->name('update');
        Route::delete('/{id}', [AssetController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/assign', [AssetController::class, 'assign'])->name('assign');
        Route::post('/{id}/return', [AssetController::class, 'returnAsset'])->name('return');
        Route::get('/{id}/download-document', [AssetController::class, 'downloadDocument'])->name('download.document');
        Route::get('/{id}/download-image', [AssetController::class, 'downloadImage'])->name('download.image');
    });

    // ============================================
    // LEAVES
    // ============================================
    Route::prefix('leaves')->name('leaves.')->group(function () {
        Route::get('/', [LeavesController::class, 'index'])->name('index');
        Route::get('/create', [LeavesController::class, 'create'])->name('create');
        Route::post('/', [LeavesController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [LeavesController::class, 'edit'])->name('edit');
        Route::put('/{id}', [LeavesController::class, 'update'])->name('update');
        Route::delete('/{id}', [LeavesController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/watch', [LeavesController::class, 'watch'])->name('watch');
        Route::get('/{id}/download-pdf', [LeavesController::class, 'downloadPDF'])->name('download.pdf');
        Route::get('/{id}/download-word', [LeavesController::class, 'downloadWord'])->name('download.word');
        Route::post('/{id}/approve', [LeavesController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [LeavesController::class, 'reject'])->name('reject');
    });

    // ============================================
    // STAFF APPRAISALS
    // ============================================
    Route::prefix('staff-appraisals')->name('staff-appraisals.')->group(function () {
        Route::get('/', [StaffAppraisalController::class, 'index'])->name('index');
        Route::get('/create', [StaffAppraisalController::class, 'create'])->name('create');
        Route::post('/', [StaffAppraisalController::class, 'store'])->name('store');
        Route::get('/{id}', [StaffAppraisalController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [StaffAppraisalController::class, 'edit'])->name('edit');
        Route::put('/{id}', [StaffAppraisalController::class, 'update'])->name('update');
        Route::delete('/{id}', [StaffAppraisalController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/download', [StaffAppraisalController::class, 'download'])->name('download');
        Route::get('/{id}/view', [StaffAppraisalController::class, 'view'])->name('view');
        Route::post('/{id}/toggle-status', [StaffAppraisalController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{id}/review', [StaffAppraisalController::class, 'review'])->name('review');
    });

    // ============================================
    // DISCUSSIONS
    // ============================================
    Route::prefix('discussions')->name('discussions.')->group(function () {
        Route::get('/', [DiscussionController::class, 'index'])->name('index');
        Route::get('/create', [DiscussionController::class, 'create'])->name('create');
        Route::post('/', [DiscussionController::class, 'store'])->name('store');
        Route::get('/{slug}', [DiscussionController::class, 'show'])->name('show');
        Route::post('/{slug}/message', [DiscussionController::class, 'sendMessage'])->name('message.send');
        Route::put('/message/{id}', [DiscussionController::class, 'editMessage'])->name('message.edit');
        Route::delete('/message/{id}', [DiscussionController::class, 'deleteMessage'])->name('message.delete');
        Route::post('/{slug}/participant', [DiscussionController::class, 'addParticipant'])->name('participant.add');
        Route::delete('/{slug}/participant/{staffId}', [DiscussionController::class, 'removeParticipant'])->name('participant.remove');
        Route::get('/attachment/{id}/download', [DiscussionController::class, 'downloadAttachment'])->name('attachment.download');
    });



    // ============================================
    // GRIEVANCES (Staff)
    // ============================================
    Route::prefix('grievance')->name('grievance.')->group(function () {
        Route::get('/', [GrievanceController::class, 'index'])->name('index');
        Route::get('/create', [GrievanceController::class, 'create'])->name('create');
        Route::post('/', [GrievanceController::class, 'store'])->name('store');
        Route::get('/{id}', [GrievanceController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [GrievanceController::class, 'edit'])->name('edit');
        Route::put('/{id}', [GrievanceController::class, 'update'])->name('update');
        Route::delete('/{id}', [GrievanceController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/assign', [GrievanceController::class, 'assign'])->name('assign');
        Route::post('/{id}/status', [GrievanceController::class, 'updateStatus'])->name('update-status');
        Route::post('/{id}/comments', [GrievanceController::class, 'addComment'])->name('add-comment');
        Route::post('/{id}/escalate', [GrievanceController::class, 'escalate'])->name('escalate');
        Route::post('/{id}/appeal', [GrievanceController::class, 'appeal'])->name('appeal');
        Route::get('/statistics', [GrievanceController::class, 'statistics'])->name('statistics');
    });


    // routes/web.php
    Route::prefix('admin')->middleware(['auth'])->group(function () {
        // Payment routes
        Route::prefix('payments')->group(function () {
            Route::get('/', [AdminPaymentController::class, 'index'])->name('admin.payments.index');
            Route::get('/create', [AdminPaymentController::class, 'create'])->name('admin.payments.create');
            Route::post('/', [AdminPaymentController::class, 'store'])->name('admin.payments.store');
            Route::get('/{id}', [AdminPaymentController::class, 'show'])->name('admin.payments.show');
            Route::get('/{id}/edit', [AdminPaymentController::class, 'edit'])->name('admin.payments.edit');
            Route::put('/{id}', [AdminPaymentController::class, 'update'])->name('admin.payments.update');
            Route::delete('/{id}', [AdminPaymentController::class, 'destroy'])->name('admin.payments.destroy');
            
            // AJAX endpoints
            Route::get('/get-student-details/{id}', [AdminPaymentController::class, 'getStudentDetails'])->name('admin.payments.get-student-details');
            Route::get('/get-student-balance/{id}', [AdminPaymentController::class, 'getStudentBalance'])->name('admin.payments.get-student-balance');
            Route::get('/get-allocations', [AdminPaymentController::class, 'getAllocations'])->name('admin.payments.get-allocations');
            Route::get('/get-bills', [AdminPaymentController::class, 'getBills'])->name('admin.payments.get-bills');
            
            // Receipt
            Route::get('/{id}/receipt', [AdminPaymentController::class, 'downloadReceipt'])->name('admin.payments.receipt');
        });
    });

        // Add this temporary route for testing
Route::get('/test-bills/{studentId}', function($studentId) {
    $bills = App\Models\BillSheet::where('student_id', $studentId)
        ->where('balance', '>', 0)
        ->where('status', '!=', 'cancelled')
        ->get(['id', 'bill_number', 'balance']);
    
    return response()->json($bills);
});

      
    

}); // CLOSES THE AUTH:WEB GROUP - THIS WAS MISSING!

// ============================================
// STUDENT GRIEVANCES - OUTSIDE AUTH:WEB GROUP
// ============================================
Route::prefix('student-grievance')->name('student-grievance.')->group(function () {
    Route::get('/', [StudentGrievanceController::class, 'index'])->name('index');
    Route::get('/create', [StudentGrievanceController::class, 'create'])->name('create');
    Route::post('/', [StudentGrievanceController::class, 'store'])->name('store');
    Route::get('/{id}', [StudentGrievanceController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [StudentGrievanceController::class, 'edit'])->name('edit');
    Route::put('/{id}', [StudentGrievanceController::class, 'update'])->name('update');
    Route::delete('/{id}', [StudentGrievanceController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/assign', [StudentGrievanceController::class, 'assign'])->name('assign');
    Route::post('/{id}/status', [StudentGrievanceController::class, 'updateStatus'])->name('update-status');
    Route::post('/{id}/comments', [StudentGrievanceController::class, 'addComment'])->name('add-comment');
    Route::post('/{id}/escalate', [StudentGrievanceController::class, 'escalate'])->name('escalate');
    Route::post('/{id}/appeal', [StudentGrievanceController::class, 'appeal'])->name('appeal');
    Route::get('/statistics', [StudentGrievanceController::class, 'statistics'])->name('statistics');
});

/*
|--------------------------------------------------------------------------
| STUDENT ROUTES (Protected by auth:student)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:student'])->group(function () {
    // Student Dashboard
    Route::get('/student/dashboard', [StudentDashboardController::class, 'index'])->name('students.dashboard');
    Route::get('/student/profile', [StudentDashboardController::class, 'profile'])->name('students.profile');
    Route::get('/student/attendance', [StudentDashboardController::class, 'attendance'])->name('students.attendance');
    Route::get('/student/results', [StudentDashboardController::class, 'results'])->name('students.results');
    Route::get('/student/academic-history', [StudentDashboardController::class, 'academicHistory'])->name('students.academic-history');
    Route::get('/student/class-history', [StudentDashboardController::class, 'classHistory'])->name('students.class-history');
    Route::get('/student/timetable', [StudentDashboardController::class, 'timetable'])->name('students.timetable');
    Route::get('/student/fees', [StudentDashboardController::class, 'fees'])->name('students.fees');
    Route::get('/student/settings', [StudentDashboardController::class, 'settings'])->name('students.settings');
    Route::get('/student/report-card', [ReportCardController::class, 'studentReportCard'])->name('student.report-card');
    
    
    // Student API Routes
    Route::prefix('student/api')->name('students.api.')->group(function () {
        Route::get('/class-history', [StudentDashboardController::class, 'getClassHistoryApi'])->name('class-history');
        Route::get('/class-performance', [StudentDashboardController::class, 'getClassPerformanceApi'])->name('class-performance');
    });
    
    // Student Timetable routes
    Route::prefix('student/timetable')->name('students.timetable.')->group(function () {
        Route::get('/view/{id}', [StudentDashboardController::class, 'viewTimetable'])->name('view');
        Route::get('/download/{id}', [StudentDashboardController::class, 'downloadTimetable'])->name('download');
        Route::get('/stream/{id}', [StudentDashboardController::class, 'streamTimetable'])->name('stream');
        Route::get('/info/{id}', [StudentDashboardController::class, 'getTimetableInfo'])->name('info');
        Route::post('/switch', [StudentDashboardController::class, 'switchTimetable'])->name('switch');
    });





    

                    /*
    |--------------------------------------------------------------------------
    | School Fee Structures Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:web'])->prefix('school-fee-structures')->name('school-fee-structures.')->group(function () {
        Route::get('/', [SchoolFeeStructureController::class, 'index'])->name('index');
        Route::get('/create', [SchoolFeeStructureController::class, 'create'])->name('create');
        Route::post('/', [SchoolFeeStructureController::class, 'store'])->name('store');
        Route::get('/{id}', [SchoolFeeStructureController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [SchoolFeeStructureController::class, 'edit'])->name('edit');
        Route::put('/{id}', [SchoolFeeStructureController::class, 'update'])->name('update');
        Route::delete('/{id}', [SchoolFeeStructureController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [SchoolFeeStructureController::class, 'toggleStatus'])->name('toggle-status');
        Route::patch('/{id}/toggle-optional', [SchoolFeeStructureController::class, 'toggleOptional'])->name('toggle-optional');
        Route::post('/bulk-delete', [SchoolFeeStructureController::class, 'bulkDelete'])->name('bulk-delete');
        Route::get('/api/by-class-year', [SchoolFeeStructureController::class, 'getByClassAndYear'])->name('api.by-class-year');
    });

            /*
    |--------------------------------------------------------------------------
    | Bill Sheet Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:web'])->prefix('bills')->name('bills.')->group(function () {
        Route::get('/', [BillSheetController::class, 'index'])->name('index');
        Route::get('/create', [BillSheetController::class, 'create'])->name('create');
        Route::post('/', [BillSheetController::class, 'store'])->name('store');
        Route::get('/{id}', [BillSheetController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [BillSheetController::class, 'edit'])->name('edit');
        Route::put('/{id}', [BillSheetController::class, 'update'])->name('update');
        Route::delete('/{id}', [BillSheetController::class, 'destroy'])->name('destroy');
        
        // Bill actions
        Route::post('/{id}/generate', [BillSheetController::class, 'generate'])->name('generate');
        Route::post('/{id}/approve', [BillSheetController::class, 'approve'])->name('approve');
        Route::post('/{id}/cancel', [BillSheetController::class, 'cancel'])->name('cancel');
        
        // PDF routes
        Route::get('/{id}/download-pdf', [BillSheetController::class, 'downloadPdf'])->name('download-pdf');
        Route::get('/{id}/view-pdf', [BillSheetController::class, 'viewPdf'])->name('view-pdf');
        
        // API routes
        Route::get('/get-fee-allocations', [BillSheetController::class, 'getFeeAllocations'])->name('get-fee-allocations');
        Route::get('/get-student-details/{id}', [BillSheetController::class, 'getStudentDetails'])->name('get-student-details');
        
        // Bulk generate
        Route::post('/bulk-generate', [BillSheetController::class, 'bulkGenerate'])->name('bulk-generate');
    });

            // ============================================
    // BILL SHEETS
    // ============================================
    Route::middleware(['auth:web'])->prefix('bills')->name('bills.')->group(function () {
        // Main CRUD
        Route::get('/', [BillSheetController::class, 'index'])->name('index');
        Route::get('/create', [BillSheetController::class, 'create'])->name('create');
        Route::post('/', [BillSheetController::class, 'store'])->name('store');
        Route::get('/{id}', [BillSheetController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [BillSheetController::class, 'edit'])->name('edit');
        Route::put('/{id}', [BillSheetController::class, 'update'])->name('update');
        Route::delete('/{id}', [BillSheetController::class, 'destroy'])->name('destroy');
        
        // Bill Actions
        Route::post('/{id}/generate', [BillSheetController::class, 'generate'])->name('generate');
        Route::post('/{id}/approve', [BillSheetController::class, 'approve'])->name('approve');
        Route::post('/{id}/cancel', [BillSheetController::class, 'cancel'])->name('cancel');
        
        // PDF Routes
        Route::get('/{id}/download-pdf', [BillSheetController::class, 'downloadPdf'])->name('download-pdf');
        Route::get('/{id}/view-pdf', [BillSheetController::class, 'viewPdf'])->name('view-pdf');
        
        // API Routes
        Route::get('/get-fee-allocations', [BillSheetController::class, 'getFeeAllocations'])->name('get-fee-allocations');
        Route::get('/get-student-details/{id}', [BillSheetController::class, 'getStudentDetails'])->name('get-student-details');
        
        // Bulk Operations
        Route::post('/bulk-generate', [BillSheetController::class, 'bulkGenerate'])->name('bulk-generate');
    });


    


      
       

       
        




});

