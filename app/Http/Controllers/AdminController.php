<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountModel;
use App\Models\ClientModel;
use App\Models\AdministratorModel;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function getClients(Request $request)
    {
        try {
            // Vérifier si l'admin est authentifié
            if (!Auth::guard('admin')->check()) {
                return response()->json([
                    'status_code' => 401,
                    'message' => 'Accès refusé. Vous devez être un administrateur authentifié.',
                ], 401);
            }
    
            // Vérifier si l'utilisateur authentifié est un administrateur
            $admin = AdministratorModel::where('email', $request->user('admin')->email)->first();
            if (!$admin) {
                return response()->json([
                    'status_code' => 403,
                    'message' => 'Accès refusé. Vous n\'avez pas les droits nécessaires pour accéder à cette ressource.',
                ], 403);
            }
    
            // Récupérer tous les clients avec leurs informations de compte et fichiers associés
            $query = ClientModel::with(['account', 'files']); // Charger les relations 'account' et 'files'
    
            // Recherche par nom, prénom, ou email de l'utilisateur
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where('firstname', 'LIKE', "%$search%")
                      ->orWhere('lastname', 'LIKE', "%$search%")
                      ->orWhere('email', 'LIKE', "%$search%");
            }
    
            // Pagination (par défaut 10 résultats par page)
            $perPage = $request->input('per_page', 10);
            $clients = $query->paginate($perPage);
    
            return response()->json([
                'status_code' => 200,
                'message' => 'Liste des clients récupérée avec succès.',
                'data' => $clients, // Retourne les données paginées avec les informations de compte et les fichiers associés
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Erreur lors de la récupération des clients.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
}
