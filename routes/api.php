<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/login', function (Request $request) {
    return "login";
})->name('login');

Route::middleware(['tenant'])->group(function () {

    Route::get('/teste', function (Request $request) {
        return "ok";
    });
});
