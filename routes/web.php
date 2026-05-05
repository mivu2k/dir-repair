<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Administrative Gate
    Route::middleware('can:manage users')->group(function () {
        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::patch('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
        
        Route::get('/admin/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('admin.settings');
        Route::post('/admin/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('admin.settings.update');
        
        Route::post('/admin/settings/symptoms', [\App\Http\Controllers\Admin\SettingController::class, 'storeSymptom'])->name('admin.settings.symptoms.store');
        Route::delete('/admin/settings/symptoms/{symptom}', [\App\Http\Controllers\Admin\SettingController::class, 'destroySymptom'])->name('admin.settings.symptoms.destroy');
        
        Route::post('/admin/settings/accessories', [\App\Http\Controllers\Admin\SettingController::class, 'storeAccessory'])->name('admin.settings.accessories.store');
        Route::delete('/admin/settings/accessories/{accessory}', [\App\Http\Controllers\Admin\SettingController::class, 'destroyAccessory'])->name('admin.settings.accessories.destroy');
    });

    // Operational Core (Protected by RoleMiddleware for destructive actions)
    Route::middleware('role')->group(function () {
        // Customers
        Route::get('/customers', [\App\Http\Controllers\CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/create', [\App\Http\Controllers\CustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers', [\App\Http\Controllers\CustomerController::class, 'store'])->name('customers.store');
        Route::get('/customers/{customer}', [\App\Http\Controllers\CustomerController::class, 'show'])->name('customers.show');
        Route::get('/customers/{customer}/edit', [\App\Http\Controllers\CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{customer}', [\App\Http\Controllers\CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [\App\Http\Controllers\CustomerController::class, 'destroy'])->middleware('role:admin')->name('customers.destroy');

        // Intakes
        Route::get('/intakes', [\App\Http\Controllers\IntakeController::class, 'index'])->name('intakes.index');
        Route::get('/intakes/create', [\App\Http\Controllers\IntakeController::class, 'create'])->name('intakes.create');
        Route::post('/intakes', [\App\Http\Controllers\IntakeController::class, 'store'])->name('intakes.store');
        Route::get('/intakes/{intake}', [\App\Http\Controllers\IntakeController::class, 'show'])->name('intakes.show');
        Route::patch('/intakes/{intake}', [\App\Http\Controllers\IntakeController::class, 'update'])->name('intakes.update');
        Route::delete('/intakes/{intake}', [\App\Http\Controllers\IntakeController::class, 'destroy'])->middleware('role:admin')->name('intakes.destroy');
        Route::post('/intakes/{intake}/status', [\App\Http\Controllers\IntakeController::class, 'updateStatus'])->name('intakes.status.update');

        // Jobs
        Route::get('/jobs', [\App\Http\Controllers\RepairJobController::class, 'index'])->name('jobs.index');
        Route::get('/jobs/{job_number}', [\App\Http\Controllers\RepairJobController::class, 'show'])->name('jobs.show');
        Route::get('/jobs/{job}/edit', [\App\Http\Controllers\RepairJobController::class, 'edit'])->name('jobs.edit');
        Route::put('/jobs/{job}', [\App\Http\Controllers\RepairJobController::class, 'update'])->name('jobs.update');
        Route::delete('/jobs/{job}', [\App\Http\Controllers\RepairJobController::class, 'destroy'])->middleware('role:admin')->name('jobs.destroy');
        Route::post('/jobs/{job}/assign', [\App\Http\Controllers\RepairJobController::class, 'assign'])->name('jobs.assign');
        Route::post('/jobs/{job}/status', [\App\Http\Controllers\RepairJobController::class, 'updateStatus'])->name('jobs.status');
        Route::post('/jobs/{job}/diagnose', [\App\Http\Controllers\DiagnosisController::class, 'store'])->name('jobs.diagnose');
        Route::patch('/diagnoses/{diagnosis}', [\App\Http\Controllers\DiagnosisController::class, 'update'])->name('diagnoses.update');
        Route::delete('/diagnoses/{diagnosis}', [\App\Http\Controllers\DiagnosisController::class, 'destroy'])->name('diagnoses.destroy');

        // Inventory
        Route::resource('parts', App\Http\Controllers\PartController::class);
        Route::delete('/parts/{part}', [\App\Http\Controllers\PartController::class, 'destroy'])->middleware('role:admin')->name('parts.destroy');
    });

    // Financial & Analytical (Manager/Admin Only)
    Route::middleware('role:manager')->group(function () {
        Route::get('/quotations', [\App\Http\Controllers\QuotationController::class, 'index'])->name('quotations.index');
        Route::get('/quotations/create', [\App\Http\Controllers\QuotationController::class, 'create'])->name('quotations.create');
        Route::post('/quotations', [\App\Http\Controllers\QuotationController::class, 'store'])->name('quotations.store');
        Route::get('/quotations/{quotation}', [\App\Http\Controllers\QuotationController::class, 'show'])->name('quotations.show');
        Route::delete('/quotations/{quotation}', [\App\Http\Controllers\QuotationController::class, 'destroy'])->middleware('role:admin')->name('quotations.destroy');
        Route::post('/quotations/{quotation}/status', [\App\Http\Controllers\QuotationController::class, 'updateStatus'])->name('quotations.status');
        
        Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [\App\Http\Controllers\ReportController::class, 'export'])->name('reports.export');
        
        Route::get('/sales-orders', [\App\Http\Controllers\SalesOrderController::class, 'index'])->name('sales-orders.index');
        Route::post('/sales-orders', [\App\Http\Controllers\SalesOrderController::class, 'store'])->name('sales-orders.store');
        Route::get('/sales-orders/{salesOrder}', [\App\Http\Controllers\SalesOrderController::class, 'show'])->name('sales-orders.show');
        Route::post('/sales-orders/{salesOrder}/payments', [\App\Http\Controllers\PaymentController::class, 'store'])->name('sales-orders.payments.store');
    });

    // PDFs (Inherit access from parent routes)
    Route::get('/quotations/{quotation}/pdf', [\App\Http\Controllers\PdfExportController::class, 'quotation'])->name('quotations.pdf');
    Route::get('/intakes/{intake}/pdf', [\App\Http\Controllers\PdfExportController::class, 'intakeSummary'])->name('intakes.pdf');
    Route::get('/intakes/{intake}/stickers', [\App\Http\Controllers\PdfExportController::class, 'intakeStickers'])->name('intakes.stickers');
    Route::get('/jobs/{job}/pdf/{variant}', [\App\Http\Controllers\PdfExportController::class, 'jobCard'])->name('jobs.pdf');
    Route::get('/jobs/{job}/pos/{type?}', [\App\Http\Controllers\PdfExportController::class, 'pos'])->name('jobs.pos');
    Route::get('/jobs/{job}/sticker', [\App\Http\Controllers\PdfExportController::class, 'jobSticker'])->name('jobs.sticker');
    Route::get('/sales-orders/{salesOrder}/pdf', [\App\Http\Controllers\PdfExportController::class, 'invoice'])->name('sales-orders.pdf');
});

require __DIR__.'/auth.php';
