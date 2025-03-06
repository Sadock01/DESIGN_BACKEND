<?php

namespace App\Http\Controllers;

use App\Models\AccountModel;
use App\Models\TransactionModel;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{

    public function index(Request $request, $clientAccountId)
{
    try {
        // Début de la requête pour récupérer les transactions
        $query = TransactionModel::where('client_account_id', $clientAccountId);

        // Filtrer par recherche (par description de la transaction, nom du bénéficiaire, etc.)
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('transaction_description', 'LIKE', "%$search%")
                  ->orWhere('beneficiary_name', 'LIKE', "%$search%");
        }

        // Pagination : nombre d'éléments par page (par défaut 10)
        $perPage = $request->input('per_page', 10);
        $transactions = $query->orderBy('transaction_date', 'desc')->paginate($perPage);

        return response()->json([
            'status_code' => 200,
            'message' => 'Liste des transactions récupérée avec succès.',
            'data' => $transactions,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'message' => 'Erreur lors de la récupération des transactions.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    public function createTransaction(Request $request)
    {
        // Validation des données envoyées par le Front-End
        $request->validate([
            'beneficiary_name' => 'required|string',
            'beneficiary_iban' => 'required|string',
            'client_account_id' => 'required|exists:client_account,id',
            'transaction_type' => 'required|string', // Le type de transaction vient du Front
            'transaction_description' => 'required|string',
            'transaction_amount' => 'required|numeric|min:1',
            'transaction_message' => 'nullable|string',
        ]);
    
        // Création de la transaction avec les données du Front
        $transaction = TransactionModel::create([
            'client_account_id' => $request->client_account_id,
      
            'transaction_date' => now(), // Date de la transaction = aujourd'hui
            'transaction_description' => $request->transaction_description,
            'beneficiary_name' => $request->beneficiary_name,
            'beneficiary_iban' => $request->beneficiary_iban,
            'transaction_type' => $request->transaction_type, // Récupéré du Front-End
            'transaction_amount' => $request->transaction_amount,
            'transaction_status' => 'pending', // Statut par défaut
            'transaction_desactivated' => false, // La transaction est active par défaut
            'transaction_message' => $request->transaction_message,
        ]);
    
        return response()->json([
            'status' => 201,
            'message' => 'Transaction créée avec succès',
            'transaction' => $transaction
        ], 201);
    }
   
   

    public function getTransactions()
    {
        // Récupérer les transactions du client connecté
        $transactions = TransactionModel::where('client_id', Auth::id())->get();
    
        return response()->json([
            'status' => 200,
            'transactions' => $transactions
        ]);
    }
    
    public function validateTransaction($transactionId)
    {
        // Récupérer la transaction
        $transaction = TransactionModel::find($transactionId);
        if (!$transaction) {
            return response()->json([
                'status' => 404,
                'message' => 'Transaction introuvable.'
            ], 404);
        }
    
        // Vérifier si la transaction est déjà validée
        if ($transaction->transaction_status === 'approved') {
            return response()->json([
                'status' => 400,
                'message' => 'Cette transaction a déjà été approuvée.'
            ], 400);
        }
    
        // Récupérer le compte client
        $clientAccount = AccountModel::find($transaction->client_account_id);
        if (!$clientAccount) {
            return response()->json([
                'status' => 404,
                'message' => 'Compte client introuvable.'
            ], 404);
        }
    
        // Valider la transaction (changement de statut)
        $transaction->transaction_status = 'approved';
        $transaction->transaction_date = now(); // Date de validation
        $transaction->save();
    
        // Ajouter automatiquement le montant au solde du client
        $clientAccount->balance -= $transaction->transaction_amount;
        $clientAccount->save();
    
        return response()->json([
            'status' => 200,
            'message' => 'Transaction approuvée et montant ajouté au solde.',
            'transaction' => $transaction,
            'new_balance' => $clientAccount->balance
        ]);
    }
    public function updateTransactionMessage($transactionId, Request $request)
{
    // Validation du message
    $request->validate([
        'transaction_message' => 'required|string', // Validation du message
    ]);

    try {
        // Récupérer la transaction
        $transaction = TransactionModel::findOrFail($transactionId);

        // Mettre à jour le message
        $transaction->update([
            'transaction_message' => $request->transaction_message,
        ]);

        return response()->json([
            'status_code' => 200,
            'message' => 'Message de la transaction mis à jour avec succès.',
            'transaction' => $transaction
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'message' => 'Erreur lors de la mise à jour du message.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    
}
