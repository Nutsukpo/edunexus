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
use App\Http\Controllers\PaymentController;
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
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\GrievanceController;
use App\Http\Controllers\StudentGrievanceController;

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

    // ============================================
    // BILLING & INVOICING
    // ============================================
    Route::resource('billing', BillingController::class);
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/{billing}/pdf', [BillingController::class, 'pdf'])->name('pdf');
        Route::get('/{billing}/view-pdf', [BillingController::class, 'viewPdf'])->name('view-pdf');
    });

    // ============================================
    // PAYMENTS
    // ============================================
    Route::resource('payments', PaymentController::class);
    Route::get('/payments/create/{invoice}', [PaymentController::class, 'create'])->name('payments.create');

    // ============================================
    // STUDENT PROGRESSION
    // ============================================
    Route::prefix('student-progressions')->name('student-progressions.')->group(function () {
        Route::get('/', [StudentProgressionController::class, 'index'])->name('index');
        Route::post('/process', [StudentProgressionController::class, 'process'])->name('process');
        Route::post('/bulk-promote', [StudentProgressionController::class, 'bulkPromote'])->name('bulk-promote');
    });

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
        // In routes/web.php
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

    // ============================================
    // LESSON NOTES ROUTES (Protected by auth:web)
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
    // In routes/web.php

    Route::prefix('lesson-notes')->group(function () {
        Route::get('/{lessonNote}/download-attachment/{file}', [LessonNoteController::class, 'downloadAttachment'])
            ->name('lesson-notes.download-attachment');
    });

                /*
            |--------------------------------------------------------------------------
            | Assessment Forms Routes
            |--------------------------------------------------------------------------
            */
    Route::middleware(['auth:web'])->prefix('assessment-forms')->name('assessment-forms.')->group(function () {
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
    Route::put('/assessment-forms/{id}', [AssessmentFormController::class, 'update'])->name('assessment-forms.update');

    // / ============================================
    // STUDENT CLASS HISTORY - FIXED
    // ============================================
    Route::get('/student/class-history', [StudentDashboardController::class, 'classHistory'])
        ->name('students.class-history');  // Note: using 'students.class-history'
          
    
    /*
    |--------------------------------------------------------------------------
    | STUDENT ROUTES (Protected by auth:student)
    |--------------------------------------------------------------------------
    */
   
        
        // ============================================
        // STUDENT TIMETABLE
        // ============================================
        Route::get('/timetable', [StudentDashboardController::class, 'timetable'])->name('timetable');
        Route::get('/timetable/view/{id}', [StudentDashboardController::class, 'viewTimetable'])->name('timetable.view');
        Route::get('/timetable/download/{id}', [StudentDashboardController::class, 'downloadTimetable'])->name('timetable.download');
        Route::get('/timetable/stream/{id}', [StudentDashboardController::class, 'streamTimetable'])->name('timetable.stream');
        Route::get('/timetable/info/{id}', [StudentDashboardController::class, 'getTimetableInfo'])->name('timetable.info');
        Route::post('/timetable/switch', [StudentDashboardController::class, 'switchTimetable'])->name('timetable.switch');
        
        // ============================================
        // STUDENT FEES
        // ============================================
        Route::get('/fees', [StudentDashboardController::class, 'fees'])->name('fees');
        
        // ============================================
        // STUDENT SETTINGS
        // ============================================
        Route::get('/settings', [StudentDashboardController::class, 'settings'])->name('settings');
    
    
    /*
    |--------------------------------------------------------------------------
    | STUDENT API ROUTES (Protected by auth:student)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:student'])->prefix('student/api')->name('students.api.')->group(function () {
        Route::get('/class-history', [StudentDashboardController::class, 'getClassHistoryApi'])
            ->name('class-history');
        Route::get('/class-performance', [StudentDashboardController::class, 'getClassPerformanceApi'])
            ->name('class-performance');
    });


        /*
    |--------------------------------------------------------------------------
    | Asset Management Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:web'])->prefix('assets')->name('assets.')->group(function () {
        // Main CRUD
        Route::get('/', [AssetController::class, 'index'])->name('index');
        Route::get('/create', [AssetController::class, 'create'])->name('create');
        Route::post('/', [AssetController::class, 'store'])->name('store');
        Route::get('/{id}', [AssetController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [AssetController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AssetController::class, 'update'])->name('update');
        Route::delete('/{id}', [AssetController::class, 'destroy'])->name('destroy');
        
        // Assignment
        Route::post('/{id}/assign', [AssetController::class, 'assign'])->name('assign');
        Route::post('/{id}/return', [AssetController::class, 'returnAsset'])->name('return');
        
        // Downloads
        Route::get('/{id}/download-document', [AssetController::class, 'downloadDocument'])->name('download.document');
        Route::get('/{id}/download-image', [AssetController::class, 'downloadImage'])->name('download.image');
    });

    Route::prefix('leaves')->group(function () {
        Route::get('/', [LeavesController::class, 'index'])->name('leaves.index');
        Route::get('/create', [LeavesController::class, 'create'])->name('leaves.create');
        Route::post('/', [LeavesController::class, 'store'])->name('leaves.store');
        Route::get('/{id}/edit', [LeavesController::class, 'edit'])->name('leaves.edit');
        Route::put('/{id}', [LeavesController::class, 'update'])->name('leaves.update');
        Route::delete('/{id}', [LeavesController::class, 'destroy'])->name('leaves.destroy');
    
        Route::get('/{id}/watch', [LeavesController::class, 'watch'])->name('leaves.watch');
        Route::get('/{id}/download-pdf', [LeavesController::class, 'downloadPDF'])->name('leaves.download.pdf');
        Route::get('/{id}/download-word', [LeavesController::class, 'downloadWord'])->name('leaves.download.word');
    
        Route::post('/{id}/approve', [LeavesController::class, 'approve'])->name('leaves.approve');
        Route::post('/{id}/reject', [LeavesController::class, 'reject'])->name('leaves.reject');
    });


            /*
    |--------------------------------------------------------------------------
    | Staff Appraisal Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:web'])->prefix('staff-appraisals')->name('staff-appraisals.')->group(function () {
        // Main CRUD
        Route::get('/', [StaffAppraisalController::class, 'index'])->name('index');
        Route::get('/create', [StaffAppraisalController::class, 'create'])->name('create');
        Route::post('/', [StaffAppraisalController::class, 'store'])->name('store');
        Route::get('/{id}', [StaffAppraisalController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [StaffAppraisalController::class, 'edit'])->name('edit');
        Route::put('/{id}', [StaffAppraisalController::class, 'update'])->name('update');
        Route::delete('/{id}', [StaffAppraisalController::class, 'destroy'])->name('destroy');
        
        // File Operations
        Route::get('/{id}/download', [StaffAppraisalController::class, 'download'])->name('download');
        Route::get('/{id}/view', [StaffAppraisalController::class, 'view'])->name('view');
        
        // Status Operations
        Route::post('/{id}/toggle-status', [StaffAppraisalController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{id}/review', [StaffAppraisalController::class, 'review'])->name('review');
    });

                /*
    |--------------------------------------------------------------------------
    | Discussion / Chat Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:web'])->prefix('discussions')->name('discussions.')->group(function () {
        // Groups
        Route::get('/', [DiscussionController::class, 'index'])->name('index');
        Route::get('/create', [DiscussionController::class, 'create'])->name('create');
        Route::post('/', [DiscussionController::class, 'store'])->name('store');
        Route::get('/{slug}', [DiscussionController::class, 'show'])->name('show');
        
        // Messages
        Route::post('/{slug}/message', [DiscussionController::class, 'sendMessage'])->name('message.send');
        Route::put('/message/{id}', [DiscussionController::class, 'editMessage'])->name('message.edit');
        Route::delete('/message/{id}', [DiscussionController::class, 'deleteMessage'])->name('message.delete');
        
        // Participants
        Route::post('/{slug}/participant', [DiscussionController::class, 'addParticipant'])->name('participant.add');
        Route::delete('/{slug}/participant/{staffId}', [DiscussionController::class, 'removeParticipant'])->name('participant.remove');
        
        // Attachments
        Route::get('/attachment/{id}/download', [DiscussionController::class, 'downloadAttachment'])->name('attachment.download');
    });

            // Payroll Routes
    Route::prefix('payroll')->name('payroll.')->middleware(['auth'])->group(function () {
        // Payroll Periods
        Route::get('/', [PayrollController::class, 'index'])->name('index');
        Route::get('/create', [PayrollController::class, 'create'])->name('create');
        Route::post('/', [PayrollController::class, 'store'])->name('store');
        Route::get('/{id}', [PayrollController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PayrollController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PayrollController::class, 'update'])->name('update');
        Route::delete('/{id}', [PayrollController::class, 'destroy'])->name('destroy');
        
        // Payroll Items
        Route::get('/item/{id}/edit', [PayrollController::class, 'editPayrollItem'])->name('edit-item');
        Route::put('/item/{id}', [PayrollController::class, 'updatePayrollItem'])->name('update-item');
        Route::post('/item/{id}/adjustment', [PayrollController::class, 'addAdjustment'])->name('add-adjustment');
        Route::post('/item/{id}/paid', [PayrollController::class, 'markAsPaid'])->name('mark-paid');
        Route::post('/item/{id}/payslip', [PayrollController::class, 'generatePayslip'])->name('generate-payslip');
        
        // Payslips
        Route::get('/payslip/{id}', [PayrollController::class, 'viewPayslip'])->name('view-payslip');
        Route::post('/{periodId}/payslips/generate-all', [PayrollController::class, 'generateAllPayslips'])->name('generate-all-payslips');
    });

    Route::prefix('payroll')->name('payroll.')->middleware(['auth'])->group(function () {
        // ... existing routes ...
        
        // Attendance data route (must be before the {id} routes)
        Route::get('/attendance-data', [PayrollController::class, 'getAttendanceData'])->name('attendance-data');
        
        // ... rest of routes ...
    });

        // Routes for GrievanceC
    Route::prefix('grievance')->name('grievance.')->middleware(['auth'])->group(function () {
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

                // Student Grievance Routes
    
        Route::get('/student/student-grievance/', [StudentGrievanceController::class, 'index'])->name('index');
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


        //....................................................................................................
    

});

