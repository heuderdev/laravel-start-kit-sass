<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [RegisteredUserController::class, 'storeApi'])->name('registerApi');
Route::post('/login',    [AuthenticatedSessionController::class, 'storeApi'])->name('loginApi');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroyApi'])->name('destroyApi.logout');

    Route::middleware('tenant')->group(function () {
        Route::get('/me', fn(Request $request) => $request->user())->name('me');
    });
});


Route::post('/webhook/stripe', [WebhookController::class, 'handleWebhook'])->name('webhook.stripe');
