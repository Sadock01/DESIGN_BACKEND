<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeneficiaryModel extends Model
{
    use HasFactory;
    protected $table = 'beneficiaries';
    protected $fillable = [
        'client_id', 'firstname', 'lastname', 'BIC_code', 'IBAN_code', 'account_number'
    ];

    public function clientAccount()
    {
        return $this->belongsTo(AccountModel::class);
    }
}
