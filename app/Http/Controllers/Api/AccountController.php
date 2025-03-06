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
            'iban' => 'required|string|unique:accounts',
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

        try {
            // Démarrer la transaction
            DB::beginTransaction();

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

            // Création du compte bancaire
            $account = AccountModel::create([
                'client_id' => $client->id,
                'solde' => $request->solde,
                'iban' => $request->iban,
                'bic' => $request->bic,
                'account_activated' => false,
            ]);

            // Gestion des fichiers téléversés
            $uploadedFiles = [];
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $filePath = $file->store('documents', 'public'); // Stockage dans "storage/app/public/documents"

                    $document = UserFileModel::create([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $filePath,
                        'client_id' => $client->id,
                    ]);

                    $uploadedFiles[] = $document;
                }
            }

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
}
