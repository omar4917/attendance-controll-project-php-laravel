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
use App\Http\Controllers\ModeratorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use Illuminate\Support\Facades\Route;

// Auth routes (public)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::redirect('/', '/attendance');

// Protected routes - require Django admin authentication
Route::middleware(['web', 'django.auth'])->group(function () {
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{id}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/{id}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    
    // Attendance
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::put('/attendance/{id}', [AttendanceController::class, 'update'])->name('attendance.update');
    Route::delete('/attendance/{id}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');
    Route::post('/attendance/export', [AttendanceController::class, 'export'])->name('attendance.export');
    Route::post('/attendance/import', [AttendanceController::class, 'import'])->name('attendance.import');
    
    // Attendance Records (Manual CRUD)
    Route::get('/attendance-records', [AttendanceRecordController::class, 'index'])->name('attendance-records.index');
    Route::get('/attendance-records/create', [AttendanceRecordController::class, 'create'])->name('attendance-records.create');
    Route::post('/attendance-records', [AttendanceRecordController::class, 'store'])->name('attendance-records.store');
    Route::get('/attendance-records/{id}/edit', [AttendanceRecordController::class, 'edit'])->name('attendance-records.edit');
    Route::put('/attendance-records/{id}', [AttendanceRecordController::class, 'update'])->name('attendance-records.update');
    Route::delete('/attendance-records/{id}', [AttendanceRecordController::class, 'destroy'])->name('attendance-records.destroy');
    Route::post('/attendance-records/bulk-action', [AttendanceRecordController::class, 'bulkAction'])->name('attendance-records.bulk_action');
    
    // Settings
    Route::get('/settings/voice-message', [SettingsController::class, 'voiceMessage'])->name('settings.voice_message');
    Route::post('/settings/voice-message', [SettingsController::class, 'saveVoiceMessage'])->name('settings.voice_message.save');
    Route::redirect('/settings/voice', '/settings/voice-message');
    Route::redirect('/settings/message', '/settings/voice-message');
    Route::post('/settings/api-server', [SettingsController::class, 'saveApiServer'])->name('settings.api-server');
    Route::get('/settings/company', [SettingsController::class, 'company'])->name('settings.company');
    Route::post('/settings/company', [SettingsController::class, 'saveCompany'])->name('settings.company.save');
    Route::get('/settings/context', [SettingsController::class, 'context'])->name('settings.context');
    Route::post('/settings/context', [SettingsController::class, 'saveContext'])->name('settings.context.save');
    Route::get('/settings/integration', [SettingsController::class, 'integration'])->name('settings.integration');
    Route::post('/settings/integration', [SettingsController::class, 'saveIntegration'])->name('settings.integration.save');
    
    // Holidays
    Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');
    Route::get('/holidays/{id}/edit', [HolidayController::class, 'edit'])->name('holidays.edit');
    Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store');
    Route::put('/holidays/{id}', [HolidayController::class, 'update'])->name('holidays.update');
    Route::delete('/holidays/{id}', [HolidayController::class, 'destroy'])->name('holidays.destroy');
    Route::post('/holidays/generate', [HolidayController::class, 'generate'])->name('holidays.generate');
    
    // Salary
    Route::get('/salary', [SalaryController::class, 'index'])->name('salary.index');
    Route::get('/salary/{id}/edit', [SalaryController::class, 'edit'])->name('salary.edit');
    Route::post('/salary', [SalaryController::class, 'store'])->name('salary.store');
    Route::put('/salary/{id}', [SalaryController::class, 'update'])->name('salary.update');
    Route::delete('/salary/{id}', [SalaryController::class, 'destroy'])->name('salary.destroy');
    Route::get('/salary/defaults', [SalaryController::class, 'defaults'])->name('salary.defaults');
    Route::post('/salary/defaults', [SalaryController::class, 'saveDefaults'])->name('salary.defaults.save');
    
    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/salary', [ReportController::class, 'salaryReport'])->name('reports.salary');

    // Live Feed
    Route::get('/livefeed', [LiveFeedController::class, 'index'])->name('livefeed.index');
    Route::post('/livefeed/action', [LiveFeedController::class, 'action'])->name('livefeed.action');
    Route::delete('/livefeed/{id}', [LiveFeedController::class, 'destroy'])->name('livefeed.destroy');

    // Shifts
    Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
    Route::post('/shifts', [ShiftController::class, 'store'])->name('shifts.store');
    Route::put('/shifts/{id}', [ShiftController::class, 'update'])->name('shifts.update');
    Route::delete('/shifts/{id}', [ShiftController::class, 'destroy'])->name('shifts.destroy');

    // Moderator Labels
    Route::get('/moderator', [ModeratorController::class, 'index'])->name('moderator.index');
    Route::post('/moderator', [ModeratorController::class, 'store'])->name('moderator.store');

    // Companies (Organizations) - Super Admin
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/create', [CompanyController::class, 'create'])->name('companies.create');
    Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
    Route::get('/companies/{id}', [CompanyController::class, 'show'])->name('companies.show');
    Route::get('/companies/{id}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
    Route::put('/companies/{id}', [CompanyController::class, 'update'])->name('companies.update');
    Route::delete('/companies/{id}', [CompanyController::class, 'destroy'])->name('companies.destroy');
    Route::get('/companies/{id}/devices', [CompanyController::class, 'devices'])->name('companies.devices');
    Route::post('/companies/{id}/devices', [CompanyController::class, 'storeDevice'])->name('companies.devices.store');
    Route::put('/companies/{orgId}/devices/{deviceId}', [CompanyController::class, 'updateDevice'])->name('companies.devices.update');
    Route::delete('/companies/{orgId}/devices/{deviceId}', [CompanyController::class, 'destroyDevice'])->name('companies.devices.destroy');
});
