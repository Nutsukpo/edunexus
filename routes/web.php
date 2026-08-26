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
use App\Http\Controllers\LessonNoteApprovalController;
use App\Http\Controllers\BillSheetApprovalController;
use App\Http\Controllers\FeePaymentController;
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
use App\Http\Controllers\BillSheetController;
use App\Http\Controllers\StudentFeeController;
use App\Http\Controllers\SalaryStructureController;
use App\Http\Controllers\ClassFeeStructureController;
use App\Http\Controllers\PayslipController;
use App\Http\Controllers\FeePaymentReportController;
use App\Http\Controllers\LeaveApprovalController;
use App\Http\Controllers\PayrollPeriodApprovalController;
use App\Http\Controllers\RolePermissionController;











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
         // Add these routes for the dashboard AJAX calls
    Route::get('/attendance-data', [DashboardController::class, 'getAttendanceData'])->name('dashboard.attendance-data');
    Route::get('/class-attendance', [DashboardController::class, 'getClassAttendance'])->name('dashboard.class-attendance');
    

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


        /*
        |--------------------------------------------------------------------------
        | Payroll Period Workflow
        |--------------------------------------------------------------------------
        */

        Route::post(
            'payroll-periods/{id}/submit',
            [PayrollPeriodController::class, 'submit']
        )->name('payroll-periods.submit');

        Route::post(
            'payroll-periods/{id}/approve',
            [PayrollPeriodController::class, 'approve']
        )->name('payroll-periods.approve');

        Route::post(
            'payroll-periods/{id}/reject',
            [PayrollPeriodController::class, 'reject']
        )->name('payroll-periods.reject');
    });

    Route::middleware(['auth:web'])->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Payroll Period Approvals
        |--------------------------------------------------------------------------
        */
    
        Route::get(
            '/payroll-period-approvals',
            [PayrollPeriodApprovalController::class, 'index']
        )->name('payroll-period-approvals.index');
    
        Route::get(
            '/payroll-period-approvals/{payrollPeriod}',
            [PayrollPeriodApprovalController::class, 'show']
        )->name('payroll-period-approvals.show');
    
        Route::post(
            '/payroll-period-approvals/{payrollPeriod}/submit',
            [PayrollPeriodApprovalController::class, 'submit']
        )->name('payroll-period-approvals.submit');
    
        Route::post(
            '/payroll-period-approvals/{payrollPeriod}/approve',
            [PayrollPeriodApprovalController::class, 'approve']
        )->name('payroll-period-approvals.approve');
    
        Route::post(
            '/payroll-period-approvals/{payrollPeriod}/reject',
            [PayrollPeriodApprovalController::class, 'reject']
        )->name('payroll-period-approvals.reject');
    
        Route::post(
            '/payroll-period-approvals/{payrollPeriod}/resubmit',
            [PayrollPeriodApprovalController::class, 'resubmit']
        )->name('payroll-period-approvals.resubmit');
    
    });


    // Fee Payment Routes
    Route::get('/fee-payments/get-students-by-class', [FeePaymentController::class, 'getStudentsByClass'])->name('fee-payments.get-students-by-class');
    Route::get('/fee-payments/get-student-details/{studentId}', [FeePaymentController::class, 'getStudentDetails'])->name('fee-payments.get-student-details');
    Route::resource('fee-payments', FeePaymentController::class);

    Route::get('/fee-payments/get-students-by-class', [FeePaymentController::class, 'getStudentsByClass'])->name('fee-payments.get-students-by-class');


    Route::get('/fee-payments/get-bill-sheet-items', [FeePaymentController::class, 'getBillSheetItems'])->name('fee-payments.get-bill-sheet-items');
    



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
    | BILL SHEET APPROVAL CENTER
    |--------------------------------------------------------------------------
    */

        Route::get('/bill-sheet-approvals', [BillSheetApprovalController::class, 'index'])
        ->name('bill-sheet-approvals.index');

        Route::post('/bill-sheet-approvals/approve-all', [BillSheetApprovalController::class, 'approveAll'])
        ->name('bill-sheet-approvals.approve-all');

        Route::post('/bill-sheet-approvals/reject-all', [BillSheetApprovalController::class, 'rejectAll'])
        ->name('bill-sheet-approvals.reject-all');

        Route::post('/bill-sheet-approvals/{billSheet}/approve', [BillSheetApprovalController::class, 'approve'])
        ->name('bill-sheet-approvals.approve');

        Route::post('/bill-sheet-approvals/{billSheet}/reject', [BillSheetApprovalController::class, 'reject'])
        ->name('bill-sheet-approvals.reject');
    /*
    |--------------------------------------------------------------------------
    | AJAX / API-style routes
    |--------------------------------------------------------------------------
    |
    | These MUST come before /bill-sheets/{id} style routes.
    |
    */

    Route::prefix('api')->group(function () {

        Route::get(
            '/student-count',
            [BillSheetController::class, 'getStudentCount']
        )->name('api.student-count');

    });


    /*
    |--------------------------------------------------------------------------
    | Student assignments for selected class/year
    |--------------------------------------------------------------------------
    |
    | Used by the Bill Sheet create page to determine how many
    | students are eligible for billing.
    |
    */

    Route::get(
        '/bill-sheets/assignments',
        [BillSheetController::class, 'assignments']
    )->name('bill-sheets.assignments');


    /*
    |--------------------------------------------------------------------------
    | Bill Sheet Resource Routes
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'bill-sheets',
        BillSheetController::class
    );


    /*
    |--------------------------------------------------------------------------
    | Bill Sheet Additional Actions
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/bill-sheets/{id}/toggle-status',
        [BillSheetController::class, 'toggleStatus']
    )->name('bill-sheets.toggle-status');


    Route::get(
        '/bill-sheets/{id}/pdf',
        [BillSheetController::class, 'pdf']
    )->name('bill-sheets.pdf');


    Route::get(
        '/bill-sheets/{id}/print',
        [BillSheetController::class, 'print']
    )->name('bill-sheets.print');


    Route::get(
        '/bill-sheets/{id}/duplicate',
        [BillSheetController::class, 'duplicate']
    )->name('bill-sheets.duplicate');


    Route::get(
        '/bill-sheets/{id}/export',
        [BillSheetController::class, 'export']
    )->name('bill-sheets.export');
    
        // Regenerate Bill Sheets for the class/year/term
    Route::post(
        '/bill-sheets/{billSheet}/regenerate',
        [BillSheetController::class, 'regenerate']
    )->name('bill-sheets.regenerate');

        

    // ============================================================
// PAYROLL PERIOD PROCESSING
// ============================================================

Route::patch(
    'payroll-periods/{payrollPeriod}/start-processing',
    [PayrollPeriodController::class, 'startProcessing']
)->name('payroll-periods.start-processing');


Route::patch(
    'payroll-periods/{payrollPeriod}/process',
    [PayrollPeriodController::class, 'process']
)->name('payroll-periods.process');


// ============================================================
// PAYROLL STAFF MANAGEMENT
// ============================================================

/*
|--------------------------------------------------------------------------
| Remove selected staff from a payroll period
|--------------------------------------------------------------------------
|
| The Blade form submits POST with:
|
| staff_ids[]
|
| This is the route your current Remove button should use.
|
*/

Route::post(
    'payroll-periods/{id}/remove-staff',
    [PayrollPeriodController::class, 'removeStaff']
)->name('payroll-periods.remove-staff');


/*
|--------------------------------------------------------------------------
| Remove one specific staff member
|--------------------------------------------------------------------------
|
| Kept as a separate route with a different name so it does not
| conflict with the selected/bulk removal route above.
|
*/

Route::delete(
    'payroll-periods/{payrollPeriod}/remove-staff/{staff}',
    [PayrollPeriodController::class, 'removeStaff']
)->name('payroll-periods.remove-single-staff');


/*
|--------------------------------------------------------------------------
| Remove all staff from a payroll period
|--------------------------------------------------------------------------
*/

Route::delete(
    'payroll-periods/{payrollPeriod}/remove-all-staff',
    [PayrollPeriodController::class, 'removeAllStaff']
)->name('payroll-periods.remove-all-staff');


// ============================================================
// PAYROLL PERIOD SUBMISSION
// ============================================================

Route::post(
    'payroll-periods/{id}/submit',
    [PayrollPeriodController::class, 'submitForApproval']
)->name('payroll-periods.submit');


    // ============================================================
    // PAYROLL APPROVAL WORKFLOW
    // ============================================================
    //
    // These routes remain available for the approval workflow,
    // but DO NOT put Approve/Reject buttons on the payroll details
    // Blade page.
    // ============================================================

    Route::post(
        'payroll-periods/{id}/approve',
        [PayrollPeriodController::class, 'approve']
    )->name('payroll-periods.approve');


    Route::post(
        'payroll-periods/{id}/reject',
        [PayrollPeriodController::class, 'reject']
    )->name('payroll-periods.reject');


    // ============================================================
    // PAYROLL EXPORT ROUTES
    // ============================================================

    Route::get(
        'payroll-periods/{payrollPeriod}/export-excel',
        [PayrollPeriodController::class, 'exportExcel']
    )->name('payroll-periods.export-excel');


    Route::get(
        'payroll-periods/{payrollPeriod}/export-pdf',
        [PayrollPeriodController::class, 'exportPdf']
    )->name('payroll-periods.export-pdf');


    Route::get(
        'payroll-periods/{payrollPeriod}/export-word',
        [PayrollPeriodController::class, 'exportWord']
    )->name('payroll-periods.export-word');


    Route::get(
        'payroll-periods/{payrollPeriod}/export-all',
        [PayrollPeriodController::class, 'exportAll']
    )->name('payroll-periods.export-all');
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
    // Monthly Report Routes
    Route::get('/attendance/monthly-report', [AttendanceSessionController::class, 'monthlyReport'])->name('attendance.monthly-report');
    Route::get('/attendance/export-monthly', [AttendanceSessionController::class, 'exportMonthlyReport'])->name('attendance.export-monthly');
    

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

        // Monthly Report Routes
    Route::get('/staffattendance/monthly-report', [StaffAttendanceController::class, 'monthlyReport'])->name('staffattendance.monthly-report');
    Route::get('/staffattendance/export-monthly', [StaffAttendanceController::class, 'exportMonthlyReport'])->name('staffattendance.export-monthly');

    // ============================================
    // FEE SETUP
    // ============================================
  

    Route::middleware(['auth'])->group(function () {
        // Class Fee Structure Routes
        Route::resource('class-fee-structures', ClassFeeStructureController::class);
    });


    Route::middleware(['auth'])->group(function () {
    
        // Fee Payment Report Routes
        Route::prefix('fee-payment-reports')->name('fee.payment.reports.')->group(function () {
            // Main report index
            Route::get('/', [FeePaymentReportController::class, 'index'])->name('index');
            
            // View specific fee account
            Route::get('/{id}', [FeePaymentReportController::class, 'show'])->name('show');
            
            // Export routes
            Route::get('/export/pdf', [FeePaymentReportController::class, 'exportPdf'])->name('export.pdf');
            Route::get('/export/excel', [FeePaymentReportController::class, 'exportExcel'])->name('export.excel');
            
            // Payment history
            Route::get('/payment-history', [FeePaymentReportController::class, 'paymentHistory'])->name('payment-history');
            
            // Receipt generation
            Route::get('/receipt/{paymentId}', [FeePaymentReportController::class, 'generateReceipt'])->name('receipt');
            
            // Outstanding fees
            Route::get('/outstanding', [FeePaymentReportController::class, 'outstandingFees'])->name('outstanding');
            
            // API routes for charts
            Route::get('/api/summary-stats', [FeePaymentReportController::class, 'getSummaryStats'])->name('api.summary');
            Route::get('/api/chart-data', [FeePaymentReportController::class, 'getChartData'])->name('api.chart');
        });
    });





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

  

 

Route::middleware(['auth'])->group(function () {

    Route::resource('fee-payments', FeePaymentController::class);

    /*
    |--------------------------------------------------------------------------
    | FEE PAYMENT RECEIPTS
    |--------------------------------------------------------------------------
    */

    // Display receipt as HTML
    Route::get(
        '/fee-payments/{id}/receipt',
        [FeePaymentController::class, 'printReceipt']
    )->name('fee-payments.receipt');

    // Open PDF in browser
    Route::get(
        '/fee-payments/{id}/receipt/pdf',
        [FeePaymentController::class, 'receiptPdf']
    )->name('fee-payments.receipt.pdf');

    // Force PDF download
    Route::get(
        '/fee-payments/{id}/receipt/download',
        [FeePaymentController::class, 'downloadReceipt']
    )->name('fee-payments.receipt.download');
    Route::get('payments/receipt/{payment}/html', [FeePaymentController::class, 'downloadHtmlReceipt'])
    ->name('payments.receipt.html');

    /*
    |--------------------------------------------------------------------------
    | BACKWARD COMPATIBILITY
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/fee-payments/{id}/pdf',
        [FeePaymentController::class, 'pdf']
    )->name('fee-payments.pdf');

});

    Route::prefix('fee-payments')->name('fee-payments.')->group(function () {
        // Main CRUD Routes
        Route::get('/', [FeePaymentController::class, 'index'])->name('index');
        Route::get('/create', [FeePaymentController::class, 'create'])->name('create');
        Route::post('/', [FeePaymentController::class, 'store'])->name('store');
        Route::get('/{id}', [FeePaymentController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [FeePaymentController::class, 'edit'])->name('edit');
        Route::put('/{id}', [FeePaymentController::class, 'update'])->name('update');
        Route::delete('/{id}', [FeePaymentController::class, 'destroy'])->name('destroy');
        
        // AJAX Routes for dynamic loading
        Route::get('/get-students-by-class', [FeePaymentController::class, 'getStudentsByClass'])->name('get-students-by-class');
        Route::get('/get-student-details/{studentId}', [FeePaymentController::class, 'getStudentDetails'])->name('get-student-details');
        Route::get('/get-student-bill-sheets', [FeePaymentController::class, 'getStudentBillSheets'])->name('get-student-bill-sheets');
        Route::get('/get-bill-sheet-total/{studentId}', [FeePaymentController::class, 'getBillSheetTotal'])->name('get-bill-sheet-total');
        
        // Receipt
        Route::get('/{id}/print-receipt', [FeePaymentController::class, 'printReceipt'])->name('print-receipt');
    });
    Route::get('/fee-payments/get-students-by-class', [FeePaymentController::class, 'getStudentsByClass'])->name('fee-payments.get-students-by-class');


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

        
        



    // Lesson Notes main routes
    Route::resource('lesson-notes', LessonNoteController::class);

    // Lesson Notes Approval routes (no prefix)
    Route::get('approvals', [LessonNoteApprovalController::class, 'index'])
        ->name('approvals.index');

    Route::get('approvals/{id}', [LessonNoteApprovalController::class, 'show'])
        ->name('approvals.show');

    Route::post('approvals/{id}/approve', [LessonNoteApprovalController::class, 'approve'])
        ->name('approvals.approve');

    Route::post('approvals/{id}/reject', [LessonNoteApprovalController::class, 'reject'])
        ->name('approvals.reject');

    Route::post('approvals/{id}/request-changes', [LessonNoteApprovalController::class, 'requestChanges'])
        ->name('approvals.request-changes');

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
        Route::post('/{id}/submit', [LeavesController::class, 'submit'])->name('leaves.submit');
        
    });

    

    /*
    |--------------------------------------------------------------------------
    | LEAVE APPROVALS
    |--------------------------------------------------------------------------
    */

    Route::prefix('leave-approvals')
    ->name('leave-approvals.')
    ->group(function () {

        Route::get(
            '/',[LeaveApprovalController::class, 'index'])->name('index');

        Route::get(
            '/{leave}',
            [LeaveApprovalController::class, 'show'])->name('show');

        Route::post(
            '/{leave}/approve',
            [LeaveApprovalController::class, 'approve']
        )->name('approve');

        Route::post(
            '/{leave}/reject',
            [LeaveApprovalController::class, 'reject']
        )->name('reject');

        Route::post(
            '/{leave}/modify-approve',
            [LeaveApprovalController::class, 'modifyAndApprove']
        )->name('modify-approve');
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


});

                /*
    |--------------------------------------------------------------------------
    | ROLE & PERMISSION MANAGEMENT
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:web'])->group(function () {
    
        Route::middleware(['can:roles.view'])->group(function () {
    
            Route::get('/roles-permissions', [
                RolePermissionController::class,
                'index'
            ])->name('roles.permissions.index');
    
        });
    
        Route::middleware(['can:roles.manage-permissions'])->group(function () {
    
            Route::get('/roles-permissions/{role}/edit', [
                RolePermissionController::class,
                'edit'
            ])->name('roles.permissions.edit');
    
            Route::put('/roles-permissions/{role}', [
                RolePermissionController::class,
                'update'
            ])->name('roles.permissions.update');
    
        });
    });