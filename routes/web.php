<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes
|--------------------------------------------------------------------------
| - /login, POST /login, POST /logout: công khai (chưa cần đăng nhập)
| - Toàn bộ route còn lại: bọc trong middleware 'auth' — chưa đăng nhập
|   sẽ tự bị đá về /login
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect('/login'));

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [PageController::class, 'dashboard']);

    Route::prefix('attendance')->group(function () {
        Route::get('/history', [PageController::class, 'attendanceHistory']);
        Route::post('/check-in', [PageController::class, 'checkIn'])->name('attendance.check-in');
        Route::post('/check-out', [PageController::class, 'checkOut'])->name('attendance.check-out');
    });

    Route::get('/leave', [PageController::class, 'leaveIndex'])->name('leave.index');
    Route::post('/leave', [PageController::class, 'storeLeaveRequest'])->name('leave.store');
    Route::get('/payroll', [PageController::class, 'payrollIndex'])->name('payroll.index');

    // Hồ sơ cá nhân — nhân viên tự xem/sửa thông tin của chính mình
    Route::get('/profile', [PageController::class, 'profile'])->name('profile');
    Route::put('/profile', [PageController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [PageController::class, 'updateProfilePassword'])->name('profile.password');

    Route::prefix('admin')->group(function () {
        // Quản lý nhân viên (HR)
        Route::get('/employees', [PageController::class, 'adminEmployees'])->name('admin.employees');
        Route::post('/employees', [PageController::class, 'storeEmployee'])->name('admin.employees.store');
        Route::put('/employees/{employee}', [PageController::class, 'updateEmployee'])->name('admin.employees.update');
        Route::post('/employees/{employee}/toggle-status', [PageController::class, 'toggleEmployeeStatus'])->name('admin.employees.toggle-status');

        Route::get('/departments', [PageController::class, 'adminDepartments'])->name('admin.departments');
        Route::post('/departments', [PageController::class, 'storeDepartment'])->name('admin.departments.store');
        Route::put('/departments/{department}', [PageController::class, 'updateDepartment'])->name('admin.departments.update');
        Route::post('/departments/{department}/toggle-status', [PageController::class, 'toggleDepartmentStatus'])->name('admin.departments.toggle-status');

        Route::get('/accounts', [PageController::class, 'adminAccounts'])->name('admin.accounts');
        Route::post('/accounts', [PageController::class, 'storeAccount'])->name('admin.accounts.store');
        Route::post('/accounts/{user}/toggle-status', [PageController::class, 'toggleAccountStatus'])->name('admin.accounts.toggle-status');
        Route::delete('/accounts/{user}', [PageController::class, 'deleteAccount'])->name('admin.accounts.delete');

        Route::get('/attendance', [PageController::class, 'adminAttendance'])->name('admin.attendance');
        Route::get('/payrolls', [PageController::class, 'adminPayrolls'])->name('admin.payrolls');
        Route::post('/payrolls', [PageController::class, 'storePayroll'])->name('admin.payrolls.store');

        Route::get('/leave-approvals', [PageController::class, 'adminLeaveApprovals'])->name('admin.leave-approvals');
        Route::post('/leave-approvals/{leaveRequest}/{decision}', [PageController::class, 'updateLeaveApproval'])
            ->where('decision', 'approve|reject')
            ->name('admin.leave-approvals.update');
        Route::get('/reports', [PageController::class, 'adminReports'])->name('admin.reports');
    });
});