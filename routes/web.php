<?php

use App\Http\Controllers\Admin\SuperAdminTenantController;
use App\Http\Controllers\Admin\SuperAdminUserController;
use App\Http\Controllers\CookiePreferenceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

// ─── Pública ─────────────────────────────────────────────────────────────────

Route::get('/', [DashboardController::class, 'welcome'])->name('welcome');

Route::get('/tenant/inactive', fn() => view('tenants.inactive'))->name('tenant.inactive');

Route::get('/invitations/{token}/accept', [InvitationController::class, 'accept'])
    ->name('invitations.accept');

// ─── Autenticado — sem tenant ─────────────────────────────────────────────────

Route::middleware('auth')->group(function () {

    // Seleção e troca de workspace (não pode ter middleware tenant)
    Route::get('/tenants',         [TenantController::class, 'index'])->name('tenants.index');
    Route::post('/tenants/switch', [TenantController::class, 'switch'])->name('tenants.switch');

    // Callbacks do Stripe (Stripe não envia X-Tenant-ID)
    Route::get('/subscription/success',   [SubscriptionController::class, 'success'])->name('subscription.success');
    Route::get('/subscription/cancelled', fn() => view('subscription.cancel'))->name('subscription.cancel.view');
});

// ─── Autenticado — com tenant ─────────────────────────────────────────────────

Route::middleware(['auth', 'tenant'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/pricing',        [PricingController::class, 'index'])->name('pricing.index');
    Route::get('/pricing/{plan}', [PricingController::class, 'show'])->name('pricing.show');

    Route::post('/subscription/checkout', [SubscriptionController::class, 'checkout'])->name('subscription.checkout');
    Route::get('/subscription/cancel',    [SubscriptionController::class, 'cancel'])->name('subscription.cancel');

    Route::post('/invitations',                       [InvitationController::class, 'invite'])->name('invitations.invite');
    Route::delete('/invitations/{invitation}/revoke', [InvitationController::class, 'revoke'])->name('invitations.revoke');

    Route::get('/members',           [MemberController::class, 'index'])->name('members.index');
    Route::patch('/members/{user}',  [MemberController::class, 'update'])->name('members.update');
    Route::delete('/members/{user}', [MemberController::class, 'destroy'])->name('members.destroy');
});

// ─── Autenticado — com tenant — plano pro ────────────────────────────────────

Route::middleware(['auth', 'tenant', 'plan:pro'])->group(function () {
    Route::get('/test', fn() => 'Acesso permitido para planos Pro!')->name('test.plan');
});

// ─── Auth (login, register, etc.) ────────────────────────────────────────────

require __DIR__ . '/auth.php';

// ─── Admin — super-admin global ───────────────────────────────────────────────

Route::middleware(['auth', 'super-admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/tenants',                          [SuperAdminTenantController::class, 'index'])->name('tenants.index');
        Route::get('/tenants/{tenant}/edit',            [SuperAdminTenantController::class, 'edit'])->name('tenants.edit');
        Route::match(['put', 'patch'], '/tenants/{tenant}/bypass', [SuperAdminTenantController::class, 'updateBypass'])->name('tenants.bypass.update');

        Route::get('/users',                                    [SuperAdminUserController::class, 'index'])->name('users.index');
        Route::post('/users/{user}/promote-super-admin',        [SuperAdminUserController::class, 'promote'])->name('users.promote-super-admin');
        Route::delete('/users/{user}/revoke-super-admin',       [SuperAdminUserController::class, 'revoke'])->name('users.revoke-super-admin');
    });

// ─── Admin — super-admin com tenant ──────────────────────────────────────────

Route::middleware(['auth', 'tenant', 'super-admin'])->group(function () {
    Route::get('/cookies',         [CookiePreferenceController::class, 'index'])->name('cookies.index');
    Route::post('/cookies',        [CookiePreferenceController::class, 'store'])->name('cookies.store');
    Route::patch('/cookies',       [CookiePreferenceController::class, 'update'])->name('cookies.update');
    Route::post('/cookies/renew',  [CookiePreferenceController::class, 'renew'])->name('cookies.renew');
    Route::delete('/cookies',      [CookiePreferenceController::class, 'destroy'])->name('cookies.destroy');
});
