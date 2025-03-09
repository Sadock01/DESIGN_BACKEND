<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountModel;
use App\Models\ClientModel;
use App\Models\CardModel;
use App\Models\UserFileModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;


class AccountController extends Controller
{
    /**
     * Création d'un compte client avec documents téléversés.
     */
    public function createAccount(Request $request)
{
    // Validation des données
    $validator = Validator::make($request->all(), [
        'firstname' => 'required|string|max:255',
        'lastname' => 'required|string|max:255',
        'email' => 'required|email|unique:clients',
        'phone' => 'required|string|max:20',
        'address' => 'required|string|max:255',
        'postal_code' => 'required|string|max:20',
        'job' => 'required|string|max:255',
        'salary' => 'required|string|max:50',
        'country' => 'required|string|max:100',
        'country_doc_select' => 'required|string|max:100',
        'password' => 'required|string|min:8',

        // Validation pour le compte
        'solde' => 'required|numeric|min:0',
        'iban' => 'required|string|unique:client_account',
        'bic' => 'required|string|max:20',

        // Validation des fichiers téléversés
        'documents' => 'required|array',
        'documents.*' => 'file|mimes:pdf,jpg,png,jpeg|max:2048',

        // Validation pour le CVV
        'cvv' => 'required|string|max:4',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status_code' => 422,
            'message' => 'Validation échouée.',
            'errors' => $validator->errors(),
        ], 422);
    }

    DB::beginTransaction(); // Commencer la transaction

    try {
       
        // Création du client
        $client = ClientModel::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'postal_code' => $request->postal_code,
            'job' => $request->job,
            'salary' => $request->salary,
            'country' => $request->country,
            'country_doc_select' => $request->country_doc_select,
            'password' => Hash::make($request->password),
        ]);
        // dd('la creation du compte commence '.$client);
        // Gestion des fichiers téléversés
        $uploadedFiles = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $filePath = $file->store('documents', 'public'); // Stockage dans "storage/app/public/documents"

                // Créer un enregistrement pour chaque fichier téléchargé
                $document = UserFileModel::create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $filePath,
                    'client_id' => $client->id, // Utilisation de l'ID du client
                ]);

                $uploadedFiles[] = $document;
            }
        }
        // dd('la creation du compte commence');
        // Création du compte bancaire avec l'ID du client
        $account = AccountModel::create([
            'client_id' => $client->id,
            'solde' => $request->solde,
            'iban' => $request->iban,
            'bic' => $request->bic,
            'account_activated' => false,
        ]);
        
      
        // Génération d'un numéro de carte aléatoire
        $cardNumber = $this->generateCardNumber();  // Méthode pour générer le numéro de carte

        // Calcul de la date d'expiration (3 ans après la création)
        $dateValidity = now();
        $dateExpiration = now()->addYears(3);

        // Création de la carte
        $card = CardModel::create([
            'date_validity' => $dateValidity,
            'date_expiration' => $dateExpiration,
            'card_number' => $cardNumber,
            'cvv' => $request->cvv,  // CVV entré par l'utilisateur
            'client_account_id' => $account->id,  // Liaison avec le compte client
        ]);

        // Valider la transaction
        DB::commit();

        return response()->json([
            'status_code' => 201,
            'message' => 'Compte créé avec succès.',
            'client' => $client,
            'account' => $account,
            'documents' => $uploadedFiles,
        ], 201);

    } catch (\Exception $e) {
        // Annuler en cas d'erreur
        DB::rollBack();

        return response()->json([
            'status_code' => 500,
            'message' => 'Erreur lors de la création du compte.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    private function generateCardNumber()
{
    $prefix = '5290';  
    $suffix = rand(1000000000, 9999999999);  // Génération aléatoire de 10 chiffres
    return $prefix . $suffix;
}


public function rechargeClientAccount(Request $request, $clientAccountId)
{
    // Validation du montant de recharge
    $validated = $request->validate([
        'montant' => 'required|numeric|min:1',  // Le montant doit être un nombre supérieur ou égal à 1
    ]);

    try {
        // Trouver le compte client par son ID
        $clientAccount = AccountModel::findOrFail($clientAccountId);

        // Vérification si le compte est activé
        if (!$clientAccount->account_activated) {
            return response()->json([
                'status_code' => 400,
                'message' => 'Le compte du client est désactivé. Recharge impossible.',
            ], 400);
        }

        // Ajouter le montant au solde actuel du client
        $clientAccount->solde = (float)$clientAccount->solde + $validated['montant'];

        // Sauvegarder les modifications dans la base de données
        $clientAccount->save();

        // Retourner une réponse confirmant le rechargement du solde
        return response()->json([
            'status_code' => 200,
            'message' => 'Le solde du compte client a été rechargé avec succès.',
            'nouveau_solde' => $clientAccount->solde,
        ]);
    } catch (Exception $e) {
        // Gestion des erreurs
        return response()->json([
            'status_code' => 500,
            'message' => 'Erreur lors du rechargement du solde du compte client.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

//Cette methode permet de bloquer le compte d'un client Button 2
public function blockAccount(Request $request, $clientAccountId)
{
    try {
        // Récupérer le compte client par son ID
        $clientAccount = AccountModel::findOrFail($clientAccountId);

        // Mettre à jour le statut de blocage du compte
        $clientAccount->is_locked = true;
        $clientAccount->save();

        return response()->json([
            'status_code' => 200,
            'message' => 'Le compte a été bloqué avec succès.'
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'message' => 'Échec de la tentative de blocage du compte.',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function unblockClientAccount(Request $request, $clientAccountId)
{
    try {
        // Trouver le compte client par son ID
        $clientAccount = AccountModel::findOrFail($clientAccountId);

        // Mettre à jour le statut du compte à 'activé'
        $clientAccount->account_activated = true;

        // Supprimer la raison de la désactivation
        $clientAccount->desactivation_raison = null;

        // Sauvegarder les modifications
        $clientAccount->save();

        // Retourner une réponse confirmant que le compte a été débloqué
        return response()->json([
            'status_code' => 200,
            'message' => 'Le compte client a été débloqué avec succès.',
        ]);
    } catch (Exception $e) {
        // Gestion des erreurs
        return response()->json([
            'status_code' => 500,
            'message' => 'Erreur lors du déblocage du compte client.',
            'error' => $e->getMessage(),
        ], 500);
    }
}



}
