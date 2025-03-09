<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
// Route pour la création d'un compte client
Route::post('account/create', [AccountController::class, 'createAccount']);
Route::post('account/login', [AuthController::class, 'login']);