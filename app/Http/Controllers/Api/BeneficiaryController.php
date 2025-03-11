<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransactionModel;
use Illuminate\Http\Request;
use App\Models\BeneficiaryModel;
use Illuminate\Support\Facades\Validator;

class BeneficiaryController extends Controller
{
    /**
     * Afficher la liste des bénéficiaires avec pagination et recherche.
     */  public function index(Request $request)
    {
        try {
            $query = BeneficiaryModel::query();

            // Filtrer par recherche (nom/prénom)
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where('firstname', 'LIKE', "%$search%")
                      ->orWhere('lastname', 'LIKE', "%$search%");
            }

            // Pagination
            $perPage = $request->input('per_page', 10);
            $beneficiaries = $query->paginate($perPage);

            return response()->json([
                'status_code' => 200,
                'message' => 'Liste des bénéficiaires récupérée avec succès.',
                'data' => $beneficiaries,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Erreur lors de la récupération des bénéficiaires.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Afficher un bénéficiaire spécifique.
     */
    public function show($id)
    {
        try {
            $beneficiary = BeneficiaryModel::findOrFail($id);

            return response()->json([
                'status_code' => 200,
                'message' => 'Bénéficiaire récupéré avec succès.',
                'data' => $beneficiary,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 404,
                'message' => 'Bénéficiaire introuvable.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Créer un nouveau bénéficiaire.
     */ public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'BIC_code' => 'required|string|unique:beneficiaries',
            'IBAN_code' => 'required|string',
            'account_number' => 'required|string',
            'client_account_id' => 'required|exists:client_account,id', // Vérifie si le compte existe
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation échouée.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $beneficiary = BeneficiaryModel::create($request->all());

            return response()->json([
                'status_code' => 201,
                'message' => 'Bénéficiaire créé avec succès.',
                'data' => $beneficiary,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Erreur lors de la création du bénéficiaire.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mettre à jour un bénéficiaire existant.
     */
    public function update(Request $request, $id)
    {
        try {
            $beneficiary = BeneficiaryModel::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'firstname' => 'sometimes|string|max:255',
                'lastname' => 'sometimes|string|max:255',
                'BIC_code' => "sometimes|string|unique:beneficiaries,BIC_code,$id",
                'IBAN_code' => 'sometimes|string',
                'account_number' => 'sometimes|string',
                'client_account_id' => 'sometimes|exists:client_accounts,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 422,
                    'message' => 'Validation échouée.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $beneficiary->update($request->all());

            return response()->json([
                'status_code' => 200,
                'message' => 'Bénéficiaire mis à jour avec succès.',
                'data' => $beneficiary,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Erreur lors de la mise à jour du bénéficiaire.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprimer un bénéficiaire.
     */
    public function destroy($id)
    {
        try {
            $beneficiary = BeneficiaryModel::findOrFail($id);
            $beneficiary->delete();

            return response()->json([
                'status_code' => 200,
                'message' => 'Bénéficiaire supprimé avec succès.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Erreur lors de la suppression du bénéficiaire.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
