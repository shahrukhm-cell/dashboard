<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\ExpenseController;
use App\Http\Controllers\Backend\PaymentController;
use App\Http\Controllers\Backend\PlanController;
use App\Http\Controllers\Backend\ReportController;
use App\Http\Controllers\Backend\CustomerController;
use App\Http\Controllers\Backend\TeamController;
use App\Http\Controllers\Backend\TenantController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\ServiceController;
use App\Http\Controllers\Backend\JobWorkController;
use App\Http\Controllers\Backend\JobInvoiceController;
use App\Http\Controllers\Backend\InvoiceSettingController;
use App\Http\Controllers\Backend\ServiceJobController;
use App\Http\Controllers\Backend\TenantUserController;
use App\Http\Controllers\Backend\TimeEntryController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('auth.login');
})->name('home');

Route::get('/dashboard', DashboardController::class)
    ->middleware('auth')
    ->name('dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('reports', ReportController::class)->name('reports.index');
    Route::patch('customers/{customer}/restore', [CustomerController::class, 'restore'])->name('customers.restore')->withTrashed();
    Route::resource('customers', CustomerController::class)->withTrashed(['show', 'edit', 'update']);
    Route::resource('services', ServiceController::class)->only(['index', 'store', 'edit', 'update']);
    Route::patch('jobs/{job}/restore', [ServiceJobController::class, 'restore'])->name('jobs.restore')->withTrashed();
    Route::resource('jobs', ServiceJobController::class)->withTrashed(['show', 'edit', 'update']);
    Route::post('jobs/{job}/status', [ServiceJobController::class, 'updateStatus'])->name('jobs.status.update');
    Route::post('jobs/{job}/quote', [ServiceJobController::class, 'updateQuote'])->name('jobs.quote.update');
    Route::get('jobs/{job}/quote', [JobInvoiceController::class, 'quote'])->name('jobs.quote.download');
    Route::get('jobs/{job}/current-invoice', [JobInvoiceController::class, 'current'])->name('jobs.current-invoice');
    Route::post('jobs/{job}/photos', [ServiceJobController::class, 'storePhoto'])->name('jobs.photos.store');
    Route::get('jobs/{job}/invoice', JobInvoiceController::class)->name('jobs.invoice');
    Route::get('invoice-settings', [InvoiceSettingController::class, 'edit'])->name('invoice-settings.edit');
    Route::patch('invoice-settings', [InvoiceSettingController::class, 'update'])->name('invoice-settings.update');
    Route::post('jobs/{job}/work/start', [JobWorkController::class, 'start'])->name('jobs.work.start');
    Route::post('jobs/{job}/work/break/start', [JobWorkController::class, 'startBreak'])->name('jobs.work.break.start');
    Route::post('jobs/{job}/work/break/end', [JobWorkController::class, 'endBreak'])->name('jobs.work.break.end');
    Route::post('jobs/{job}/work/end', [JobWorkController::class, 'end'])->name('jobs.work.end');
    Route::patch('teams/{team}/restore', [TeamController::class, 'restore'])->name('teams.restore')->withTrashed();
    Route::resource('teams', TeamController::class)->only(['index', 'store', 'show', 'update', 'destroy'])->withTrashed(['show', 'update']);
    Route::get('teams/{team}/members/{user}', [TeamController::class, 'member'])->name('teams.members.show');
    Route::post('teams/{team}/members/{user}/attendance', [TeamController::class, 'storeAttendance'])->name('teams.members.attendance.store');
    Route::resource('time-entries', TimeEntryController::class)->only(['index', 'store']);
    Route::resource('expenses', ExpenseController::class)->only(['index', 'store', 'update']);
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('customer-payments', [PaymentController::class, 'storeCustomerPayment'])->name('customer-payments.store');
    Route::patch('customer-payments/{customerPayment}', [PaymentController::class, 'updateCustomerPayment'])->name('customer-payments.update');
    Route::post('team-payments', [PaymentController::class, 'storeTeamPayment'])->name('team-payments.store');
    Route::patch('team-payments/{teamPayment}', [PaymentController::class, 'updateTeamPayment'])->name('team-payments.update');
    Route::post('expense-categories', [ExpenseController::class, 'storeCategory'])->name('expense-categories.store');
    Route::post('service-categories', [ServiceController::class, 'storeCategory'])->name('service-categories.store');
    Route::get('/admin/plans', [PlanController::class, 'index'])->name('admin.plans.index');
    Route::post('/admin/plans', [PlanController::class, 'store'])->name('admin.plans.store');
    Route::patch('/admin/plans/{plan}', [PlanController::class, 'update'])->name('admin.plans.update');
    Route::post('/admin/tenants/{tenant}/subscription', [PlanController::class, 'subscribe'])->name('admin.tenants.subscription');
    Route::get('/admin/tenants', [TenantController::class, 'index'])->name('admin.tenants.index');
    Route::post('/admin/tenants', [TenantController::class, 'store'])->name('admin.tenants.store');
    Route::patch('/admin/tenants/{tenant}', [TenantController::class, 'update'])->name('admin.tenants.update');
    Route::get('/tenants/switch', fn () => redirect()->route('dashboard'));
    Route::post('/tenants/switch', [TenantController::class, 'switch'])->name('tenant.switch');
    Route::get('/tenants/{tenant}/users', [TenantUserController::class, 'index'])->name('tenant.users.index');
    Route::post('/tenants/{tenant}/users', [TenantUserController::class, 'store'])->name('tenant.users.store');
    Route::patch('/tenants/{tenant}/users/{user}', [TenantUserController::class, 'update'])->name('tenant.users.update');
    Route::post('/tenants/{tenant}/team-leaves', [TenantUserController::class, 'storeLeave'])->name('tenant.team-leaves.store');
    Route::get('/tenants/{tenant}/roles', [RoleController::class, 'index'])->name('tenant.roles.index');
    Route::post('/tenants/{tenant}/roles', [RoleController::class, 'store'])->name('tenant.roles.store');
    Route::patch('/tenants/{tenant}/roles/{role}', [RoleController::class, 'update'])->name('tenant.roles.update');
    Route::delete('/tenants/{tenant}/roles/{role}', [RoleController::class, 'destroy'])->name('tenant.roles.destroy');
    Route::post('/tenants/{tenant}/settings/theme', [TenantController::class, 'updateTheme'])->name('tenant.theme.update');
});










