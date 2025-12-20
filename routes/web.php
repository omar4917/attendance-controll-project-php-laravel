<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceRecordController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LiveFeedController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\OrgUsersController;
use Illuminate\Support\Facades\Route;

// Auth routes (public)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/switch-organization', [AuthController::class, 'switchOrganization'])->name('switch.organization');

Route::redirect('/', '/attendance');

// Protected routes - require Django admin authentication
Route::middleware(['web', 'django.auth'])->group(function () {
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::middleware('role:super_admin,org_main_admin,org_admin,org_viewer')->group(function () {
        Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/employees/{id}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/employees/{id}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    });
    
    // Attendance
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::middleware('role:super_admin,org_main_admin,org_admin,org_viewer')->group(function () {
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::put('/attendance/{id}', [AttendanceController::class, 'update'])->name('attendance.update');
        Route::delete('/attendance/{id}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');
    });
    Route::post('/attendance/export', [AttendanceController::class, 'export'])->name('attendance.export');
    Route::post('/attendance/import', [AttendanceController::class, 'import'])->name('attendance.import');
    Route::get('/attendance/pdf', [AttendanceController::class, 'downloadPdf'])->name('attendance.pdf');
    
    // Attendance Records (Manual CRUD)
    Route::get('/attendance-records', [AttendanceRecordController::class, 'index'])->name('attendance-records.index');
    Route::middleware('role:super_admin,org_main_admin,org_admin,org_viewer')->group(function () {
        Route::get('/attendance-records/create', [AttendanceRecordController::class, 'create'])->name('attendance-records.create');
        Route::post('/attendance-records', [AttendanceRecordController::class, 'store'])->name('attendance-records.store');
        Route::get('/attendance-records/{id}/edit', [AttendanceRecordController::class, 'edit'])->name('attendance-records.edit');
        Route::put('/attendance-records/{id}', [AttendanceRecordController::class, 'update'])->name('attendance-records.update');
        Route::delete('/attendance-records/{id}', [AttendanceRecordController::class, 'destroy'])->name('attendance-records.destroy');
        Route::post('/attendance-records/bulk-action', [AttendanceRecordController::class, 'bulkAction'])->name('attendance-records.bulk_action');
    });
    
    Route::middleware('role:super_admin,org_main_admin,org_admin,org_viewer')->group(function () {
        Route::post('/settings/voice-message', [SettingsController::class, 'saveVoiceMessage'])->name('settings.voice_message.save');
        Route::post('/settings/api-server', [SettingsController::class, 'saveApiServer'])->name('settings.api-server');
        Route::post('/settings/company', [SettingsController::class, 'saveCompany'])->name('settings.company.save');
        Route::post('/settings/context', [SettingsController::class, 'saveContext'])->name('settings.context.save');
        Route::post('/settings/integration', [SettingsController::class, 'saveIntegration'])->name('settings.integration.save');
    });
    Route::get('/settings/voice-message', [SettingsController::class, 'voiceMessage'])->name('settings.voice_message');
    Route::redirect('/settings/voice', '/settings/voice-message');
    Route::redirect('/settings/message', '/settings/voice-message');
    Route::get('/settings/company', [SettingsController::class, 'company'])->name('settings.company');
    Route::get('/settings/context', [SettingsController::class, 'context'])->name('settings.context');
    Route::get('/settings/integration', [SettingsController::class, 'integration'])->name('settings.integration');
    
    // Holidays
    Route::middleware('role:super_admin,org_main_admin,org_admin,org_viewer')->group(function () {
        Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');
        Route::get('/holidays/{id}/edit', [HolidayController::class, 'edit'])->name('holidays.edit');
        Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store');
        Route::put('/holidays/{id}', [HolidayController::class, 'update'])->name('holidays.update');
        Route::delete('/holidays/{id}', [HolidayController::class, 'destroy'])->name('holidays.destroy');
        Route::post('/holidays/generate', [HolidayController::class, 'generate'])->name('holidays.generate');
    });
    
    // Salary
    Route::middleware('role:super_admin,org_main_admin,org_admin,org_viewer')->group(function () {
        Route::get('/salary', [SalaryController::class, 'index'])->name('salary.index');
        Route::get('/salary/{id}/edit', [SalaryController::class, 'edit'])->name('salary.edit');
        Route::post('/salary', [SalaryController::class, 'store'])->name('salary.store');
        Route::put('/salary/{id}', [SalaryController::class, 'update'])->name('salary.update');
        Route::delete('/salary/{id}', [SalaryController::class, 'destroy'])->name('salary.destroy');
        Route::get('/salary/defaults', [SalaryController::class, 'defaults'])->name('salary.defaults');
        Route::post('/salary/defaults', [SalaryController::class, 'saveDefaults'])->name('salary.defaults.save');
    });
    
    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/salary', [ReportController::class, 'salaryReport'])->name('reports.salary');

    // Live Feed
    Route::get('/livefeed', [LiveFeedController::class, 'index'])->name('livefeed.index');
    Route::post('/livefeed/action', [LiveFeedController::class, 'action'])->name('livefeed.action');
    Route::delete('/livefeed/{id}', [LiveFeedController::class, 'destroy'])->name('livefeed.destroy');

    // Shifts
    Route::middleware('role:super_admin,org_main_admin,org_admin,org_viewer')->group(function () {
        Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
        Route::post('/shifts', [ShiftController::class, 'store'])->name('shifts.store');
        Route::put('/shifts/{id}', [ShiftController::class, 'update'])->name('shifts.update');
        Route::delete('/shifts/{id}', [ShiftController::class, 'destroy'])->name('shifts.destroy');
    });


    // Organizations - Super Admin
    Route::get('/organizations', [OrganizationController::class, 'index'])->name('organizations.index');
    Route::get('/organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
    Route::post('/organizations', [OrganizationController::class, 'store'])->name('organizations.store');
    Route::get('/organizations/{id}', [OrganizationController::class, 'show'])->name('organizations.show');
    Route::get('/organizations/{id}/edit', [OrganizationController::class, 'edit'])->name('organizations.edit');
    Route::put('/organizations/{id}', [OrganizationController::class, 'update'])->name('organizations.update');
    Route::delete('/organizations/{id}', [OrganizationController::class, 'destroy'])->name('organizations.destroy');
    Route::get('/organizations/{id}/devices', [OrganizationController::class, 'devices'])->name('organizations.devices');
    Route::post('/organizations/{id}/devices', [OrganizationController::class, 'storeDevice'])->name('organizations.devices.store');
    Route::put('/organizations/{orgId}/devices/{deviceId}', [OrganizationController::class, 'updateDevice'])->name('organizations.devices.update');
    Route::delete('/organizations/{orgId}/devices/{deviceId}', [OrganizationController::class, 'destroyDevice'])->name('organizations.devices.destroy');
    
    // Audit Logs
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
    
    // Organization Users Management
    Route::get('/org-users', [OrgUsersController::class, 'index'])->name('org-users.index');
    Route::post('/org-users', [OrgUsersController::class, 'store'])->name('org-users.store');
    Route::put('/org-users/{id}', [OrgUsersController::class, 'update'])->name('org-users.update');
    Route::delete('/org-users/{id}', [OrgUsersController::class, 'destroy'])->name('org-users.destroy');
    
    // Export / Import
    Route::get('/export', [ExportController::class, 'index'])->name('export.index');
    Route::get('/export/download', [ExportController::class, 'export'])->name('export.download');
    Route::post('/export/import', [ExportController::class, 'import'])->name('export.import');
    
    // Analytics Dashboard
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
});
