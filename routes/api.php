<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\BeneficiaryController;     

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('account')->group(function () {
        // Route pour la création d'un compte client
        Route::post('/create', [AccountController::class, 'createAccount']);
    
        // Route pour recharger le solde du compte client
        Route::post('/recharge/{clientAccountId}', [AccountController::class, 'rechargeClientAccount']);
    
        // Route pour bloquer un compte client
        Route::post('/block/{clientAccountId}', [AccountController::class, 'blockAccount']);
    
        // Route pour débloquer un compte client
        Route::post('/unblock/{clientAccountId}', [AccountController::class, 'unblockClientAccount']);
    });
    // Route pour récupérer toutes les transactions d'un compte client
    Route::get('/transactions/{clientAccountId}', [TransactionController::class, 'index']);

    // Route pour créer une nouvelle transaction
    Route::post('/transactions', [TransactionController::class, 'createTransaction']);

    // Route pour récupérer toutes les transactions du client connecté
    Route::get('/transactions/client', [TransactionController::class, 'getTransactions']);

    // Route pour valider une transaction (change le statut de la transaction et met à jour le solde)
    Route::put('/transactions/{transactionId}/validate', [TransactionController::class, 'validateTransaction']);

    // Route pour mettre à jour le message d'une transaction
    Route::put('/transactions/{transactionId}/message', [TransactionController::class, 'updateTransactionMessage']);

    // Route pour afficher la liste des bénéficiaires avec pagination et recherche
    Route::get('/beneficiaries', [BeneficiaryController::class, 'index']);
    
    // Route pour afficher un bénéficiaire spécifique
    Route::get('/beneficiaries/{id}', [BeneficiaryController::class, 'show']);
    
    // Route pour créer un nouveau bénéficiaire
    Route::post('/beneficiaries', [BeneficiaryController::class, 'store']);
    
    // Route pour mettre à jour un bénéficiaire existant
    Route::put('/beneficiaries/{id}', [BeneficiaryController::class, 'update']);
    
    // Route pour supprimer un bénéficiaire
    Route::delete('/beneficiaries/{id}', [BeneficiaryController::class, 'destroy']);


Route::prefix('card')->group(function () {
    // Route pour la création d'une carte
    Route::post('/create', [CardController::class, 'createCard']);
});


    // Route pour la déconnexion (logout) de l'utilisateur
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Route pour la connexion (login) de l'utilisateur
Route::post('/login', [AuthController::class, 'login']);
