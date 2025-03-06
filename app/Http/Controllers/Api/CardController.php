<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\CardModel;
use Illuminate\Http\Request;
use App\Models\AccountModel;

class CardController extends Controller
{
   
    
   
  

    
    public function createCard(Request $request)
    {
        // Validation des données entrantes
        $request->validate([
            'client_account_id' => 'required|exists:client_account,id',
            'cvv' => 'required|string|min:3|max:4', // Le CVV est fourni par l'utilisateur
        ]);
    
        $clientAccountId = $request->client_account_id;
    
        // Vérifier si le compte a déjà une carte active
        $existingCard = CardModel::where('client_account_id', $clientAccountId)->where('is_active', true)->first();
    
        if ($existingCard) {
            // Désactiver l'ancienne carte
            $existingCard->update(['is_active' => false]);
        }
    
        // Génération d'un numéro de carte unique
        do {
            $cardNumber = $this->generateCardNumber();
        } while (CardModel::where('card_number', $cardNumber)->exists());
    
        // Définition des dates
        $dateValidity = now();
        $dateExpiration = now()->addYears(3);
    
        // Création de la nouvelle carte
        $newCard = CardModel::create([
            'client_account_id' => $clientAccountId,
            'card_number' => $cardNumber,
            'cvv' => $request->cvv,
            'date_validity' => $dateValidity,
            'date_expiration' => $dateExpiration,
            'is_active' => true, 
        ]);
    
        return response()->json([
            'message' => 'Carte bancaire créée avec succès',
            'card' => $newCard
        ], 201);
    }

    private function generateCardNumber()
{
    $prefix = '5290'; // Préfixe fixe
    $suffix = rand(1000000000, 9999999999); // Génération aléatoire de 10 chiffres
    return $prefix . $suffix;
}
    
}
