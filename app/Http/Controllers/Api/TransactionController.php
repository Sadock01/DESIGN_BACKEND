<?php

namespace App\Http\Controllers;

use App\Models\TransactionModel;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    // Créer une transaction
    public function createTransaction(Request $request)
    {
        $request->validate([
            'beneficiary_id' => 'required|exists:beneficiaries,id',
            'client_account_id' => 'required|exists:client_account,id',
            'transaction_type' => 'required|string',
            'transaction_amount' => 'required|numeric|min:1',
        ]);

        $transaction = TransactionModel::create([
            'client_id' => Auth::id(),
            'beneficiary_id' => $request->beneficiary_id,
            'client_account_id' => $request->client_account_id,
            'transaction_type' => $request->transaction_type,
            'transaction_amount' => $request->transaction_amount,
            'transaction_status' => 'pending',
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'Transaction créée avec succès',
            'transaction' => $transaction
        ]);
    }

    // Lister les transactions d'un client
    public function getClientTransactions()
    {
        $transactions = TransactionModel::where('client_id', Auth::id())->get();

        return response()->json([
            'status' => 200,
            'transactions' => $transactions
        ]);
    }

   
    public function checkTransactionStatus($id)
    {
        $transaction = TransactionModel::findOrFail($id);

        // Vérifier si la transaction appartient au client connecté
        if ($transaction->client_id !== Auth::id()) {
            return response()->json([
                'status' => 403,
                'message' => 'Accès refusé'
            ], 403);
        }

        return response()->json([
            'status' => 200,
            'transaction_status' => $transaction->transaction_status
        ]);
    }

    // Mettre à jour le statut d'une transaction (ADMIN)
    public function updateTransactionStatus(Request $request, $id)
{
    $request->validate([
        'transaction_status' => 'required|in:pending,completed,failed',
    ]);

    $transaction = TransactionModel::findOrFail($id);

    // Mettre à jour le statut directement
    $transaction->transaction_status = $request->transaction_status;
    $transaction->save();

    return response()->json([
        'status' => 200,
        'message' => 'Statut de la transaction mis à jour avec succès',
        'transaction' => $transaction
    ]);
}

    
}
