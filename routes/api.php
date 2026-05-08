<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [RegisteredUserController::class, 'storeApi'])->name('registerApi');
Route::post('/login',    [AuthenticatedSessionController::class, 'storeApi'])->name('loginApi');

Route::get('/invitations/{token}/accept', [InvitationController::class, 'accept'])->name('api.invitations.accept');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroyApi'])->name('destroyApi.logout');

    Route::middleware('tenant')->group(function () {
        Route::get('/me', fn(Request $request) => $request->user())->name('me');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tenants', [TenantController::class, 'index'])->name('api.tenants.index');
    Route::post('/tenants/switch', [TenantController::class, 'switch'])->name('api.tenants.switch');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/subscription/checkout', [SubscriptionController::class, 'checkout']);
    Route::get('/subscription/success',   [SubscriptionController::class, 'success']);
    Route::get('/subscription/cancel',    [SubscriptionController::class, 'cancel']);
});
Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    Route::get('/pricing',        [PricingController::class, 'index'])->name('api.pricing.index');
    Route::get('/pricing/{plan}', [PricingController::class, 'show'])->name('api.pricing.show');

    Route::post('/invitations',                       [InvitationController::class, 'invite']);
    Route::delete('/invitations/{invitation}/revoke', [InvitationController::class, 'revoke']);

    Route::get('/members',           [MemberController::class, 'index']);
    Route::patch('/members/{user}',  [MemberController::class, 'update']);
    Route::delete('/members/{user}', [MemberController::class, 'destroy']);
});

Route::post('/webhook/stripe', [WebhookController::class, 'handleWebhook'])->name('webhook.stripe');
