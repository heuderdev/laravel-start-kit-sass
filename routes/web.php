<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'welcome'])->name('welcome');

Route::get('/invitations/{token}/accept', [InvitationController::class, 'accept'])->name('invitations.accept');

// Callbacks do Stripe — só auth, sem tenant (Stripe não envia X-Tenant-ID)
Route::middleware('auth')->group(function () {
    Route::get('/subscription/success',   [SubscriptionController::class, 'success'])->name('subscription.success');
    Route::get('/subscription/cancelled', fn() => view('subscription.cancel'))->name('subscription.cancel.view');
});

// Rotas autenticadas com contexto de tenant
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/tenants',           [TenantController::class, 'index'])->name('tenants.index');
    Route::post('/tenants/switch',   [TenantController::class, 'switch'])->name('tenants.switch');

    Route::post('/subscription/checkout', [SubscriptionController::class, 'checkout'])->name('subscription.checkout');
    Route::get('/subscription/cancel',    [SubscriptionController::class, 'cancel'])->name('subscription.cancel');

    Route::get('/pricing',        [PricingController::class, 'index'])->name('pricing.index');
    Route::get('/pricing/{plan}', [PricingController::class, 'show'])->name('pricing.show');

    Route::post('/invitations',                        [InvitationController::class, 'invite'])->name('invitations.invite');
    Route::delete('/invitations/{invitation}/revoke',  [InvitationController::class, 'revoke'])->name('invitations.revoke');

    Route::get('/members',              [MemberController::class, 'index'])->name('members.index');
    Route::patch('/members/{user}',     [MemberController::class, 'update'])->name('members.update');
    Route::delete('/members/{user}',    [MemberController::class, 'destroy'])->name('members.destroy');
});

require __DIR__ . '/auth.php';
