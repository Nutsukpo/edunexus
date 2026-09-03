<?php

use Illuminate\Http\Request;
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
use App\Http\Controllers\StudentFeesController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\PaystackWebhookController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/login');

Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now(),
    ]);
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATION ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/login/admin', [AuthController::class, 'showAdminLoginForm'])
    ->name('admin.login');

/*
|--------------------------------------------------------------------------
| PUBLIC ANNOUNCEMENTS
|--------------------------------------------------------------------------
| Kept outside auth:web because the original routes explicitly exposed
| these endpoints publicly.
|--------------------------------------------------------------------------
*/

Route::get('/announcements/public', [AnnouncementController::class, 'publicIndex'])
    ->name('announcements.public');

Route::get('/announcements', [AnnouncementController::class, 'publicIndex'])
    ->name('announcements.public.index');

/*
|--------------------------------------------------------------------------
| STUDENT PASSWORD CHANGE / INITIAL DASHBOARD
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:student'])->group(function () {
    Route::get('/student/change-password', [AuthController::class, 'showPasswordChangeForm'])
        ->name('student.password.change.form');

    Route::post('/student/change-password', [AuthController::class, 'updatePassword'])
        ->name('student.password.change');

    Route::get('/student/dashboard', [StudentDashboardController::class, 'index'])
        ->name('students.dashboard');
});

/*
|--------------------------------------------------------------------------
| ADMIN / STAFF ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:web'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/attendance-summary', [DashboardController::class, 'getAttendanceSummary'])
            ->middleware('permission:dashboard.view')
            ->name('attendance.summary');

        Route::get('/class-attendance', [DashboardController::class, 'getClassAttendance'])
            ->middleware('permission:dashboard.view')
            ->name('class-attendance');

        Route::get('/attendance-data', [DashboardController::class, 'getAttendanceData'])
            ->middleware('permission:dashboard.view')
            ->name('attendance-data');
    });

    /*
    |--------------------------------------------------------------------------
    | ADMINISTRATION
    |--------------------------------------------------------------------------
    */

    Route::resource('users', UserController::class)
        ->middlewareFor(['index', 'show'], 'permission:users.view')
        ->middlewareFor(['create', 'store'], 'permission:users.create')
        ->middlewareFor(['edit', 'update'], 'permission:users.edit')
        ->middlewareFor('destroy', 'permission:users.delete');

    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
        ->middleware('permission:users.toggle-status')
        ->name('users.toggle-status');

    Route::resource('departments', DepartmentController::class)
        ->middlewareFor(['index', 'show'], 'permission:departments.view')
        ->middlewareFor(['create', 'store'], 'permission:departments.create')
        ->middlewareFor(['edit', 'update'], 'permission:departments.edit')
        ->middlewareFor('destroy', 'permission:departments.delete');

    /*
    |--------------------------------------------------------------------------
    | ACADEMIC SETUP
    |--------------------------------------------------------------------------
    */

    Route::resource('academic-years', AcademicYearController::class)
        ->middlewareFor(['index', 'show'], 'permission:academic-years.view')
        ->middlewareFor(['create', 'store'], 'permission:academic-years.create')
        ->middlewareFor(['edit', 'update'], 'permission:academic-years.edit')
        ->middlewareFor('destroy', 'permission:academic-years.delete');
    Route::resource('terms', TermController::class)
        ->middlewareFor(['index', 'show'], 'permission:terms.view')
        ->middlewareFor(['create', 'store'], 'permission:terms.create')
        ->middlewareFor(['edit', 'update'], 'permission:terms.edit')
        ->middlewareFor('destroy', 'permission:terms.delete');
    Route::resource('subjects', SubjectController::class)
        ->middlewareFor(['index', 'show'], 'permission:subjects.view')
        ->middlewareFor(['create', 'store'], 'permission:subjects.create')
        ->middlewareFor(['edit', 'update'], 'permission:subjects.edit')
        ->middlewareFor('destroy', 'permission:subjects.delete');
    Route::resource('student-classes', StudentClassController::class)
        ->middlewareFor(['index', 'show'], 'permission:classes.view')
        ->middlewareFor(['create', 'store'], 'permission:classes.create')
        ->middlewareFor(['edit', 'update'], 'permission:classes.edit')
        ->middlewareFor('destroy', 'permission:classes.delete');

    /*
    |--------------------------------------------------------------------------
    | STAFF
    |--------------------------------------------------------------------------
    */

    Route::resource('staff', StaffController::class)
        ->middlewareFor(['index', 'show'], 'permission:staff.view')
        ->middlewareFor(['create', 'store'], 'permission:staff.create')
        ->middlewareFor(['edit', 'update'], 'permission:staff.edit')
        ->middlewareFor('destroy', 'permission:staff.delete');

    /*
    |--------------------------------------------------------------------------
    | STUDENTS
    |--------------------------------------------------------------------------
    */

    Route::resource('students', StudentController::class)
        ->middlewareFor(['index', 'show'], 'permission:students.view')
        ->middlewareFor(['create', 'store'], 'permission:students.create')
        ->middlewareFor(['edit', 'update'], 'permission:students.edit')
        ->middlewareFor('destroy', 'permission:students.delete');
    Route::resource('enrollments', EnrollmentController::class)
        ->middlewareFor(['index', 'show'], 'permission:enrollments.view')
        ->middlewareFor(['create', 'store'], 'permission:enrollments.create')
        ->middlewareFor(['edit', 'update'], 'permission:enrollments.edit')
        ->middlewareFor('destroy', 'permission:enrollments.delete');
    Route::resource('student-class-assignments', StudentClassAssignmentController::class)
        ->middlewareFor(['index', 'show'], 'permission:student-class-assignments.view')
        ->middlewareFor(['create', 'store'], 'permission:student-class-assignments.create')
        ->middlewareFor(['edit', 'update'], 'permission:student-class-assignments.edit')
        ->middlewareFor('destroy', 'permission:student-class-assignments.delete');

    Route::delete(
        '/classes/{class}/students/{student}',
        [StudentClassAssignmentController::class, 'destroy']
    )->middleware('permission:student-class-assignments.delete')
->name('classes.students.remove');

    /*
    |--------------------------------------------------------------------------
    | PAYROLL PERIODS
    |--------------------------------------------------------------------------
    */

    Route::resource('payroll-periods', PayrollPeriodController::class)
        ->middlewareFor(['index', 'show'], 'permission:payroll.view')
        ->middlewareFor(['create', 'store'], 'permission:payroll.create')
        ->middlewareFor(['edit', 'update'], 'permission:payroll.edit')
        ->middlewareFor('destroy', 'permission:payroll.delete');

    Route::post(
        'payroll-periods/{payrollPeriod}/add-staff',
        [PayrollGenerationController::class, 'addStaff']
    )->middleware('permission:payroll.edit')
->name('payroll.addStaff');

    Route::get(
        'payroll-periods/{id}/assign-staff',
        [PayrollPeriodController::class, 'assignStaffForm']
    )->middleware('permission:payroll.edit')
->name('payroll.assign-staff.form');

    Route::post(
        'payroll-periods/{id}/assign-staff',
        [PayrollPeriodController::class, 'assignStaff']
    )->middleware('permission:payroll.edit')
->name('payroll.assign-staff.store');

    Route::post(
        'payroll-periods/{id}/remove-staff',
        [PayrollPeriodController::class, 'removeStaff']
    )->middleware('permission:payroll.edit')
->name('payroll-periods.remove-staff');

    Route::delete(
        'payroll-periods/{id}/remove-staff',
        [PayrollPeriodController::class, 'removeStaff']
    )->middleware('permission:payroll.edit')
->name('payroll-periods.remove-staff.delete');

    Route::delete(
        'payroll-periods/{payrollPeriod}/remove-staff/{staff}',
        [PayrollPeriodController::class, 'removeStaff']
    )->middleware('permission:payroll.edit')
->name('payroll-periods.remove-single-staff');

    Route::delete(
        'payroll-periods/{payrollPeriod}/remove-all-staff',
        [PayrollPeriodController::class, 'removeAllStaff']
    )->middleware('permission:payroll.edit')
->name('payroll-periods.remove-all-staff');

    Route::patch(
        'payroll-periods/{payrollPeriod}/start-processing',
        [PayrollPeriodController::class, 'startProcessing']
    )->middleware('permission:payroll.process')
->name('payroll-periods.start-processing');

    Route::patch(
        'payroll-periods/{payrollPeriod}/process',
        [PayrollPeriodController::class, 'process']
    )->middleware('permission:payroll.process')
->name('payroll-periods.process');

    Route::post(
        'payroll-periods/{id}/submit',
        [PayrollPeriodController::class, 'submitForApproval']
    )->middleware('permission:payroll.process')
->name('payroll-periods.submit');

    Route::post(
        'payroll-periods/{id}/approve',
        [PayrollPeriodController::class, 'approve']
    )->middleware('permission:payroll.approve')
->name('payroll-periods.approve');

    Route::post(
        'payroll-periods/{id}/reject',
        [PayrollPeriodController::class, 'reject']
    )->middleware('permission:payroll.reject')
->name('payroll-periods.reject');

    Route::get(
        'payroll-periods/{payrollPeriod}/export',
        [PayrollPeriodController::class, 'export']
    )->middleware('permission:payroll.reports')
->name('payroll-periods.export');

    Route::get(
        'payroll-periods/{payrollPeriod}/export-excel',
        [PayrollPeriodController::class, 'exportExcel']
    )->middleware('permission:payroll.reports')
->name('payroll-periods.export-excel');

    Route::get(
        'payroll-periods/{payrollPeriod}/export-pdf',
        [PayrollPeriodController::class, 'exportPdf']
    )->middleware('permission:payroll.reports')
->name('payroll-periods.export-pdf');

    Route::get(
        'payroll-periods/{payrollPeriod}/export-word',
        [PayrollPeriodController::class, 'exportWord']
    )->middleware('permission:payroll.reports')
->name('payroll-periods.export-word');

    Route::get(
        'payroll-periods/{payrollPeriod}/export-all',
        [PayrollPeriodController::class, 'exportAll']
    )->middleware('permission:payroll.reports')
->name('payroll-periods.export-all');

    /*
    |--------------------------------------------------------------------------
    | PAYROLL PERIOD APPROVAL CENTER
    |--------------------------------------------------------------------------
    */

    Route::prefix('payroll-period-approvals')
        ->name('payroll-period-approvals.')
        ->group(function () {
            Route::get('/', [PayrollPeriodApprovalController::class, 'index'])
                ->middleware('permission:payroll.view')
                ->name('index');

            Route::get('/{payrollPeriod}', [PayrollPeriodApprovalController::class, 'show'])
                ->middleware('permission:payroll.view')
                ->name('show');

            Route::post('/{payrollPeriod}/submit', [PayrollPeriodApprovalController::class, 'submit'])
                ->middleware('permission:payroll.process')
                ->name('submit');

            Route::post('/{payrollPeriod}/approve', [PayrollPeriodApprovalController::class, 'approve'])
                ->middleware('permission:payroll.approve')
                ->name('approve');

            Route::post('/{payrollPeriod}/reject', [PayrollPeriodApprovalController::class, 'reject'])
                ->middleware('permission:payroll.reject')
                ->name('reject');

            Route::post('/{payrollPeriod}/resubmit', [PayrollPeriodApprovalController::class, 'resubmit'])
                ->middleware('permission:payroll.process')
                ->name('resubmit');
        });

        /*
    |--------------------------------------------------------------------------
    | FEE PAYMENTS
    |--------------------------------------------------------------------------
    | IMPORTANT: Put the fixed AJAX routes BEFORE Route::resource().
    | Otherwise /fee-payments/get-students-by-class can be captured by the
    | resource show route as {fee_payment}.
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/fee-payments/get-students-by-class',
        [FeePaymentController::class, 'getStudentsByClass']
    )->middleware('permission:payments.create')
->name('fee-payments.get-students-by-class');

    Route::get(
        '/fee-payments/get-student-details/{studentId}',
        [FeePaymentController::class, 'getStudentDetails']
    )->middleware('permission:payments.create')
->name('fee-payments.get-student-details');

    Route::get(
        '/fee-payments/get-student-bill-sheets',
        [FeePaymentController::class, 'getStudentBillSheets']
    )->middleware('permission:payments.create')
->name('fee-payments.get-student-bill-sheets');

    Route::get(
        '/fee-payments/get-bill-sheet-total/{studentId}',
        [FeePaymentController::class, 'getBillSheetTotal']
    )->middleware('permission:payments.create')
->name('fee-payments.get-bill-sheet-total');

    Route::get(
        '/fee-payments/get-bill-sheet-items',
        [FeePaymentController::class, 'getBillSheetItems']
    )->middleware('permission:payments.create')
->name('fee-payments.get-bill-sheet-items');

    // Resource routes MUST come after the specific GET routes above.
    Route::resource('fee-payments', FeePaymentController::class)
        ->middlewareFor(['index', 'show'], 'permission:payments.view')
        ->middlewareFor(['create', 'store'], 'permission:payments.create')
        ->middlewareFor(['edit', 'update'], 'permission:payments.edit')
        ->middlewareFor('destroy', 'permission:payments.delete');

    Route::get(
        '/fee-payments/{id}/receipt',
        [FeePaymentController::class, 'printReceipt']
    )->middleware('permission:payments.receipts')
->name('fee-payments.receipt');

    Route::get(
        '/fee-payments/{id}/receipt/pdf',
        [FeePaymentController::class, 'receiptPdf']
    )->middleware('permission:payments.receipts')
->name('fee-payments.receipt.pdf');

    Route::get(
        '/fee-payments/{id}/receipt/download',
        [FeePaymentController::class, 'downloadReceipt']
    )->middleware('permission:payments.receipts')
->name('fee-payments.receipt.download');

    Route::get('/fee-payment-reports/school-overview',
    [FeePaymentReportController::class, 'schoolOverview']
    )->middleware('permission:fees.reports')
->name('fee.payment.reports.school-overview');



    /*
    |--------------------------------------------------------------------------
    | ANNOUNCEMENTS - ADMIN / STAFF
    |--------------------------------------------------------------------------
    */

    Route::resource('announcements', AnnouncementController::class)
        ->middlewareFor(['index', 'show'], 'permission:announcements.view')
        ->middlewareFor(['create', 'store'], 'permission:announcements.create')
        ->middlewareFor(['edit', 'update'], 'permission:announcements.edit')
        ->middlewareFor('destroy', 'permission:announcements.delete');

    Route::post('/announcements/bulk-delete', [AnnouncementController::class, 'bulkDelete'])
        ->middleware('permission:announcements.delete')
        ->name('announcements.bulk-delete');

    Route::post('/announcements/bulk-expire', [AnnouncementController::class, 'bulkExpire'])
        ->middleware('permission:announcements.edit')
        ->name('announcements.bulk-expire');

    Route::patch('/announcements/{announcement}/toggle-status', [AnnouncementController::class, 'toggleStatus'])
        ->middleware('permission:announcements.edit')
        ->name('announcements.toggle-status');

    Route::patch('/announcements/{announcement}/toggle-featured', [AnnouncementController::class, 'toggleFeatured'])
        ->middleware('permission:announcements.edit')
        ->name('announcements.toggle-featured');

    Route::put('/announcements/{announcement}/expire', [AnnouncementController::class, 'expire'])
        ->middleware('permission:announcements.publish')
        ->name('announcements.expire');

    Route::put('/announcements/{announcement}/restore', [AnnouncementController::class, 'restore'])
        ->middleware('permission:announcements.edit')
        ->name('announcements.restore');

    Route::get('/api/announcements', [AnnouncementController::class, 'getAnnouncements'])
        ->middleware('permission:announcements.view')
        ->name('announcements.api');

    /*
    |--------------------------------------------------------------------------
    | BILL SHEET APPROVAL CENTER
    |--------------------------------------------------------------------------
    */

    Route::get('/bill-sheet-approvals', [BillSheetApprovalController::class, 'index'])
        ->middleware('permission:bill-sheets.view')
        ->name('bill-sheet-approvals.index');

    Route::post('/bill-sheet-approvals/approve-all', [BillSheetApprovalController::class, 'approveAll'])
        ->middleware('permission:bill-sheets.approve')
        ->name('bill-sheet-approvals.approve-all');

    Route::post('/bill-sheet-approvals/reject-all', [BillSheetApprovalController::class, 'rejectAll'])
        ->middleware('permission:bill-sheets.reject')
        ->name('bill-sheet-approvals.reject-all');

    Route::post('/bill-sheet-approvals/{billSheet}/approve', [BillSheetApprovalController::class, 'approve'])
        ->middleware('permission:bill-sheets.approve')
        ->name('bill-sheet-approvals.approve');

    Route::post('/bill-sheet-approvals/{billSheet}/reject', [BillSheetApprovalController::class, 'reject'])
        ->middleware('permission:bill-sheets.reject')
        ->name('bill-sheet-approvals.reject');

    /*
    |--------------------------------------------------------------------------
    | BILL SHEETS
    |--------------------------------------------------------------------------
    */

    Route::prefix('api')->group(function () {
        Route::get('/student-count', [BillSheetController::class, 'getStudentCount'])
            ->middleware('permission:bill-sheets.view')
            ->name('api.student-count');
    });

    Route::get('/bill-sheets/assignments', [BillSheetController::class, 'assignments'])
        ->middleware('permission:bill-sheets.view')
        ->name('bill-sheets.assignments');

    Route::resource('bill-sheets', BillSheetController::class)
        ->middlewareFor(['index', 'show'], 'permission:bill-sheets.view')
        ->middlewareFor(['create', 'store'], 'permission:bill-sheets.create')
        ->middlewareFor(['edit', 'update'], 'permission:bill-sheets.edit')
        ->middlewareFor('destroy', 'permission:bill-sheets.delete');

    Route::post('/bill-sheets/{id}/toggle-status', [BillSheetController::class, 'toggleStatus'])
        ->middleware('permission:bill-sheets.edit')
        ->name('bill-sheets.toggle-status');

    Route::get('/bill-sheets/{id}/pdf', [BillSheetController::class, 'pdf'])
        ->middleware('permission:bill-sheets.view')
        ->name('bill-sheets.pdf');

    Route::get('/bill-sheets/{id}/print', [BillSheetController::class, 'print'])
        ->middleware('permission:bill-sheets.view')
        ->name('bill-sheets.print');

    Route::get('/bill-sheets/{id}/duplicate', [BillSheetController::class, 'duplicate'])
        ->middleware('permission:bill-sheets.create')
        ->name('bill-sheets.duplicate');

    Route::get('/bill-sheets/{id}/export', [BillSheetController::class, 'export'])
        ->middleware('permission:fees.export')
        ->name('bill-sheets.export');

    Route::post('/bill-sheets/{billSheet}/regenerate', [BillSheetController::class, 'regenerate'])
        ->middleware('permission:bill-sheets.edit')
        ->name('bill-sheets.regenerate');

    /*
    |--------------------------------------------------------------------------
    | CLASS MANAGEMENT
    |--------------------------------------------------------------------------
    */

    Route::prefix('classes')->name('classes.')->group(function () {
        Route::post('/{class}/subjects', [StudentClassController::class, 'attachSubject'])
            ->middleware('permission:classes.assign-subject')
            ->name('subject.attach');

        Route::delete('/{class}/subjects/{subject}', [StudentClassController::class, 'detachSubject'])
            ->middleware('permission:classes.assign-subject')
            ->name('subject.detach');

        Route::get('/{class}/attendance-dashboard', [StudentClassController::class, 'attendanceDashboard'])
            ->middleware('permission:attendance.view')
            ->name('attendance.dashboard');
    });

    Route::prefix('student-classes')->name('student-classes.')->group(function () {
        Route::post('/{studentClass}/assign-subject-teacher', [StudentClassController::class, 'assignSubjectTeacher'])
            ->middleware('permission:classes.assign-staff')
            ->name('assign-subject-teacher');

        Route::delete('/{studentClass}/remove-subject-teacher/{subject}', [StudentClassController::class, 'removeSubjectTeacher'])
            ->middleware('permission:classes.assign-staff')
            ->name('remove-subject-teacher');

        Route::post('/{studentClass}/assign-prefect', [StudentClassController::class, 'assignPrefect'])
            ->middleware('permission:classes.assign-prefect')
            ->name('assign-prefect');

        Route::get('/{class}/attendance-data', [StudentClassController::class, 'getAttendanceData'])
            ->middleware('permission:attendance.view')
            ->name('attendance-data');
    });

    /*
    |--------------------------------------------------------------------------
    | ATTENDANCE
    |--------------------------------------------------------------------------
    */

    Route::resource('attendance-sessions', AttendanceSessionController::class)
        ->middlewareFor(['index', 'show'], 'permission:attendance.view')
        ->middlewareFor(['create', 'store'], 'permission:attendance.create')
        ->middlewareFor(['edit', 'update'], 'permission:attendance.edit')
        ->middlewareFor('destroy', 'permission:attendance.delete');
        Route::resource('attendance-settings', AttendanceSettingController::class)
        ->middlewareFor(['index', 'show'], 'permission:attendance-settings.view')
        ->middlewareFor(['create', 'store'], 'permission:attendance-settings.create')
        ->middlewareFor(['edit', 'update'], 'permission:attendance-settings.edit')
        ->middlewareFor('destroy', 'permission:attendance-settings.delete')
        ->middlewareFor(['index', 'show'], 'permission:attendance.view')
        ->middlewareFor(['create', 'store'], 'permission:attendance.create')
        ->middlewareFor(['edit', 'update'], 'permission:attendance.edit')
        ->middlewareFor('destroy', 'permission:attendance.delete');

    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/create-for-class/{studentClassId}', [AttendanceSessionController::class, 'createForClass'])
            ->middleware('permission:attendance.create')
            ->name('create-for-class');

        Route::get('/check-exists', [AttendanceSessionController::class, 'checkExists'])
            ->middleware('permission:attendance.view')
            ->name('check-exists');

        Route::get('/class/{classId}/students', [AttendanceSessionController::class, 'getStudents'])
            ->middleware('permission:attendance.view')
            ->name('class.students');

        Route::get('/session/ajax', [AttendanceSessionController::class, 'ajax'])
            ->middleware('permission:attendance.view')
            ->name('session.ajax');

        Route::get('/{classId}/load', [AttendanceSessionController::class, 'loadAttendance'])
            ->middleware('permission:attendance.view')
            ->name('session.load');

        Route::post('/ajax/{classId}', [AttendanceSessionController::class, 'getAttendanceData'])
            ->middleware('permission:attendance.view')
            ->name('ajax');

        Route::post('/store/{classId}', [AttendanceSessionController::class, 'storeForClass'])
            ->middleware('permission:attendance.create')
            ->name('store.class');

        Route::get('/monthly-report', [AttendanceSessionController::class, 'monthlyReport'])
            ->middleware('permission:attendance.reports')
            ->name('monthly-report');

        Route::get('/export-monthly', [AttendanceSessionController::class, 'exportMonthlyReport'])
            ->middleware('permission:attendance.export')
            ->name('export-monthly');
    });

    /*
    |--------------------------------------------------------------------------
    | STAFF ATTENDANCE
    |--------------------------------------------------------------------------
    */

    Route::resource('staff-attendance', StaffAttendanceController::class)
        ->middlewareFor(['index', 'show'], 'permission:staff-attendance.view')
        ->middlewareFor(['create', 'store'], 'permission:staff-attendance.create')
        ->middlewareFor(['edit', 'update'], 'permission:staff-attendance.edit')
        ->middlewareFor('destroy', 'permission:staff-attendance.delete');

    Route::get('staff-attendance-dashboard', [StaffAttendanceController::class, 'dashboard'])
        ->middleware('permission:staff-attendance.view')
        ->name('staff-attendance.dashboard');

    Route::post('staff-attendance/gps-clock-in', [StaffAttendanceController::class, 'gpsClockIn'])
        ->middleware('permission:staff-attendance.create')
        ->name('staff-attendance.gps-clock-in');

    Route::post('staff-attendance/gps-clock-out', [StaffAttendanceController::class, 'gpsClockOut'])
        ->middleware('permission:staff-attendance.edit')
        ->name('staff-attendance.gps-clock-out');

    Route::get('staffattendance-live-map', [StaffAttendanceController::class, 'liveMap'])
        ->middleware('permission:staff-attendance.view')
        ->name('staff-attendance.live-map');

    Route::get('/staffattendance/monthly-report', [StaffAttendanceController::class, 'monthlyReport'])
        ->middleware('permission:staff-attendance.reports')
        ->name('staffattendance.monthly-report');

    Route::get('/staffattendance/export-monthly', [StaffAttendanceController::class, 'exportMonthlyReport'])
        ->middleware('permission:staff-attendance.export')
        ->name('staffattendance.export-monthly');

    /*
    |--------------------------------------------------------------------------
    | FEE SETUP
    |--------------------------------------------------------------------------
    */

    Route::resource('class-fee-structures', ClassFeeStructureController::class)
        ->middlewareFor(['index', 'show'], 'permission:fee-structures.view')
        ->middlewareFor(['create', 'store'], 'permission:fee-structures.create')
        ->middlewareFor(['edit', 'update'], 'permission:fee-structures.edit')
        ->middlewareFor('destroy', 'permission:fee-structures.delete');

    Route::prefix('fee-payment-reports')
        ->name('fee.payment.reports.')
        ->group(function () {
            Route::get('/', [FeePaymentReportController::class, 'index'])->middleware('permission:fees.reports')
->name('index');
            Route::get('/{id}', [FeePaymentReportController::class, 'show'])->middleware('permission:fees.reports')
->name('show');
            Route::get('/export/pdf', [FeePaymentReportController::class, 'exportPdf'])->middleware('permission:fees.export')
->name('export.pdf');
            Route::get('/export/excel', [FeePaymentReportController::class, 'exportExcel'])->middleware('permission:fees.export')
->name('export.excel');
            Route::get('/payment-history', [FeePaymentReportController::class, 'paymentHistory'])->middleware('permission:payments.reports')
->name('payment-history');
            Route::get('/receipt/{paymentId}', [FeePaymentReportController::class, 'generateReceipt'])->middleware('permission:payments.receipts')
->name('receipt');
            Route::get('/outstanding', [FeePaymentReportController::class, 'outstandingFees'])->middleware('permission:fees.reports')
->name('outstanding');
            Route::get('/api/summary-stats', [FeePaymentReportController::class, 'getSummaryStats'])->middleware('permission:fees.reports')
->name('api.summary');
            Route::get('/api/chart-data', [FeePaymentReportController::class, 'getChartData'])->middleware('permission:fees.reports')
->name('api.chart');
        });
        

    /*
    |--------------------------------------------------------------------------
    | FEE MANAGEMENT
    |--------------------------------------------------------------------------
    */

    Route::prefix('fee-structures')->name('fee-structures.')->group(function () {
        Route::get('/', [FeeStructureController::class, 'index'])->middleware('permission:fee-structures.view')
->name('index');
        Route::get('/create', [FeeStructureController::class, 'create'])->middleware('permission:fee-structures.create')
->name('create');
        Route::post('/', [FeeStructureController::class, 'store'])->middleware('permission:fee-structures.create')
->name('store');
        Route::get('/{id}', [FeeStructureController::class, 'show'])->middleware('permission:fee-structures.view')
->name('show');
        Route::get('/{id}/edit', [FeeStructureController::class, 'edit'])->middleware('permission:fee-structures.edit')
->name('edit');
        Route::put('/{id}', [FeeStructureController::class, 'update'])->middleware('permission:fee-structures.edit')
->name('update');
        Route::delete('/{id}', [FeeStructureController::class, 'destroy'])->middleware('permission:fee-structures.delete')
->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | STUDENT PROGRESSION
    |--------------------------------------------------------------------------
    */

    Route::prefix('student-progressions')->name('student-progressions.')->group(function () {
        Route::get('/', [StudentProgressionController::class, 'index'])->middleware('permission:progression.view')
->name('index');
        Route::post('/process', [StudentProgressionController::class, 'process'])->middleware('permission:progression.process')
->name('process');
        Route::post('/bulk-promote', [StudentProgressionController::class, 'bulkPromote'])->middleware('permission:progression.bulk-promote')
->name('bulk-promote');
    });

    /*
    |--------------------------------------------------------------------------
    | PAYSLIPS
    |--------------------------------------------------------------------------
    */

    Route::get('/payslips', [PayslipController::class, 'index'])->middleware('permission:payslips.view')
->name('payslips.index');
    Route::get('/payslips/create', [PayslipController::class, 'create'])->middleware('permission:payslips.create')
->name('payslips.create');
    Route::post('/payslips', [PayslipController::class, 'store'])->middleware('permission:payslips.create')
->name('payslips.store');

    Route::get('/payslips/staff-salary-data', [PayslipController::class, 'getStaffSalaryData'])
        ->middleware('permission:salary-structures.view')
        ->name('payslips.staff-salary-data');

    Route::get('/payslips/staff-salary-structures', [PayslipController::class, 'getStaffSalaryStructures'])
        ->middleware('permission:salary-structures.view')
        ->name('payslips.staff-salary-structures');

    Route::get('/payslips/filter', [PayslipController::class, 'filter'])
        ->middleware('permission:payslips.view')
        ->name('payslips.filter');

    Route::get('/payslips/{payslip}', [PayslipController::class, 'show'])
        ->middleware('permission:payslips.view')
        ->name('payslips.show');

    Route::delete('/payslips/{payslip}', [PayslipController::class, 'destroy'])
        ->middleware('permission:payslips.create')
        ->name('payslips.destroy');

    Route::get('/payslips/{payslip}/export-pdf', [PayslipController::class, 'exportPdf'])
        ->middleware('permission:payslips.download')
        ->name('payslips.export-pdf');

    Route::get('/payslips/{payslip}/pdf', [PayslipController::class, 'exportPdf'])
        ->middleware('permission:payslips.download')
        ->name('payslips.pdf');

    Route::post('/payslips/bulk-generate', [PayslipController::class, 'bulkGenerate'])
        ->middleware('permission:payslips.create')
        ->name('payslips.bulk-generate');

    Route::get('/staff/{staff}/payslips', [PayslipController::class, 'staffPayslips'])
        ->middleware('permission:payslips.view')
        ->name('staff.payslips');

    /*
    |--------------------------------------------------------------------------
    | SALARY STRUCTURES
    |--------------------------------------------------------------------------
    */

    Route::resource('salary-structures', SalaryStructureController::class)
        ->middlewareFor(['index', 'show'], 'permission:salary-structures.view')
        ->middlewareFor(['create', 'store'], 'permission:salary-structures.create')
        ->middlewareFor(['edit', 'update'], 'permission:salary-structures.edit')
        ->middlewareFor('destroy', 'permission:salary-structures.delete');

    /*
    |--------------------------------------------------------------------------
    | SCORES & RESULTS
    |--------------------------------------------------------------------------
    */

    Route::prefix('scores')->name('scores.')->group(function () {
        Route::get('/', [ScoreController::class, 'index'])->middleware('permission:results.view')
->name('index');
        Route::match(['get', 'post'], '/load-students', [ScoreController::class, 'loadStudents'])
            ->middleware('permission:results.view')
            ->name('load-students');
        Route::post('/save', [ScoreController::class, 'save'])->middleware('permission:results.create')
->name('save');
    });

    Route::get('/subject-results', [SubjectResultController::class, 'index'])
        ->middleware('permission:results.reports')
        ->name('subject-results.index');

    /*
    |--------------------------------------------------------------------------
    | BROADSHEET
    |--------------------------------------------------------------------------
    */

    Route::prefix('broadsheet')->name('broadsheet.')->group(function () {
        Route::get('/', [BroadsheetController::class, 'index'])->middleware('permission:broadsheet.view')
->name('index');
        Route::post('/generate', [BroadsheetController::class, 'generate'])->middleware('permission:broadsheet.generate')
->name('generate');
        Route::post('/pdf', [BroadsheetController::class, 'pdf'])->middleware('permission:broadsheet.export')
->name('pdf');
        Route::post('/ajax', [BroadsheetController::class, 'ajaxLoad'])->middleware('permission:broadsheet.view')
->name('ajax');
    });

    /*
    |--------------------------------------------------------------------------
    | TIMETABLES
    |--------------------------------------------------------------------------
    */

    Route::resource('timetables', TimetableController::class)
        ->middlewareFor(['index', 'show'], 'permission:timetables.view')
        ->middlewareFor(['create', 'store'], 'permission:timetables.create')
        ->middlewareFor(['edit', 'update'], 'permission:timetables.edit')
        ->middlewareFor('destroy', 'permission:timetables.delete');

    Route::get('/timetables/{timetable}/preview', [TimetableController::class, 'preview'])
        ->middleware('permission:timetables.view')
        ->name('timetables.preview');
    
    Route::get('/timetables/{timetable}/download', [TimetableController::class, 'download'])
        ->middleware('permission:timetables.download')
        ->name('timetables.download');

    /*
    |--------------------------------------------------------------------------
    | REPORT CARDS
    |--------------------------------------------------------------------------
    */

    Route::prefix('report-cards')->name('report-cards.')->group(function () {
        Route::get('/', [ReportCardController::class, 'index'])->middleware('permission:report-cards.view')
->name('index');
        Route::post('/show', [ReportCardController::class, 'show'])->middleware('permission:report-cards.view')
->name('show');
        Route::get('/get-students-by-class', [ReportCardController::class, 'getStudentsByClass'])
            ->middleware('permission:report-cards.view')
            ->name('get-students-by-class');
        Route::get('/{student}', [ReportCardController::class, 'reportCard'])
            ->middleware('permission:report-cards.view')
            ->name('show-single');
    });

    Route::get('/get-students-by-class', [ReportCardController::class, 'getStudentsByClass'])
        ->middleware('permission:report-cards.view')
        ->name('get.students.by.class');

    /*
    |--------------------------------------------------------------------------
    | GRADUATED STUDENTS
    |--------------------------------------------------------------------------
    */

    Route::prefix('graduated-students')->name('graduates.')->group(function () {
        Route::get('/', [GraduationController::class, 'index'])->middleware('permission:graduation.view')
->name('index');
        Route::get('/export', [GraduationController::class, 'export'])->middleware('permission:graduation.export')
->name('export');
        Route::get('/print', [GraduationController::class, 'printView'])->middleware('permission:graduation.view')
->name('print');
        Route::get('/{id}', [GraduationController::class, 'show'])->middleware('permission:graduation.view')
->name('show');
        Route::get('/{id}/certificate', [GraduationController::class, 'certificate'])->middleware('permission:graduation.view')
->name('certificate');
        Route::delete('/{id}', [GraduationController::class, 'destroy'])->middleware('permission:graduation.process')
->name('destroy');
        Route::post('/{id}/restore', [GraduationController::class, 'restore'])->middleware('permission:graduation.restore')
->name('restore');
    });

    /*
    |--------------------------------------------------------------------------
    | LESSON NOTES
    |--------------------------------------------------------------------------
    */

    Route::resource('lesson-notes', LessonNoteController::class)
        ->middlewareFor(['index', 'show'], 'permission:lesson-notes.view')
        ->middlewareFor(['create', 'store'], 'permission:lesson-notes.create')
        ->middlewareFor(['edit', 'update'], 'permission:lesson-notes.edit')
        ->middlewareFor('destroy', 'permission:lesson-notes.delete');

    Route::post('/lesson-notes/{id}/clone', [LessonNoteController::class, 'clone'])
        ->middleware('permission:lesson-notes.clone')
        ->name('lesson-notes.clone');

    Route::post('/lesson-notes/{id}/comments', [LessonNoteController::class, 'storeComment'])
        ->middleware('permission:lesson-notes.comment')
        ->name('lesson-notes.comments.store');

    Route::get('/lesson-notes/{id}/download', [LessonNoteController::class, 'download'])
        ->middleware('permission:lesson-notes.download')
        ->name('lesson-notes.download');

    Route::get('/lesson-notes/{lessonNote}/download-attachment/{file}', [LessonNoteController::class, 'downloadAttachment'])
        ->middleware('permission:lesson-notes.download')
        ->name('lesson-notes.download-attachment');

    /*
    |--------------------------------------------------------------------------
    | LESSON NOTE APPROVALS
    |--------------------------------------------------------------------------
    */

    Route::get('approvals', [LessonNoteApprovalController::class, 'index'])
        ->middleware('permission:lesson-notes.approve')
        ->name('approvals.index');

    Route::get('approvals/{id}', [LessonNoteApprovalController::class, 'show'])
        ->middleware('permission:lesson-notes.approve')
        ->name('approvals.show');

    Route::post('approvals/{id}/approve', [LessonNoteApprovalController::class, 'approve'])
        ->middleware('permission:lesson-notes.approve')
        ->name('approvals.approve');

    Route::post('approvals/{id}/reject', [LessonNoteApprovalController::class, 'reject'])
        ->middleware('permission:lesson-notes.reject')
        ->name('approvals.reject');

    Route::post('approvals/{id}/request-changes', [LessonNoteApprovalController::class, 'requestChanges'])
        ->middleware('permission:lesson-notes.reject')
        ->name('approvals.request-changes');

    /*
    |--------------------------------------------------------------------------
    | ASSESSMENT FORMS
    |--------------------------------------------------------------------------
    */

    Route::prefix('assessment-forms')->name('assessment-forms.')->group(function () {
        Route::get('/', [AssessmentFormController::class, 'index'])->middleware('permission:assessment-forms.view')
->name('index');
        Route::get('/create', [AssessmentFormController::class, 'create'])->middleware('permission:assessment-forms.create')
->name('create');
        Route::post('/', [AssessmentFormController::class, 'store'])->middleware('permission:assessment-forms.create')
->name('store');
        Route::get('/{id}', [AssessmentFormController::class, 'show'])->middleware('permission:assessment-forms.view')
->name('show');
        Route::get('/{id}/edit', [AssessmentFormController::class, 'edit'])->middleware('permission:assessment-forms.edit')
->name('edit');
        Route::put('/{id}', [AssessmentFormController::class, 'update'])->middleware('permission:assessment-forms.edit')
->name('update');
        Route::delete('/{id}', [AssessmentFormController::class, 'destroy'])->middleware('permission:assessment-forms.delete')
->name('destroy');
        Route::get('/{id}/download', [AssessmentFormController::class, 'download'])->middleware('permission:assessment-forms.download')
->name('download');
        Route::get('/{id}/view', [AssessmentFormController::class, 'view'])->middleware('permission:assessment-forms.view')
->name('view');
        Route::post('/{id}/toggle-status', [AssessmentFormController::class, 'toggleStatus'])->middleware('permission:assessment-forms.change-status')
->name('toggle-status');
    });

    /*
    |--------------------------------------------------------------------------
    | ASSETS
    |--------------------------------------------------------------------------
    */

    Route::prefix('assets')->name('assets.')->group(function () {
        Route::get('/', [AssetController::class, 'index'])->middleware('permission:assets.view')
->name('index');
        Route::get('/create', [AssetController::class, 'create'])->middleware('permission:assets.create')
->name('create');
        Route::post('/', [AssetController::class, 'store'])->middleware('permission:assets.create')
->name('store');
        Route::get('/{id}', [AssetController::class, 'show'])->middleware('permission:assets.view')
->name('show');
        Route::get('/{id}/edit', [AssetController::class, 'edit'])->middleware('permission:assets.edit')
->name('edit');
        Route::put('/{id}', [AssetController::class, 'update'])->middleware('permission:assets.edit')
->name('update');
        Route::delete('/{id}', [AssetController::class, 'destroy'])->middleware('permission:assets.delete')
->name('destroy');
        Route::post('/{id}/assign', [AssetController::class, 'assign'])->middleware('permission:assets.assign')
->name('assign');
        Route::post('/{id}/return', [AssetController::class, 'returnAsset'])->middleware('permission:assets.return')
->name('return');
        Route::get('/{id}/download-document', [AssetController::class, 'downloadDocument'])
            ->middleware('permission:assets.view')
            ->name('download.document');
        Route::get('/{id}/download-image', [AssetController::class, 'downloadImage'])
            ->middleware('permission:assets.view')
            ->name('download.image');
    });

    /*
    |--------------------------------------------------------------------------
    | LEAVES
    |--------------------------------------------------------------------------
    */

    Route::prefix('leaves')->name('leaves.')->group(function () {
        Route::get('/', [LeavesController::class, 'index'])->middleware('permission:leaves.view')
->name('index');
        Route::get('/create', [LeavesController::class, 'create'])->middleware('permission:leaves.create')
->name('create');
        Route::post('/', [LeavesController::class, 'store'])->middleware('permission:leaves.create')
->name('store');
        Route::get('/{id}/edit', [LeavesController::class, 'edit'])->middleware('permission:leaves.edit')
->name('edit');
        Route::put('/{id}', [LeavesController::class, 'update'])->middleware('permission:leaves.edit')
->name('update');
        Route::delete('/{id}', [LeavesController::class, 'destroy'])->middleware('permission:leaves.delete')
->name('destroy');
        Route::get('/{id}/watch', [LeavesController::class, 'watch'])->middleware('permission:leaves.view')
->name('watch');
        Route::get('/{id}/download-pdf', [LeavesController::class, 'downloadPDF'])->middleware('permission:leaves.view')
->name('download.pdf');
        Route::get('/{id}/download-word', [LeavesController::class, 'downloadWord'])->middleware('permission:leaves.view')
->name('download.word');
        Route::post('/{id}/approve', [LeavesController::class, 'approve'])->middleware('permission:leaves.approve')
->name('approve');
        Route::post('/{id}/reject', [LeavesController::class, 'reject'])->middleware('permission:leaves.reject')
->name('reject');
        Route::post('/{id}/submit', [LeavesController::class, 'submit'])->middleware('permission:leaves.create')
->name('leaves.submit');
    });

    /*
    |--------------------------------------------------------------------------
    | LEAVE APPROVALS
    |--------------------------------------------------------------------------
    */

    Route::prefix('leave-approvals')
        ->name('leave-approvals.')
        ->group(function () {
            Route::get('/', [LeaveApprovalController::class, 'index'])->middleware('permission:leaves.approve')
->name('index');
            Route::get('/{leave}', [LeaveApprovalController::class, 'show'])->middleware('permission:leaves.approve')
->name('show');
            Route::post('/{leave}/approve', [LeaveApprovalController::class, 'approve'])->middleware('permission:leaves.approve')
->name('approve');
            Route::post('/{leave}/reject', [LeaveApprovalController::class, 'reject'])->middleware('permission:leaves.reject')
->name('reject');
            Route::post('/{leave}/modify-approve', [LeaveApprovalController::class, 'modifyAndApprove'])
                ->middleware('permission:leaves.approve')
                ->name('modify-approve');
        });

    /*
    |--------------------------------------------------------------------------
    | STAFF APPRAISALS
    |--------------------------------------------------------------------------
    */

    Route::prefix('staff-appraisals')->name('staff-appraisals.')->group(function () {
        Route::get('/', [StaffAppraisalController::class, 'index'])->middleware('permission:appraisals.view')
->name('index');
        Route::get('/create', [StaffAppraisalController::class, 'create'])->middleware('permission:appraisals.create')
->name('create');
        Route::post('/', [StaffAppraisalController::class, 'store'])->middleware('permission:appraisals.create')
->name('store');
        Route::get('/{id}', [StaffAppraisalController::class, 'show'])->middleware('permission:appraisals.view')
->name('show');
        Route::get('/{id}/edit', [StaffAppraisalController::class, 'edit'])->middleware('permission:appraisals.edit')
->name('edit');
        Route::put('/{id}', [StaffAppraisalController::class, 'update'])->middleware('permission:appraisals.edit')
->name('update');
        Route::delete('/{id}', [StaffAppraisalController::class, 'destroy'])->middleware('permission:appraisals.delete')
->name('destroy');
        Route::get('/{id}/download', [StaffAppraisalController::class, 'download'])->middleware('permission:appraisals.view')
->name('download');
        Route::get('/{id}/view', [StaffAppraisalController::class, 'view'])->middleware('permission:appraisals.view')
->name('view');
        Route::post('/{id}/toggle-status', [StaffAppraisalController::class, 'toggleStatus'])->middleware('permission:appraisals.edit')
->name('toggle-status');
        Route::post('/{id}/review', [StaffAppraisalController::class, 'review'])->middleware('permission:appraisals.review')
->name('review');
    });

    /*
    |--------------------------------------------------------------------------
    | DISCUSSIONS
    |--------------------------------------------------------------------------
    */

    Route::prefix('discussions')->name('discussions.')->group(function () {
        Route::get('/', [DiscussionController::class, 'index'])->middleware('permission:discussions.view')
->name('index');
        Route::get('/create', [DiscussionController::class, 'create'])->middleware('permission:discussions.create')
->name('create');
        Route::post('/', [DiscussionController::class, 'store'])->middleware('permission:discussions.create')
->name('store');
        Route::get('/{slug}', [DiscussionController::class, 'show'])->middleware('permission:discussions.view')
->name('show');
        Route::post('/{slug}/message', [DiscussionController::class, 'sendMessage'])->middleware('permission:discussions.participate')
->name('message.send');
        Route::put('/message/{id}', [DiscussionController::class, 'editMessage'])->middleware('permission:discussions.edit')
->name('message.edit');
        Route::delete('/message/{id}', [DiscussionController::class, 'deleteMessage'])->middleware('permission:discussions.delete')
->name('message.delete');
        Route::post('/{slug}/participant', [DiscussionController::class, 'addParticipant'])->middleware('permission:discussions.participate')
->name('participant.add');
        Route::delete('/{slug}/participant/{staffId}', [DiscussionController::class, 'removeParticipant'])
            ->middleware('permission:discussions.participate')
            ->name('participant.remove');
        Route::get('/attachment/{id}/download', [DiscussionController::class, 'downloadAttachment'])
            ->middleware('permission:discussions.view')
            ->name('attachment.download');
    });

    /*
    |--------------------------------------------------------------------------
    | STAFF GRIEVANCES
    |--------------------------------------------------------------------------
    */

    Route::prefix('grievance')->name('grievance.')->group(function () {
        Route::get('/', [GrievanceController::class, 'index'])->middleware('permission:grievances.view')
->name('index');
        Route::get('/create', [GrievanceController::class, 'create'])->middleware('permission:grievances.create')
->name('create');
        Route::post('/', [GrievanceController::class, 'store'])->middleware('permission:grievances.create')
->name('store');
        Route::get('/{id}', [GrievanceController::class, 'show'])->middleware('permission:grievances.view')
->name('show');
        Route::get('/{id}/edit', [GrievanceController::class, 'edit'])->middleware('permission:grievances.edit')
->name('edit');
        Route::put('/{id}', [GrievanceController::class, 'update'])->middleware('permission:grievances.edit')
->name('update');
        Route::delete('/{id}', [GrievanceController::class, 'destroy'])->middleware('permission:grievances.delete')
->name('destroy');
        Route::post('/{id}/assign', [GrievanceController::class, 'assign'])->middleware('permission:grievances.assign')
->name('assign');
        Route::post('/{id}/status', [GrievanceController::class, 'updateStatus'])->middleware('permission:grievances.status')
->name('update-status');
        Route::post('/{id}/comments', [GrievanceController::class, 'addComment'])->middleware('permission:grievances.view')
->name('add-comment');
        Route::post('/{id}/escalate', [GrievanceController::class, 'escalate'])->middleware('permission:grievances.escalate')
->name('escalate');
        Route::post('/{id}/appeal', [GrievanceController::class, 'appeal'])->middleware('permission:grievances.appeal')
->name('appeal');
        Route::get('/statistics', [GrievanceController::class, 'statistics'])->middleware('permission:grievances.view')
->name('statistics');
    });
});

/*
|--------------------------------------------------------------------------
| STUDENT GRIEVANCES
|--------------------------------------------------------------------------
| Kept outside auth:web because the supplied route file explicitly placed
| these routes outside that group. No duplicate route is removed here.
|--------------------------------------------------------------------------
*/

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
| STUDENT PORTAL
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:student'])->group(function () {

    Route::get('/student/dashboard', [StudentDashboardController::class, 'index'])
        ->name('students.dashboard');

    Route::get('/student/profile', [StudentDashboardController::class, 'profile'])
        ->name('students.profile');

    Route::get('/student/attendance', [StudentDashboardController::class, 'attendance'])
        ->name('students.attendance');

    Route::get('/student/results', [StudentDashboardController::class, 'results'])
        ->name('students.results');

    Route::get('/student/academic-history', [StudentDashboardController::class, 'academicHistory'])
        ->name('students.academic-history');

    Route::get('/student/class-history', [StudentDashboardController::class, 'classHistory'])
        ->name('students.class-history');

    Route::get('/student/timetable', [StudentDashboardController::class, 'timetable'])
        ->name('students.timetable');

    Route::get('/student/fees', [StudentDashboardController::class, 'fees'])
        ->name('students.fees');

    Route::get('/student/settings', [StudentDashboardController::class, 'settings'])
        ->name('students.settings');

    Route::get('/student/report-card', [ReportCardController::class, 'studentReportCard'])
        ->name('student.report-card');

    Route::prefix('student/api')->name('students.api.')->group(function () {
        Route::get('/class-history', [StudentDashboardController::class, 'getClassHistoryApi'])
            ->name('class-history');

        Route::get('/class-performance', [StudentDashboardController::class, 'getClassPerformanceApi'])
            ->name('class-performance');
    });

    Route::prefix('student/timetable')->name('students.timetable.')->group(function () {
        Route::get('/view/{id}', [StudentDashboardController::class, 'viewTimetable'])
            ->name('view');

        Route::get('/download/{id}', [StudentDashboardController::class, 'downloadTimetable'])
            ->name('download');

        Route::get('/stream/{id}', [StudentDashboardController::class, 'streamTimetable'])
            ->name('stream');

        Route::get('/info/{id}', [StudentDashboardController::class, 'getTimetableInfo'])
            ->name('info');

        Route::post('/switch', [StudentDashboardController::class, 'switchTimetable'])
            ->name('switch');
    });

    Route::middleware('auth:student')
    ->prefix('student')
    ->group(function () {

        Route::get(
            '/fees',
            [StudentFeesController::class, 'index']
        )->name('students.fees');

        Route::get(
            '/fees/payment',
            [StudentFeesController::class, 'payment']
        )->name('students.fees.payment');

        Route::post(
            '/fees/payment',
            [StudentFeesController::class, 'initiatePayment']
        )->name('students.fees.payment.initiate');

        Route::get(
            '/fees/receipt/{id}',
            [StudentFeesController::class, 'receipt']
        )->name('students.fees.receipt');

        Route::get(
            '/fees/receipt/{id}/pdf',
            [StudentFeesController::class, 'receiptPdf']
        )->name('students.fees.receipt.pdf');
    });

    Route::get(
        '/student/fees/payment/callback',
        [StudentFeesController::class, 'paymentCallback']
    )
        ->middleware('auth:student')
        ->name('students.fees.payment.callback');
});

/*
|--------------------------------------------------------------------------
| ROLE & PERMISSION MANAGEMENT
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:web'])->group(function () {

    Route::middleware(['can:roles.view'])->group(function () {
        Route::get('/roles-permissions', [RolePermissionController::class, 'index'])
            ->name('roles.permissions.index');
    });

    Route::middleware(['can:roles.manage-permissions'])->group(function () {
        Route::get('/roles-permissions/{role}/edit', [RolePermissionController::class, 'edit'])
            ->name('roles.permissions.edit');

        Route::put('/roles-permissions/{role}', [RolePermissionController::class, 'update'])
            ->name('roles.permissions.update');
    });
});

Route::post(
    '/paystack/webhook',
    [PaystackWebhookController::class, 'handle']
)->name('paystack.webhook');
