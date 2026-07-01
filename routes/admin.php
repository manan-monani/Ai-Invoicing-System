<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\BrandingController;
use App\Http\Controllers\Admin\Catalog\CategoryController;
use App\Http\Controllers\Admin\Catalog\ItemController;
use App\Http\Controllers\Admin\Catalog\TaxController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeDashboardController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\InvoicePaymentController;
use App\Http\Controllers\Admin\LookupController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TaxSettingsController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'storeLogin']);
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'storeRegister']);
});

Route::middleware(['auth', 'super-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('users/employees', [UserController::class, 'employees'])->name('users.employees.index');
    Route::get('users/customers', [UserController::class, 'customers'])->name('users.customers.index');
    Route::patch('users/{user}/status', [UserController::class, 'updateStatus'])->name('users.status');
    Route::resource('users', UserController::class);

    Route::get('/branding', [BrandingController::class, 'index'])->name('branding.index');
    Route::put('/branding/{brand}', [BrandingController::class, 'update'])->name('branding.update');

    Route::resource('roles', RoleController::class);

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity_logs.index');
    Route::get('/activity-logs/{activityLog}', [ActivityLogController::class, 'show'])->name('activity_logs.show');

    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/tax-settings', [TaxSettingsController::class, 'index'])->name('tax-settings.index');
        Route::put('/tax-settings', [TaxSettingsController::class, 'update'])->name('tax-settings.update');
        Route::resource('payment-methods', PaymentMethodController::class)->only(['index', 'store', 'update', 'destroy']);
    });

    Route::resource('invoices', InvoiceController::class);
    Route::post('invoices/{invoice}/payments', [InvoicePaymentController::class, 'store'])->name('invoices.payments.store');
    Route::post('invoices/{invoice}/email', [InvoiceController::class, 'sendEmail'])->name('invoices.email');

    Route::prefix('lookups')->name('lookups.')->group(function () {
        Route::get('customers', [LookupController::class, 'customers'])->name('customers');
        Route::get('items', [LookupController::class, 'items'])->name('items');
        Route::get('taxes', [LookupController::class, 'taxes'])->name('taxes');
        Route::get('categories', [LookupController::class, 'categories'])->name('categories');
        Route::get('roles', [LookupController::class, 'roles'])->name('roles');
    });

    Route::get('/profile', [App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');

    Route::get('/business/branding', [App\Http\Controllers\Admin\BusinessSettingController::class, 'index'])->name('business.branding');
    Route::post('/business/branding', [App\Http\Controllers\Admin\BusinessSettingController::class, 'update'])->name('business.update');

    Route::get('/business/settings', [App\Http\Controllers\Admin\BusinessLogicController::class, 'index'])->name('business.settings.index');
    Route::post('/business/settings', [App\Http\Controllers\Admin\BusinessLogicController::class, 'update'])->name('business.settings.update');

    Route::prefix('catalog')->name('catalog.')->group(function () {
        Route::resource('items', ItemController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('taxes', TaxController::class)->only(['index', 'store', 'update', 'destroy']);
    });

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read_all');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

Route::middleware(['auth', 'employee'])->prefix('admin/employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');

    Route::resource('invoices', InvoiceController::class);
    Route::post('invoices/{invoice}/payments', [InvoicePaymentController::class, 'store'])->name('invoices.payments.store');

    Route::prefix('lookups')->name('lookups.')->group(function () {
        Route::get('customers', [LookupController::class, 'customers'])->name('customers');
        Route::get('items', [LookupController::class, 'items'])->name('items');
        Route::get('taxes', [LookupController::class, 'taxes'])->name('taxes');
        Route::get('categories', [LookupController::class, 'categories'])->name('categories');
    });

    Route::prefix('catalog')->name('catalog.')->group(function () {
        Route::resource('items', ItemController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('taxes', TaxController::class)->only(['index', 'store', 'update', 'destroy']);
    });

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read_all');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});
