<?php

use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BeneficiaryController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::middleware(['auth:admin'])->group(function () {
    Route::get('account/list/accounts', [AdminController::class, 'getClients']);
});
// Route pour la création d'un compte client
Route::post('account/create', [AccountController::class, 'createAccount']);

Route::post('account/login', [AuthController::class, 'login']);


Route::get('beneficiaries/list', [BeneficiaryController::class, 'index']); // Liste des bénéficiaires avec pagination et recherche
Route::get('beneficiaries/{id}', [BeneficiaryController::class, 'show']); // Détails d'un bénéficiaire
Route::post('bebeficiaries/create', [BeneficiaryController::class, 'store']); // Création d'un bénéficiaire
Route::put('beneficiaries/{id}', [BeneficiaryController::class, 'update']); // Mise à jour d'un bénéficiaire
Route::delete('/{id}', [BeneficiaryController::class, 'destroy']); 