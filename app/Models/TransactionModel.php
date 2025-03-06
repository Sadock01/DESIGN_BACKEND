<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransactionModel extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'transaction_date',
        'transaction_description',
        'beneficiary_name',
        'beneficiary_iban',
        'client_account_id',
        'transaction_type',
        'transaction_desactivated',
        'transaction_reason',
        'transaction_amount',
        'transaction_status',
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    public function clientAccount()
    {
        return $this->belongsTo(AccountModel::class);
    }
}
