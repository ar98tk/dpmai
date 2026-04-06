<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\Admin\BusinessController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InstanceController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\AdminAuthController;
use App\Models\Plan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/', function () {
    $plans = new Collection();

    if (Schema::hasTable('plans')) {
        $plans = Plan::query()
            ->select(['id', 'name', 'price', 'max_instances', 'daily_token_limit', 'monthly_token_limit', 'features'])
            ->orderBy('price')
            ->get();
    }

    return view('welcome', [
        'plans' => $plans,
    ]);
});

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware('auth')->group(function (): void {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::middleware('admin')->group(function (): void {
            Route::get('/home', [DashboardController::class, 'index'])->name('home');
            Route::resource('admins', AdminController::class)->except(['show']);
        });
    });
});

Route::middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/businesses', [BusinessController::class, 'index'])->name('admin.businesses.index');
    Route::get('/businesses/create', [BusinessController::class, 'create'])->name('admin.businesses.create');
    Route::get('/businesses/{business}', [BusinessController::class, 'show'])->name('admin.businesses.show');
    Route::post('/businesses', [BusinessController::class, 'store'])->name('admin.businesses.store');
    Route::put('/businesses/{business}', [BusinessController::class, 'update'])->name('admin.businesses.update');
    Route::get('/plans', [PlanController::class, 'index'])->name('admin.plans.index');
    Route::post('/plans', [PlanController::class, 'store'])->name('admin.plans.store');
    Route::put('/plans/{plan}', [PlanController::class, 'update'])->name('admin.plans.update');
    Route::delete('/plans/{plan}', [PlanController::class, 'destroy'])->name('admin.plans.destroy');
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('admin.subscriptions.index');
    Route::get('/subscriptions/{subscription}/invoice', [SubscriptionController::class, 'invoice'])->name('admin.subscriptions.invoice');
    Route::get('/billing', [BillingController::class, 'index'])->name('admin.billing.index');
    Route::get('/settings', [SettingsController::class, 'index'])->name('admin.settings.index');
    Route::post('/settings', [SettingsController::class, 'store'])->name('admin.settings.store');

    Route::post('/instances/store', [InstanceController::class, 'store'])->name('admin.instances.store');
    Route::get('/instances/{instance}/edit', [InstanceController::class, 'edit'])->name('admin.instances.edit');
    Route::get('/instances/{instance}/leads', [InstanceController::class, 'leads'])->name('admin.instances.leads');
    Route::get('/instances/{instance}/leads/{lead}/chat', [InstanceController::class, 'chat'])->name('admin.instances.leads.chat');
    Route::get('/instances/{instance}/qr', [InstanceController::class, 'qr'])->name('admin.instances.qr');
    Route::get('/instances/{instance}/status', [InstanceController::class, 'status'])->name('admin.instances.status');
    Route::put('/instances/{instance}', [InstanceController::class, 'update'])->name('admin.instances.update');
    Route::delete('/instances/{instance}', [InstanceController::class, 'destroy'])->name('admin.instances.destroy');
    Route::delete('/instances/{instance}/leads/{lead}', [InstanceController::class, 'destroyLead'])->name('admin.instances.leads.destroy');
});

