<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\ClientModel;
use App\Models\AdministratorModel;
use Exception;

class AuthController extends Controller
{
   
    public function login(Request $request)
    {
        // Validation des données de connexion
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        // Vérification des erreurs de validation
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation échouée.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Recherche de l'utilisateur client ou administrateur par email
            $user = ClientModel::where('email', $request->email)->first();

            // Si l'utilisateur n'est pas trouvé chez les clients, chercher dans les administrateurs
            if (!$user) {
                $user = AdministratorModel::where('email', $request->email)->first();
            }

            // Vérifier si l'utilisateur existe et si le mot de passe est correct
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status_code' => 401,
                    'message' => 'Le mot de passe fourni ne correspond à aucun compte.',
                ], 401);
            }

            // Vérification de l'état du compte (client ou administrateur)
            if (!$user->status) {
                return response()->json([
                    'status_code' => 401,
                    'message' => 'Votre compte est désactivé. Veuillez contacter l\'administrateur.',
                ], 401);
            }

            // Création du token d'authentification
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status_code' => 200,
                'message' => 'Utilisateur connecté avec succès.',
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
        } catch (Exception $e) {
            // Gestion des erreurs
            return response()->json([
                'status_code' => 500,
                'message' => 'Échec lors de la connexion.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   
    public function logout(Request $request)
    {
        // Supprime le token de l'utilisateur connecté
        $request->user()->tokens()->delete();

        return response()->json([
            'status_code' => 200,
            'message' => 'Déconnexion réussie'
        ]);
    }
}
