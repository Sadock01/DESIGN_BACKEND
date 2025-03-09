<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class AccountModel extends Model
{

    use HasFactory;

    protected $table = 'client_account';
    protected $fillable = [
        'solde',
        'iban',
        'bic',
        'account_activated',
        'client_id',
    ];

    protected $casts = [
        'account_activated' => 'boolean',
    ];
    public function transaction()
    {
        return $this->hasMany(TransactionModel::class);
    }

    public function card()
    {
        return $this->hasOne(CardModel::class);
    }

    public function beneficiaire()
    {
        return $this->hasMany(BeneficiaryModel::class);
    }
    public function client()
    {
        return $this->belongsTo(ClientModel::class);
    }
}
