<?php

use App\Http\Controllers\PricingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
    Route::post('/tenants/switch', [TenantController::class, 'switch'])->name('tenants.switch');
});

Route::middleware('auth')->group(function () {
    Route::post('/subscription/checkout', [SubscriptionController::class, 'checkout'])->name('subscription.checkout');
    Route::get('/subscription/success',   [SubscriptionController::class, 'success'])->name('subscription.success');
    Route::get('/subscription/cancel',    [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
});

Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/pricing',        [PricingController::class, 'index'])->name('pricing.index');
    Route::get('/pricing/{plan}', [PricingController::class, 'show'])->name('pricing.show');
});

require __DIR__ . '/auth.php';
